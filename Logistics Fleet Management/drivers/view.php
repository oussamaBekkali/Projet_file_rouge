<?php
/**
 * View Driver Details & Related Deliveries
 * Fleet Management System
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDBConnection();
$activeMenu = 'drivers';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    setFlash('danger', 'Invalid driver ID.');
    redirect('drivers/index.php');
}

// Fetch driver info
$stmt = $pdo->prepare("SELECT * FROM driver WHERE id_driver = ?");
$stmt->execute([$id]);
$driver = $stmt->fetch();

if (!$driver) {
    setFlash('danger', 'Driver not found.');
    redirect('drivers/index.php');
}

$pageTitle = $driver['first_name'] . ' ' . $driver['last_name'];

// Fetch deliveries made by this driver
$stmt = $pdo->prepare("
    SELECT 
        d.id_delivery,
        d.departure_location,
        d.destination,
        d.delivery_date,
        d.status,
        d.weight,
        v.registration_number,
        v.brand,
        v.model,
        g.name AS goods_type
    FROM delivery d
    JOIN vehicle v ON d.id_vehicle = v.id_vehicle
    JOIN goodstype g ON d.id_goods_type = g.id_goods_type
    WHERE d.id_driver = ?
    ORDER BY d.delivery_date DESC
");
$stmt->execute([$id]);
$deliveries = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="page-title-group">
        <h1>Driver Profile</h1>
        <p>Driver #<?= e($id) ?>: <?= e($driver['first_name'] . ' ' . $driver['last_name']) ?></p>
    </div>
    <div class="page-actions">
        <a href="<?= url('drivers/index.php') ?>" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i> Back to Drivers
        </a>
        <a href="<?= url('drivers/edit.php?id=' . $id) ?>" class="btn btn-secondary">
            <i class="fa-solid fa-pen-to-square"></i> Edit
        </a>
        <a href="<?= url('drivers/delete.php?id=' . $id) ?>" 
           class="btn btn-danger"
           data-confirm="Are you sure you want to delete this driver? Deliveries must be reassigned first."
           data-title="Delete Driver">
            <i class="fa-solid fa-trash-can"></i> Delete
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-address-card"></i> Driver Information
        </div>
    </div>
    <div class="card-body">
        <div class="details-grid">
            <div class="detail-item">
                <span class="detail-label">Full Name</span>
                <span class="detail-value"><?= e($driver['first_name'] . ' ' . $driver['last_name']) ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Phone</span>
                <span class="detail-value"><a href="tel:<?= e($driver['phone']) ?>"><?= e($driver['phone']) ?></a></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Email Address</span>
                <span class="detail-value"><a href="mailto:<?= e($driver['email']) ?>"><?= e($driver['email']) ?></a></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">License Number</span>
                <span class="detail-value">
                    <code style="background: var(--gray-100); padding: 3px 8px; border-radius: 4px; font-weight: 600;">
                        <?= e($driver['license_number']) ?>
                    </code>
                </span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-truck-ramp-box"></i> Assigned Deliveries (<?= count($deliveries) ?>)
        </div>
        <a href="<?= url('deliveries/create.php') ?>" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i> Assign New Delivery
        </a>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($deliveries)): ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fa-solid fa-box-open"></i></div>
                <div class="empty-state-title">No Deliveries Found</div>
                <p class="empty-state-text">This driver has not been assigned to any deliveries yet.</p>
                <a href="<?= url('deliveries/create.php') ?>" class="btn btn-primary btn-sm">
                    Assign a Delivery
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Route (Origin &rarr; Destination)</th>
                            <th>Delivery Date</th>
                            <th>Assigned Vehicle</th>
                            <th>Goods Type</th>
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
                                <td>
                                    <?= e($del['brand'] . ' ' . $del['model']) ?>
                                    <small style="color: var(--gray-500); display: block;">(<?= e($del['registration_number']) ?>)</small>
                                </td>
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
