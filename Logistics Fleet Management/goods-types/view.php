<?php
/**
 * View Goods Type Details & Related Deliveries
 * Fleet Management System
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDBConnection();
$activeMenu = 'goods-types';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    setFlash('danger', 'Invalid goods type ID.');
    redirect('goods-types/index.php');
}

// Fetch goods type
$stmt = $pdo->prepare("SELECT * FROM goodstype WHERE id_goods_type = ?");
$stmt->execute([$id]);
$goodsType = $stmt->fetch();

if (!$goodsType) {
    setFlash('danger', 'Goods category not found.');
    redirect('goods-types/index.php');
}

$pageTitle = 'Category: ' . $goodsType['name'];

// Fetch deliveries using this goods type
$stmt = $pdo->prepare("
    SELECT 
        d.id_delivery,
        d.departure_location,
        d.destination,
        d.delivery_date,
        d.status,
        d.weight,
        CONCAT(dr.first_name, ' ', dr.last_name) AS driver_name,
        v.registration_number,
        v.brand,
        v.model
    FROM delivery d
    JOIN driver dr ON d.id_driver = dr.id_driver
    JOIN vehicle v ON d.id_vehicle = v.id_vehicle
    WHERE d.id_goods_type = ?
    ORDER BY d.delivery_date DESC
");
$stmt->execute([$id]);
$deliveries = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="page-title-group">
        <h1>Goods Category Profile</h1>
        <p>Category #<?= e($id) ?>: <?= e($goodsType['name']) ?></p>
    </div>
    <div class="page-actions">
        <a href="<?= url('goods-types/index.php') ?>" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i> Back to Goods Types
        </a>
        <a href="<?= url('goods-types/edit.php?id=' . $id) ?>" class="btn btn-secondary">
            <i class="fa-solid fa-pen-to-square"></i> Edit
        </a>
        <a href="<?= url('goods-types/delete.php?id=' . $id) ?>" 
           class="btn btn-danger"
           data-confirm="Are you sure you want to delete category '<?= e($goodsType['name']) ?>'? Deliveries containing this goods type must be reassigned first."
           data-title="Delete Goods Category">
            <i class="fa-solid fa-trash-can"></i> Delete
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-tag"></i> Category Details
        </div>
    </div>
    <div class="card-body">
        <div class="details-grid">
            <div class="detail-item">
                <span class="detail-label">Category Name</span>
                <span class="detail-value">
                    <i class="fa-solid fa-box" style="color: var(--primary); margin-right: 6px;"></i>
                    <?= e($goodsType['name']) ?>
                </span>
            </div>
            <div class="detail-item" style="grid-column: span 2;">
                <span class="detail-label">Handling &amp; Description</span>
                <span class="detail-value" style="font-weight: normal; color: var(--gray-700);">
                    <?= nl2br(e($goodsType['description'] ?: 'No special handling instructions or description provided.')) ?>
                </span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-dolly"></i> Deliveries Containing this Goods Type (<?= count($deliveries) ?>)
        </div>
        <a href="<?= url('deliveries/create.php') ?>" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i> New Delivery
        </a>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($deliveries)): ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fa-solid fa-box-open"></i></div>
                <div class="empty-state-title">No Deliveries Found</div>
                <p class="empty-state-text">No deliveries are currently configured with this cargo type.</p>
                <a href="<?= url('deliveries/create.php') ?>" class="btn btn-primary btn-sm">
                    Create Delivery
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Route (Origin &rarr; Destination)</th>
                            <th>Date</th>
                            <th>Driver</th>
                            <th>Vehicle</th>
                            <th>Weight</th>
                            <th>Status</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deliveries as $del): ?>
                            <tr>
                                <td><span class="badge badge-default">#<?= e($del['id_delivery']) ?></span></td>
                                <td>
                                    <strong><?= e($del['departure_location']) ?></strong> 
                                    <i class="fa-solid fa-arrow-right-long" style="color: var(--gray-400); margin: 0 4px;"></i> 
                                    <strong><?= e($del['destination']) ?></strong>
                                </td>
                                <td><?= formatDateTime($del['delivery_date']) ?></td>
                                <td><?= e($del['driver_name']) ?></td>
                                <td>
                                    <?= e($del['brand'] . ' ' . $del['model']) ?>
                                    <small style="color: var(--gray-500); display: block;">(<?= e($del['registration_number']) ?>)</small>
                                </td>
                                <td><?= formatWeight($del['weight']) ?></td>
                                <td><?= renderStatusBadge($del['status']) ?></td>
                                <td style="text-align: right;">
                                    <a href="<?= url('deliveries/view.php?id=' . $del['id_delivery']) ?>" class="btn-icon" title="View Delivery Details">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
