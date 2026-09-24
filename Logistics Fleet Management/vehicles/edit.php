<?php
/**
 * Edit Vehicle
 * Fleet Management System
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDBConnection();
$pageTitle = 'Edit Vehicle';
$activeMenu = 'vehicles';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    setFlash('danger', 'Invalid vehicle ID specified.');
    redirect('vehicles/index.php');
}

// Fetch existing vehicle
$stmt = $pdo->prepare("SELECT * FROM vehicle WHERE id_vehicle = ?");
$stmt->execute([$id]);
$vehicle = $stmt->fetch();

if (!$vehicle) {
    setFlash('danger', 'Vehicle not found.');
    redirect('vehicles/index.php');
}

$vehicleTypes = ['Truck', 'Van', 'Pickup', 'Trailer', 'Semi-Trailer', 'Other'];
$vehicleStatuses = ['Available', 'On Trip', 'Maintenance', 'Out of Service'];

$errors = [];
$formData = [
    'registration_number' => $vehicle['registration_number'],
    'brand'               => $vehicle['brand'],
    'model'               => $vehicle['model'],
    'vehicle_type'        => $vehicle['vehicle_type'],
    'capacity'            => $vehicle['capacity'],
    'status'              => $vehicle['status'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['registration_number'] = strtoupper(trim($_POST['registration_number'] ?? ''));
    $formData['brand']               = trim($_POST['brand'] ?? '');
    $formData['model']               = trim($_POST['model'] ?? '');
    $formData['vehicle_type']        = trim($_POST['vehicle_type'] ?? '');
    $formData['capacity']            = trim($_POST['capacity'] ?? '');
    $formData['status']              = trim($_POST['status'] ?? 'Available');

    // Validation
    if ($formData['registration_number'] === '') {
        $errors['registration_number'] = 'Registration number is required.';
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM vehicle WHERE registration_number = ? AND id_vehicle != ?");
        $stmt->execute([$formData['registration_number'], $id]);
        if ($stmt->fetchColumn() > 0) {
            $errors['registration_number'] = 'A vehicle with this registration plate already exists.';
        }
    }

    if ($formData['brand'] === '') {
        $errors['brand'] = 'Brand / Manufacturer is required.';
    }
    if ($formData['model'] === '') {
        $errors['model'] = 'Vehicle model is required.';
    }
    if (!in_array($formData['vehicle_type'], $vehicleTypes, true)) {
        $errors['vehicle_type'] = 'Please select a valid vehicle type.';
    }
    if ($formData['capacity'] === '' || !is_numeric($formData['capacity']) || (float)$formData['capacity'] <= 0) {
        $errors['capacity'] = 'Please enter a valid positive capacity in kg.';
    }
    if (!in_array($formData['status'], $vehicleStatuses, true)) {
        $errors['status'] = 'Please select a valid operational status.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE vehicle 
                SET registration_number = :reg, 
                    brand = :brand, 
                    model = :model, 
                    vehicle_type = :type, 
                    capacity = :capacity, 
                    status = :status 
                WHERE id_vehicle = :id
            ");
            $stmt->execute([
                ':reg'      => $formData['registration_number'],
                ':brand'    => $formData['brand'],
                ':model'    => $formData['model'],
                ':type'     => $formData['vehicle_type'],
                ':capacity' => (float)$formData['capacity'],
                ':status'   => $formData['status'],
                ':id'       => $id,
            ]);

            setFlash('success', 'Vehicle "' . $formData['brand'] . ' ' . $formData['model'] . '" updated successfully.');
            redirect('vehicles/view.php?id=' . $id);
        } catch (PDOException $e) {
            $errors['general'] = 'Database error: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="page-title-group">
        <h1>Edit Vehicle</h1>
        <p>Modify specifications for <?= e($vehicle['brand'] . ' ' . $vehicle['model']) ?> (<?= e($vehicle['registration_number']) ?>)</p>
    </div>
    <div class="page-actions">
        <a href="<?= url('vehicles/view.php?id=' . $id) ?>" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i> View Vehicle
        </a>
    </div>
</div>

<?php if (isset($errors['general'])): ?>
    <div class="alert alert-danger">
        <i class="fa-solid fa-circle-exclamation alert-icon"></i>
        <div class="alert-message"><?= e($errors['general']) ?></div>
    </div>
<?php endif; ?>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-truck-pen"></i> Update Vehicle Details (#<?= e($id) ?>)
        </div>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= url('vehicles/edit.php?id=' . $id) ?>" novalidate>
            <div class="form-grid">
                <div class="form-group form-full">
                    <label class="form-label" for="registration_number">Registration / License Plate <span class="required">*</span></label>
                    <input type="text" 
                           id="registration_number" 
                           name="registration_number" 
                           class="form-control <?= isset($errors['registration_number']) ? 'is-invalid' : '' ?>" 
                           value="<?= e($formData['registration_number']) ?>" 
                           required>
                    <?php if (isset($errors['registration_number'])): ?>
                        <div class="invalid-feedback"><?= e($errors['registration_number']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="brand">Brand / Manufacturer <span class="required">*</span></label>
                    <input type="text" 
                           id="brand" 
                           name="brand" 
                           class="form-control <?= isset($errors['brand']) ? 'is-invalid' : '' ?>" 
                           value="<?= e($formData['brand']) ?>" 
                           required>
                    <?php if (isset($errors['brand'])): ?>
                        <div class="invalid-feedback"><?= e($errors['brand']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="model">Model <span class="required">*</span></label>
                    <input type="text" 
                           id="model" 
                           name="model" 
                           class="form-control <?= isset($errors['model']) ? 'is-invalid' : '' ?>" 
                           value="<?= e($formData['model']) ?>" 
                           required>
                    <?php if (isset($errors['model'])): ?>
                        <div class="invalid-feedback"><?= e($errors['model']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="vehicle_type">Vehicle Type <span class="required">*</span></label>
                    <select id="vehicle_type" name="vehicle_type" class="form-select <?= isset($errors['vehicle_type']) ? 'is-invalid' : '' ?>" required>
                        <?php foreach ($vehicleTypes as $vt): ?>
                            <option value="<?= e($vt) ?>" <?= $formData['vehicle_type'] === $vt ? 'selected' : '' ?>>
                                <?= e($vt) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['vehicle_type'])): ?>
                        <div class="invalid-feedback"><?= e($errors['vehicle_type']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="capacity">Payload Capacity (kg) <span class="required">*</span></label>
                    <input type="number" 
                           step="any"
                           min="1"
                           id="capacity" 
                           name="capacity" 
                           class="form-control <?= isset($errors['capacity']) ? 'is-invalid' : '' ?>" 
                           value="<?= e($formData['capacity']) ?>" 
                           required>
                    <?php if (isset($errors['capacity'])): ?>
                        <div class="invalid-feedback"><?= e($errors['capacity']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group form-full">
                    <label class="form-label" for="status">Operational Status <span class="required">*</span></label>
                    <select id="status" name="status" class="form-select <?= isset($errors['status']) ? 'is-invalid' : '' ?>" required>
                        <?php foreach ($vehicleStatuses as $st): ?>
                            <option value="<?= e($st) ?>" <?= $formData['status'] === $st ? 'selected' : '' ?>>
                                <?= e($st) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['status'])): ?>
                        <div class="invalid-feedback"><?= e($errors['status']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-actions">
                <a href="<?= url('vehicles/view.php?id=' . $id) ?>" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Update Vehicle
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
