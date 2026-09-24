<?php
/**
 * Edit Driver
 * Fleet Management System
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDBConnection();
$pageTitle = 'Edit Driver';
$activeMenu = 'drivers';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    setFlash('danger', 'Invalid driver ID specified.');
    redirect('drivers/index.php');
}

// Fetch existing driver
$stmt = $pdo->prepare("SELECT * FROM driver WHERE id_driver = ?");
$stmt->execute([$id]);
$driver = $stmt->fetch();

if (!$driver) {
    setFlash('danger', 'Driver not found.');
    redirect('drivers/index.php');
}

$errors = [];
$formData = [
    'first_name'     => $driver['first_name'],
    'last_name'      => $driver['last_name'],
    'phone'          => $driver['phone'],
    'email'          => $driver['email'],
    'license_number' => $driver['license_number'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['first_name']     = trim($_POST['first_name'] ?? '');
    $formData['last_name']      = trim($_POST['last_name'] ?? '');
    $formData['phone']          = trim($_POST['phone'] ?? '');
    $formData['email']          = trim($_POST['email'] ?? '');
    $formData['license_number'] = trim($_POST['license_number'] ?? '');

    // Validation
    if ($formData['first_name'] === '') {
        $errors['first_name'] = 'First name is required.';
    }
    if ($formData['last_name'] === '') {
        $errors['last_name'] = 'Last name is required.';
    }
    if ($formData['phone'] === '') {
        $errors['phone'] = 'Phone number is required.';
    }
    if ($formData['email'] === '') {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    if ($formData['license_number'] === '') {
        $errors['license_number'] = 'Driver license number is required.';
    } else {
        // Check uniqueness of license number (exclude this driver)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM driver WHERE license_number = ? AND id_driver != ?");
        $stmt->execute([$formData['license_number'], $id]);
        if ($stmt->fetchColumn() > 0) {
            $errors['license_number'] = 'This license number is already assigned to another driver.';
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE driver 
                SET first_name = :first_name, 
                    last_name = :last_name, 
                    phone = :phone, 
                    email = :email, 
                    license_number = :license_number
                WHERE id_driver = :id
            ");
            $stmt->execute([
                ':first_name'     => $formData['first_name'],
                ':last_name'      => $formData['last_name'],
                ':phone'          => $formData['phone'],
                ':email'          => $formData['email'],
                ':license_number' => $formData['license_number'],
                ':id'             => $id,
            ]);

            setFlash('success', 'Driver details updated successfully.');
            redirect('drivers/view.php?id=' . $id);
        } catch (PDOException $e) {
            $errors['general'] = 'Database error: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="page-title-group">
        <h1>Edit Driver: <?= e($driver['first_name'] . ' ' . $driver['last_name']) ?></h1>
        <p>Modify contact info and license records</p>
    </div>
    <div class="page-actions">
        <a href="<?= url('drivers/view.php?id=' . $id) ?>" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i> View Profile
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
            <i class="fa-solid fa-user-pen"></i> Update Driver Details (#<?= e($id) ?>)
        </div>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= url('drivers/edit.php?id=' . $id) ?>" novalidate>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="first_name">First Name <span class="required">*</span></label>
                    <input type="text" 
                           id="first_name" 
                           name="first_name" 
                           class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>" 
                           value="<?= e($formData['first_name']) ?>" 
                           required>
                    <?php if (isset($errors['first_name'])): ?>
                        <div class="invalid-feedback"><?= e($errors['first_name']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="last_name">Last Name <span class="required">*</span></label>
                    <input type="text" 
                           id="last_name" 
                           name="last_name" 
                           class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>" 
                           value="<?= e($formData['last_name']) ?>" 
                           required>
                    <?php if (isset($errors['last_name'])): ?>
                        <div class="invalid-feedback"><?= e($errors['last_name']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number <span class="required">*</span></label>
                    <input type="tel" 
                           id="phone" 
                           name="phone" 
                           class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" 
                           value="<?= e($formData['phone']) ?>" 
                           required>
                    <?php if (isset($errors['phone'])): ?>
                        <div class="invalid-feedback"><?= e($errors['phone']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email Address <span class="required">*</span></label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                           value="<?= e($formData['email']) ?>" 
                           required>
                    <?php if (isset($errors['email'])): ?>
                        <div class="invalid-feedback"><?= e($errors['email']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group form-full">
                    <label class="form-label" for="license_number">Driving License Number <span class="required">*</span></label>
                    <input type="text" 
                           id="license_number" 
                           name="license_number" 
                           class="form-control <?= isset($errors['license_number']) ? 'is-invalid' : '' ?>" 
                           value="<?= e($formData['license_number']) ?>" 
                           required>
                    <?php if (isset($errors['license_number'])): ?>
                        <div class="invalid-feedback"><?= e($errors['license_number']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-actions">
                <a href="<?= url('drivers/view.php?id=' . $id) ?>" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Update Driver
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
