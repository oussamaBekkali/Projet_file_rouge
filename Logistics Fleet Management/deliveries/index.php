<?php
/**
 * Deliveries List
 * Fleet Management System
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDBConnection();
$pageTitle = 'Deliveries';
$activeMenu = 'deliveries';

$statusFilter = trim($_GET['status'] ?? '');

$sql = "
    SELECT 
        d.id_delivery,
        d.departure_location,
        d.destination,
        d.delivery_date,
        d.status,
        d.weight,
        d.id_driver,
        d.id_vehicle,
        d.id_goods_type,
        CONCAT(dr.first_name, ' ', dr.last_name) AS driver_name,
        dr.phone AS driver_phone,
        v.registration_number,
        v.brand,
        v.model,
        v.capacity AS vehicle_capacity,
        g.name AS goods_type
    FROM delivery d
    JOIN driver dr ON d.id_driver = dr.id_driver
    JOIN vehicle v ON d.id_vehicle = v.id_vehicle
    JOIN goodstype g ON d.id_goods_type = g.id_goods_type
    WHERE 1=1
";
$params = [];

if ($statusFilter !== '') {
    $sql .= " AND d.status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY d.delivery_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$deliveries = $stmt->fetchAll();

$statuses = ['Pending', 'In Transit', 'Delivered', 'Cancelled'];

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="page-title-group">
        <h1>Deliveries &amp; Shipments</h1>
        <p>Schedule, track, and manage fleet transport dispatches</p>
    </div>
    <div class="page-actions">
        <a href="<?= url('deliveries/create.php') ?>" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> New Delivery
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-boxes-packing"></i> Shipments Log (<?= count($deliveries) ?>)
        </div>
        <div class="page-actions">
            <!-- Filter by Status -->
            <form method="GET" action="<?= url('deliveries/index.php') ?>" style="display: flex; gap: 0.5rem; align-items: center;">
                <select name="status" class="form-select" style="width: auto; padding: 0.4rem 0.75rem; font-size: 0.85rem;" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <?php foreach ($statuses as $st): ?>
                        <option value="<?= e($st) ?>" <?= $statusFilter === $st ? 'selected' : '' ?>><?= e($st) ?></option>
                    <?php endforeach; ?>
                </select>

                <?php if ($statusFilter !== ''): ?>
                    <a href="<?= url('deliveries/index.php') ?>" class="btn btn-outline btn-sm" title="Clear Filter">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                <?php endif; ?>
            </form>

            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" class="search-input table-search-input" data-table="#deliveriesTable" placeholder="Search route, driver, vehicle, cargo...">
            </div>
        </div>
    </div>

    <div class="card-body" style="padding: 0;">
        <?php if (empty($deliveries)): ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fa-solid fa-truck-ramp-box"></i></div>
                <div class="empty-state-title">No Deliveries Found</div>
                <p class="empty-state-text">No delivery records match your current filter. Create a new dispatch now.</p>
                <a href="<?= url('deliveries/create.php') ?>" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i> Schedule New Delivery
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table" id="deliveriesTable">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Route (Origin &rarr; Destination)</th>
                            <th>Date &amp; Time</th>
                            <th>Driver</th>
                            <th>Vehicle</th>
                            <th>Goods Type</th>
                            <th>Weight</th>
                            <th>Status</th>
                            <th style="text-align: right;">Actions</th>
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
                                <td>
                                    <span style="white-space: nowrap;">
                                        <?= formatDateTime($del['delivery_date']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= url('drivers/view.php?id=' . $del['id_driver']) ?>">
                                        <i class="fa-solid fa-user" style="font-size: 0.75rem; margin-right: 4px; color: var(--gray-400);"></i>
                                        <?= e($del['driver_name']) ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="<?= url('vehicles/view.php?id=' . $del['id_vehicle']) ?>">
                                        <?= e($del['brand'] . ' ' . $del['model']) ?>
                                    </a>
                                    <small style="color: var(--gray-500); display: block; font-family: monospace;">
                                        <?= e($del['registration_number']) ?>
                                    </small>
                                </td>
                                <td>
                                    <a href="<?= url('goods-types/view.php?id=' . $del['id_goods_type']) ?>">
                                        <?= e($del['goods_type']) ?>
                                    </a>
                                </td>
                                <td>
                                    <strong><?= formatWeight($del['weight']) ?></strong>
                                    <?php if ($del['weight'] > $del['vehicle_capacity']): ?>
                                        <span title="Overload: cargo exceeds vehicle max capacity (<?= formatWeight($del['vehicle_capacity']) ?>)" style="color: var(--danger); margin-left: 4px;">
                                            <i class="fa-solid fa-triangle-exclamation"></i>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?= renderStatusBadge($del['status']) ?></td>
                                <td style="text-align: right;">
                                    <div class="table-action-group" style="justify-content: flex-end;">
                                        <a href="<?= url('deliveries/view.php?id=' . $del['id_delivery']) ?>" class="btn-icon" title="View Delivery Details">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="<?= url('deliveries/edit.php?id=' . $del['id_delivery']) ?>" class="btn-icon" title="Edit Delivery">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="<?= url('deliveries/delete.php?id=' . $del['id_delivery']) ?>" 
                                           class="btn-icon danger" 
                                           title="Delete Delivery"
                                           data-confirm="Are you sure you want to delete delivery #<?= e($del['id_delivery']) ?> from <?= e($del['departure_location']) ?> to <?= e($del['destination']) ?>?"
                                           data-title="Delete Delivery">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </div>
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
