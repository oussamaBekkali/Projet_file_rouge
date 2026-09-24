<?php
/**
 * Edit Delivery
 * Fleet Management System
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDBConnection();
$pageTitle = 'Edit Delivery';
$activeMenu = 'deliveries';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    setFlash('danger', 'Invalid delivery ID specified.');
    redirect('deliveries/index.php');
}

// Fetch existing delivery
$stmt = $pdo->prepare("SELECT * FROM delivery WHERE id_delivery = ?");
$stmt->execute([$id]);
$delivery = $stmt->fetch();

if (!$delivery) {
    setFlash('danger', 'Delivery record not found.');
    redirect('deliveries/index.php');
}

// Fetch drivers, vehicles, and goods types for dropdowns
$drivers = $pdo->query("SELECT id_driver, first_name, last_name, license_number FROM driver ORDER BY first_name, last_name")->fetchAll();
$vehicles = $pdo->query("SELECT id_vehicle, registration_number, brand, model, vehicle_type, capacity, status FROM vehicle ORDER BY brand, model")->fetchAll();
$goodsTypes = $pdo->query("SELECT id_goods_type, name FROM goodstype ORDER BY name")->fetchAll();

$statuses = ['Pending', 'In Transit', 'Delivered', 'Cancelled'];

$errors = [];
$formData = [
    'departure_location' => $delivery['departure_location'],
    'destination'        => $delivery['destination'],
    'delivery_date'      => date('Y-m-d\TH:i', strtotime($delivery['delivery_date'])),
    'status'             => $delivery['status'],
    'weight'             => $delivery['weight'],
    'id_driver'          => $delivery['id_driver'],
    'id_vehicle'         => $delivery['id_vehicle'],
    'id_goods_type'      => $delivery['id_goods_type'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['departure_location'] = trim($_POST['departure_location'] ?? '');
    $formData['destination']        = trim($_POST['destination'] ?? '');
    $formData['delivery_date']      = trim($_POST['delivery_date'] ?? '');
    $formData['status']             = trim($_POST['status'] ?? 'Pending');
    $formData['weight']             = trim($_POST['weight'] ?? '');
    $formData['id_driver']          = (int)($_POST['id_driver'] ?? 0);
    $formData['id_vehicle']         = (int)($_POST['id_vehicle'] ?? 0);
    $formData['id_goods_type']      = (int)($_POST['id_goods_type'] ?? 0);

    // Validation
    if ($formData['departure_location'] === '') {
        $errors['departure_location'] = 'Departure location is required.';
    }
    if ($formData['destination'] === '') {
        $errors['destination'] = 'Destination is required.';
    }
    if ($formData['delivery_date'] === '') {
        $errors['delivery_date'] = 'Delivery date & time is required.';
    } else {
        $dateFormatted = str_replace('T', ' ', $formData['delivery_date']);
        if (strlen($dateFormatted) === 16) {
            $dateFormatted .= ':00';
        }
    }

    if ($formData['weight'] === '' || !is_numeric($formData['weight']) || (float)$formData['weight'] <= 0) {
        $errors['weight'] = 'Please enter a valid positive weight in kg.';
    }

    if (!in_array($formData['status'], $statuses, true)) {
        $errors['status'] = 'Please select a valid delivery status.';
    }

    // Driver existence check
    $driverExists = false;
    foreach ($drivers as $d) {
        if ($d['id_driver'] == $formData['id_driver']) {
            $driverExists = true;
            break;
        }
    }
    if (!$driverExists) {
        $errors['id_driver'] = 'Please select a valid driver.';
    }

    // Vehicle existence & capacity check
    $selectedVehicle = null;
    foreach ($vehicles as $v) {
        if ($v['id_vehicle'] == $formData['id_vehicle']) {
            $selectedVehicle = $v;
            break;
        }
    }
    if (!$selectedVehicle) {
        $errors['id_vehicle'] = 'Please select a valid vehicle.';
    } elseif (is_numeric($formData['weight']) && (float)$formData['weight'] > $selectedVehicle['capacity']) {
        $errors['weight'] = 'Weight (' . number_format((float)$formData['weight']) . ' kg) exceeds vehicle max capacity (' . number_format($selectedVehicle['capacity']) . ' kg).';
    }

    // Goods type existence check
    $goodsExists = false;
    foreach ($goodsTypes as $g) {
        if ($g['id_goods_type'] == $formData['id_goods_type']) {
            $goodsExists = true;
            break;
        }
    }
    if (!$goodsExists) {
        $errors['id_goods_type'] = 'Please select a valid goods type.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE delivery 
                SET departure_location = :dept, 
                    destination = :dest, 
                    delivery_date = :date, 
                    status = :status, 
                    weight = :weight, 
                    id_driver = :driver, 
                    id_vehicle = :vehicle, 
                    id_goods_type = :goods 
                WHERE id_delivery = :id
            ");
            $stmt->execute([
                ':dept'    => $formData['departure_location'],
                ':dest'    => $formData['destination'],
                ':date'    => $dateFormatted,
                ':status'  => $formData['status'],
                ':weight'  => (float)$formData['weight'],
                ':driver'  => $formData['id_driver'],
                ':vehicle' => $formData['id_vehicle'],
                ':goods'   => $formData['id_goods_type'],
                ':id'      => $id,
            ]);

            setFlash('success', "Delivery #{$id} updated successfully.");
            redirect('deliveries/view.php?id=' . $id);
        } catch (PDOException $e) {
            $errors['general'] = 'Database error: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="page-title-group">
        <h1>Edit Delivery #<?= e($id) ?></h1>
        <p>Update route, scheduling, vehicle, driver, or cargo allocation</p>
    </div>
    <div class="page-actions">
        <a href="<?= url('deliveries/view.php?id=' . $id) ?>" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i> View Details
        </a>
    </div>
</div>

<?php if (isset($errors['general'])): ?>
    <div class="alert alert-danger">
        <i class="fa-solid fa-circle-exclamation alert-icon"></i>
        <div class="alert-message"><?= e($errors['general']) ?></div>
    </div>
<?php endif; ?>

<div class="card" style="max-width: 850px; margin: 0 auto;">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-pen-to-square"></i> Update Dispatch Record
        </div>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= url('deliveries/edit.php?id=' . $id) ?>" novalidate>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="departure_location">Departure Location (Origin) <span class="required">*</span></label>
                    <input type="text" 
                           id="departure_location" 
                           name="departure_location" 
                           class="form-control <?= isset($errors['departure_location']) ? 'is-invalid' : '' ?>" 
                           value="<?= e($formData['departure_location']) ?>" 
                           required>
                    <?php if (isset($errors['departure_location'])): ?>
                        <div class="invalid-feedback"><?= e($errors['departure_location']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="destination">Destination <span class="required">*</span></label>
                    <input type="text" 
                           id="destination" 
                           name="destination" 
                           class="form-control <?= isset($errors['destination']) ? 'is-invalid' : '' ?>" 
                           value="<?= e($formData['destination']) ?>" 
                           required>
                    <?php if (isset($errors['destination'])): ?>
                        <div class="invalid-feedback"><?= e($errors['destination']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="delivery_date">Delivery Date &amp; Time <span class="required">*</span></label>
                    <input type="datetime-local" 
                           id="delivery_date" 
                           name="delivery_date" 
                           class="form-control <?= isset($errors['delivery_date']) ? 'is-invalid' : '' ?>" 
                           value="<?= e($formData['delivery_date']) ?>" 
                           required>
                    <?php if (isset($errors['delivery_date'])): ?>
                        <div class="invalid-feedback"><?= e($errors['delivery_date']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="status">Status <span class="required">*</span></label>
                    <select id="status" name="status" class="form-select <?= isset($errors['status']) ? 'is-invalid' : '' ?>" required>
                        <?php foreach ($statuses as $st): ?>
                            <option value="<?= e($st) ?>" <?= $formData['status'] === $st ? 'selected' : '' ?>>
                                <?= e($st) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['status'])): ?>
                        <div class="invalid-feedback"><?= e($errors['status']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="id_driver">Assigned Driver <span class="required">*</span></label>
                    <select id="id_driver" name="id_driver" class="form-select <?= isset($errors['id_driver']) ? 'is-invalid' : '' ?>" required>
                        <option value="">-- Choose a Driver --</option>
                        <?php foreach ($drivers as $d): ?>
                            <option value="<?= e($d['id_driver']) ?>" <?= $formData['id_driver'] == $d['id_driver'] ? 'selected' : '' ?>>
                                <?= e($d['first_name'] . ' ' . $d['last_name']) ?> (License: <?= e($d['license_number']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['id_driver'])): ?>
                        <div class="invalid-feedback"><?= e($errors['id_driver']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="id_vehicle">Assigned Vehicle <span class="required">*</span></label>
                    <select id="id_vehicle" name="id_vehicle" class="form-select <?= isset($errors['id_vehicle']) ? 'is-invalid' : '' ?>" required>
                        <option value="">-- Choose a Vehicle --</option>
                        <?php foreach ($vehicles as $v): ?>
                            <option value="<?= e($v['id_vehicle']) ?>" <?= $formData['id_vehicle'] == $v['id_vehicle'] ? 'selected' : '' ?>>
                                <?= e($v['brand'] . ' ' . $v['model']) ?> [<?= e($v['registration_number']) ?>] — Max: <?= formatWeight($v['capacity']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['id_vehicle'])): ?>
                        <div class="invalid-feedback"><?= e($errors['id_vehicle']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="id_goods_type">Goods / Cargo Type <span class="required">*</span></label>
                    <select id="id_goods_type" name="id_goods_type" class="form-select <?= isset($errors['id_goods_type']) ? 'is-invalid' : '' ?>" required>
                        <option value="">-- Choose Cargo Type --</option>
                        <?php foreach ($goodsTypes as $g): ?>
                            <option value="<?= e($g['id_goods_type']) ?>" <?= $formData['id_goods_type'] == $g['id_goods_type'] ? 'selected' : '' ?>>
                                <?= e($g['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['id_goods_type'])): ?>
                        <div class="invalid-feedback"><?= e($errors['id_goods_type']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="weight">Cargo Weight (kg) <span class="required">*</span></label>
                    <input type="number" 
                           step="any"
                           min="1"
                           id="weight" 
                           name="weight" 
                           class="form-control <?= isset($errors['weight']) ? 'is-invalid' : '' ?>" 
                           value="<?= e($formData['weight']) ?>" 
                           required>
                    <?php if (isset($errors['weight'])): ?>
                        <div class="invalid-feedback"><?= e($errors['weight']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-actions">
                <a href="<?= url('deliveries/view.php?id=' . $id) ?>" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Update Delivery
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
