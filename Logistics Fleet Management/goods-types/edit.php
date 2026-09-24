<?php
/**
 * Edit Goods Type
 * Fleet Management System
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDBConnection();
$pageTitle = 'Edit Goods Type';
$activeMenu = 'goods-types';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    setFlash('danger', 'Invalid goods type ID specified.');
    redirect('goods-types/index.php');
}

// Fetch existing goods type
$stmt = $pdo->prepare("SELECT * FROM goodstype WHERE id_goods_type = ?");
$stmt->execute([$id]);
$goodsType = $stmt->fetch();

if (!$goodsType) {
    setFlash('danger', 'Goods category not found.');
    redirect('goods-types/index.php');
}

$errors = [];
$formData = [
    'name'        => $goodsType['name'],
    'description' => $goodsType['description'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['name']        = trim($_POST['name'] ?? '');
    $formData['description'] = trim($_POST['description'] ?? '');

    // Validation
    if ($formData['name'] === '') {
        $errors['name'] = 'Goods category name is required.';
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM goodstype WHERE name = ? AND id_goods_type != ?");
        $stmt->execute([$formData['name'], $id]);
        if ($stmt->fetchColumn() > 0) {
            $errors['name'] = 'Another goods category already uses this name.';
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE goodstype 
                SET name = :name, 
                    description = :desc 
                WHERE id_goods_type = :id
            ");
            $stmt->execute([
                ':name' => $formData['name'],
                ':desc' => $formData['description'] !== '' ? $formData['description'] : null,
                ':id'   => $id,
            ]);

            setFlash('success', 'Goods category updated successfully.');
            redirect('goods-types/view.php?id=' . $id);
        } catch (PDOException $e) {
            $errors['general'] = 'Database error: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="page-title-group">
        <h1>Edit Goods Category</h1>
        <p>Modify category details for "<?= e($goodsType['name']) ?>"</p>
    </div>
    <div class="page-actions">
        <a href="<?= url('goods-types/view.php?id=' . $id) ?>" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i> View Category
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
            <i class="fa-solid fa-tag"></i> Update Cargo Details (#<?= e($id) ?>)
        </div>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= url('goods-types/edit.php?id=' . $id) ?>" novalidate>
            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label class="form-label" for="name">Category Name <span class="required">*</span></label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
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
                          class="form-control"><?= e($formData['description']) ?></textarea>
            </div>

            <div class="form-actions">
                <a href="<?= url('goods-types/view.php?id=' . $id) ?>" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Update Category
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
