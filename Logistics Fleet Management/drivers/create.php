<?php
/**
 * Add New Driver
 * Fleet Management System
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDBConnection();
$pageTitle = 'Add Driver';
$activeMenu = 'drivers';

$errors = [];
$formData = [
    'first_name'     => '',
    'last_name'      => '',
    'phone'          => '',
    'email'          => '',
    'license_number' => '',
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
        // Check uniqueness of license number
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM driver WHERE license_number = ?");
        $stmt->execute([$formData['license_number']]);
        if ($stmt->fetchColumn() > 0) {
            $errors['license_number'] = 'This license number is already assigned to another driver.';
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO driver (first_name, last_name, phone, email, license_number)
                VALUES (:first_name, :last_name, :phone, :email, :license_number)
            ");
            $stmt->execute([
                ':first_name'     => $formData['first_name'],
                ':last_name'      => $formData['last_name'],
                ':phone'          => $formData['phone'],
                ':email'          => $formData['email'],
                ':license_number' => $formData['license_number'],
            ]);

            setFlash('success', 'Driver "' . $formData['first_name'] . ' ' . $formData['last_name'] . '" added successfully.');
            redirect('drivers/index.php');
        } catch (PDOException $e) {
            $errors['general'] = 'Database error: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="page-title-group">
        <h1>Add New Driver</h1>
        <p>Register a new certified driver for fleet deliveries</p>
    </div>
    <div class="page-actions">
        <a href="<?= url('drivers/index.php') ?>" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i> Back to Drivers
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
            <i class="fa-solid fa-id-card"></i> Driver Information Form
        </div>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= url('drivers/create.php') ?>" novalidate>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="first_name">First Name <span class="required">*</span></label>
                    <input type="text" 
                           id="first_name" 
                           name="first_name" 
                           class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>" 
                           placeholder="e.g. John" 
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
                           placeholder="e.g. Doe" 
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
                           placeholder="e.g. +33 6 12 34 56 78" 
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
                           placeholder="e.g. john.doe@fleetlog.com" 
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
                           placeholder="e.g. DL-123456789" 
                           value="<?= e($formData['license_number']) ?>" 
                           required>
                    <?php if (isset($errors['license_number'])): ?>
                        <div class="invalid-feedback"><?= e($errors['license_number']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-actions">
                <a href="<?= url('drivers/index.php') ?>" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Save Driver
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
