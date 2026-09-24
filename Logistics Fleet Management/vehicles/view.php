<?php
/**
 * View Vehicle Details & Delivery History
 * Fleet Management System
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDBConnection();
$activeMenu = 'vehicles';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    setFlash('danger', 'Invalid vehicle ID.');
    redirect('vehicles/index.php');
}

// Fetch vehicle
$stmt = $pdo->prepare("SELECT * FROM vehicle WHERE id_vehicle = ?");
$stmt->execute([$id]);
$vehicle = $stmt->fetch();

if (!$vehicle) {
    setFlash('danger', 'Vehicle not found.');
    redirect('vehicles/index.php');
}

$pageTitle = $vehicle['brand'] . ' ' . $vehicle['model'] . ' (' . $vehicle['registration_number'] . ')';

// Fetch deliveries made using this vehicle
$stmt = $pdo->prepare("
    SELECT 
        d.id_delivery,
        d.departure_location,
        d.destination,
        d.delivery_date,
        d.status,
        d.weight,
        CONCAT(dr.first_name, ' ', dr.last_name) AS driver_name,
        g.name AS goods_type
    FROM delivery d
    JOIN driver dr ON d.id_driver = dr.id_driver
    JOIN goodstype g ON d.id_goods_type = g.id_goods_type
    WHERE d.id_vehicle = ?
    ORDER BY d.delivery_date DESC
");
$stmt->execute([$id]);
$deliveries = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="page-title-group">
        <h1>Vehicle Profile</h1>
        <p>Asset #<?= e($id) ?>: <?= e($vehicle['brand'] . ' ' . $vehicle['model']) ?></p>
    </div>
    <div class="page-actions">
        <a href="<?= url('vehicles/index.php') ?>" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i> Back to Fleet
        </a>
        <a href="<?= url('vehicles/edit.php?id=' . $id) ?>" class="btn btn-secondary">
            <i class="fa-solid fa-pen-to-square"></i> Edit
        </a>
        <a href="<?= url('vehicles/delete.php?id=' . $id) ?>" 
           class="btn btn-danger"
           data-confirm="Are you sure you want to delete this vehicle from the fleet? Associated trips must be reassigned first."
           data-title="Delete Vehicle">
            <i class="fa-solid fa-trash-can"></i> Delete
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-truck-front"></i> Technical Specifications
        </div>
        <div>
            <?= renderStatusBadge($vehicle['status'], 'vehicle') ?>
        </div>
    </div>
    <div class="card-body">
        <div class="details-grid">
            <div class="detail-item">
                <span class="detail-label">License Plate</span>
                <span class="detail-value">
                    <strong style="background: var(--gray-100); padding: 4px 10px; border-radius: 4px; font-family: monospace; font-size: 1.05rem;">
                        <?= e($vehicle['registration_number']) ?>
                    </strong>
                </span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Brand &amp; Model</span>
                <span class="detail-value"><?= e($vehicle['brand'] . ' ' . $vehicle['model']) ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Vehicle Type</span>
                <span class="detail-value"><?= e($vehicle['vehicle_type']) ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Max Payload Capacity</span>
                <span class="detail-value"><?= formatWeight($vehicle['capacity']) ?></span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-clock-rotate-left"></i> Trip &amp; Delivery History (<?= count($deliveries) ?>)
        </div>
        <a href="<?= url('deliveries/create.php') ?>" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i> Assign New Delivery
        </a>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($deliveries)): ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fa-solid fa-route"></i></div>
                <div class="empty-state-title">No Trips Recorded</div>
                <p class="empty-state-text">This vehicle has no trip history recorded yet.</p>
                <a href="<?= url('deliveries/create.php') ?>" class="btn btn-primary btn-sm">
                    Assign a Trip
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
                            <th>Goods Type</th>
                            <th>Cargo Weight</th>
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
                                <td><?= e($del['goods_type']) ?></td>
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
