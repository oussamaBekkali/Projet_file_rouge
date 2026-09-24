<?php
/**
 * Add Goods Type
 * Fleet Management System
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDBConnection();
$pageTitle = 'Add Goods Type';
$activeMenu = 'goods-types';

$errors = [];
$formData = [
    'name'        => '',
    'description' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['name']        = trim($_POST['name'] ?? '');
    $formData['description'] = trim($_POST['description'] ?? '');

    // Validation
    if ($formData['name'] === '') {
        $errors['name'] = 'Goods category name is required.';
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM goodstype WHERE name = ?");
        $stmt->execute([$formData['name']]);
        if ($stmt->fetchColumn() > 0) {
            $errors['name'] = 'A goods category with this name already exists.';
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO goodstype (name, description) VALUES (:name, :desc)");
            $stmt->execute([
                ':name' => $formData['name'],
                ':desc' => $formData['description'] !== '' ? $formData['description'] : null,
            ]);

            setFlash('success', 'Goods category "' . $formData['name'] . '" added successfully.');
            redirect('goods-types/index.php');
        } catch (PDOException $e) {
            $errors['general'] = 'Database error: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="page-title-group">
        <h1>Add Goods Type</h1>
        <p>Define a new classification for fleet cargo</p>
    </div>
    <div class="page-actions">
        <a href="<?= url('goods-types/index.php') ?>" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i> Back to Goods Types
        </a>
    </div>
</div>

<?php if (isset($errors['general'])): ?>
    <div class="alert alert-danger">
        <i class="fa-solid fa-circle-exclamation alert-icon"></i>
        <div class="alert-message"><?= e($errors['general']) ?></div>
    </div>
<?php endif; ?>

<div class="card" style="max-width: 700px; margin: 0 auto;">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-box"></i> Cargo Category Form
        </div>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= url('goods-types/create.php') ?>" novalidate>
            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label class="form-label" for="name">Category Name <span class="required">*</span></label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                       placeholder="e.g. Hazardous Materials, Frozen Foods" 
                       value="<?= e($formData['name']) ?>" 
                       required>
                <?php if (isset($errors['name'])): ?>
                    <div class="invalid-feedback"><?= e($errors['name']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Description (Optional)</label>
                <textarea id="description" 
                          name="description" 
                          rows="4" 
                          class="form-control" 
                          placeholder="Describe handling precautions, storage conditions, or common products..."><?= e($formData['description']) ?></textarea>
            </div>

            <div class="form-actions">
                <a href="<?= url('goods-types/index.php') ?>" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Save Category
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
