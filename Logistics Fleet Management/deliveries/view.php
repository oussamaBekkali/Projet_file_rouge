<?php
/**
 * View Delivery Details
 * Fleet Management System
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDBConnection();
$activeMenu = 'deliveries';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    setFlash('danger', 'Invalid delivery ID.');
    redirect('deliveries/index.php');
}

// Fetch delivery with full relational details
$stmt = $pdo->prepare("
    SELECT 
        d.*,
        dr.first_name,
        dr.last_name,
        dr.phone AS driver_phone,
        dr.email AS driver_email,
        dr.license_number AS driver_license,
        v.registration_number,
        v.brand AS vehicle_brand,
        v.model AS vehicle_model,
        v.vehicle_type,
        v.capacity AS vehicle_capacity,
        v.status AS vehicle_status,
        g.name AS goods_type_name,
        g.description AS goods_type_desc
    FROM delivery d
    JOIN driver dr ON d.id_driver = dr.id_driver
    JOIN vehicle v ON d.id_vehicle = v.id_vehicle
    JOIN goodstype g ON d.id_goods_type = g.id_goods_type
    WHERE d.id_delivery = ?
");
$stmt->execute([$id]);
$delivery = $stmt->fetch();

if (!$delivery) {
    setFlash('danger', 'Delivery not found.');
    redirect('deliveries/index.php');
}

$pageTitle = 'Delivery #' . $delivery['id_delivery'] . ': ' . $delivery['departure_location'] . ' → ' . $delivery['destination'];

$capacityRatio = $delivery['vehicle_capacity'] > 0 
    ? round(($delivery['weight'] / $delivery['vehicle_capacity']) * 100, 1) 
    : 0;

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="page-title-group">
        <h1>Delivery Tracking #<?= e($delivery['id_delivery']) ?></h1>
        <p><?= e($delivery['departure_location']) ?> &rarr; <?= e($delivery['destination']) ?></p>
    </div>
    <div class="page-actions">
        <a href="<?= url('deliveries/index.php') ?>" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i> Back to Deliveries
        </a>
        <a href="<?= url('deliveries/edit.php?id=' . $id) ?>" class="btn btn-secondary">
            <i class="fa-solid fa-pen-to-square"></i> Edit
        </a>
        <a href="<?= url('deliveries/delete.php?id=' . $id) ?>" 
           class="btn btn-danger"
           data-confirm="Are you sure you want to delete delivery #<?= e($id) ?>?"
           data-title="Delete Delivery">
            <i class="fa-solid fa-trash-can"></i> Delete
        </a>
    </div>
</div>

<!-- Primary Trip Card -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-route"></i> Shipment &amp; Route Details
        </div>
        <div>
            <?= renderStatusBadge($delivery['status']) ?>
        </div>
    </div>
    <div class="card-body">
        <div class="details-grid">
            <div class="detail-item">
                <span class="detail-label">Origin (Departure)</span>
                <span class="detail-value" style="font-size: 1.1rem; color: var(--primary);">
                    <i class="fa-solid fa-location-dot" style="margin-right: 4px;"></i>
                    <?= e($delivery['departure_location']) ?>
                </span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Destination</span>
                <span class="detail-value" style="font-size: 1.1rem; color: var(--success-hover);">
                    <i class="fa-solid fa-flag-checkered" style="margin-right: 4px;"></i>
                    <?= e($delivery['destination']) ?>
                </span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Scheduled Date &amp; Time</span>
                <span class="detail-value">
                    <i class="fa-regular fa-clock" style="color: var(--gray-400); margin-right: 4px;"></i>
                    <?= formatDateTime($delivery['delivery_date'], 'l, M d, Y - H:i') ?>
                </span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Cargo Weight</span>
                <span class="detail-value">
                    <?= formatWeight($delivery['weight']) ?>
                </span>
            </div>
        </div>

        <!-- Vehicle Capacity Usage Bar -->
        <div style="margin-top: 1.75rem; padding-top: 1.25rem; border-top: 1px solid var(--border-color);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; font-size: 0.85rem;">
                <span style="font-weight: 600; color: var(--gray-700);">
                    Vehicle Capacity Utilization:
                </span>
                <span style="font-weight: 700; color: <?= $capacityRatio > 100 ? 'var(--danger)' : 'var(--text-main)' ?>;">
                    <?= $capacityRatio ?>% (<?= formatWeight($delivery['weight']) ?> of <?= formatWeight($delivery['vehicle_capacity']) ?> max)
                </span>
            </div>
            <div style="width: 100%; height: 10px; background: var(--gray-200); border-radius: var(--radius-full); overflow: hidden;">
                <div style="width: <?= min($capacityRatio, 100) ?>%; height: 100%; background: <?= $capacityRatio > 100 ? 'var(--danger)' : ($capacityRatio > 80 ? 'var(--warning)' : 'var(--primary)') ?>; transition: width 0.3s ease;"></div>
            </div>
            <?php if ($capacityRatio > 100): ?>
                <div style="margin-top: 0.4rem; font-size: 0.8rem; color: var(--danger); font-weight: 600;">
                    <i class="fa-solid fa-triangle-exclamation"></i> Warning: Cargo weight exceeds this vehicle's certified maximum payload!
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Relational Cards Grid -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
    <!-- Driver Info Card -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-id-card-clip"></i> Assigned Driver
            </div>
            <a href="<?= url('drivers/view.php?id=' . $delivery['id_driver']) ?>" class="btn btn-outline btn-sm">
                View
            </a>
        </div>
        <div class="card-body">
            <h3 style="font-size: 1.1rem; margin-bottom: 0.75rem;">
                <a href="<?= url('drivers/view.php?id=' . $delivery['id_driver']) ?>">
                    <?= e($delivery['first_name'] . ' ' . $delivery['last_name']) ?>
                </a>
            </h3>
            <p style="font-size: 0.88rem; color: var(--text-muted); margin-bottom: 0.4rem;">
                <i class="fa-solid fa-phone" style="width: 18px;"></i>
                <a href="tel:<?= e($delivery['driver_phone']) ?>"><?= e($delivery['driver_phone']) ?></a>
            </p>
            <p style="font-size: 0.88rem; color: var(--text-muted); margin-bottom: 0.4rem;">
                <i class="fa-solid fa-envelope" style="width: 18px;"></i>
                <a href="mailto:<?= e($delivery['driver_email']) ?>"><?= e($delivery['driver_email']) ?></a>
            </p>
            <p style="font-size: 0.85rem; color: var(--text-muted);">
                <i class="fa-solid fa-id-card" style="width: 18px;"></i>
                License: <code><?= e($delivery['driver_license']) ?></code>
            </p>
        </div>
    </div>

    <!-- Vehicle Info Card -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-truck-front"></i> Assigned Vehicle
            </div>
            <a href="<?= url('vehicles/view.php?id=' . $delivery['id_vehicle']) ?>" class="btn btn-outline btn-sm">
                View
            </a>
        </div>
        <div class="card-body">
            <h3 style="font-size: 1.1rem; margin-bottom: 0.75rem;">
                <a href="<?= url('vehicles/view.php?id=' . $delivery['id_vehicle']) ?>">
                    <?= e($delivery['vehicle_brand'] . ' ' . $delivery['vehicle_model']) ?>
                </a>
            </h3>
            <p style="font-size: 0.88rem; color: var(--text-muted); margin-bottom: 0.4rem;">
                <i class="fa-solid fa-hashtag" style="width: 18px;"></i>
                Plate: <strong style="font-family: monospace; background: var(--gray-100); padding: 2px 6px; border-radius: 4px;"><?= e($delivery['registration_number']) ?></strong>
            </p>
            <p style="font-size: 0.88rem; color: var(--text-muted); margin-bottom: 0.4rem;">
                <i class="fa-solid fa-truck-pickup" style="width: 18px;"></i>
                Type: <?= e($delivery['vehicle_type']) ?>
            </p>
            <p style="font-size: 0.85rem; color: var(--text-muted);">
                <i class="fa-solid fa-weight-hanging" style="width: 18px;"></i>
                Max Payload: <?= formatWeight($delivery['vehicle_capacity']) ?>
            </p>
        </div>
    </div>

    <!-- Goods Type Info Card -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-tags"></i> Cargo Classification
            </div>
            <a href="<?= url('goods-types/view.php?id=' . $delivery['id_goods_type']) ?>" class="btn btn-outline btn-sm">
                View
            </a>
        </div>
        <div class="card-body">
            <h3 style="font-size: 1.1rem; margin-bottom: 0.75rem;">
                <a href="<?= url('goods-types/view.php?id=' . $delivery['id_goods_type']) ?>">
                    <?= e($delivery['goods_type_name']) ?>
                </a>
            </h3>
            <p style="font-size: 0.88rem; color: var(--gray-700); line-height: 1.4;">
                <?= nl2br(e($delivery['goods_type_desc'] ?: 'Standard cargo with no specific handling constraints.')) ?>
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
