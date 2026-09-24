<?php
/**
 * Vehicles List
 * Fleet Management System
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDBConnection();
$pageTitle = 'Vehicles';
$activeMenu = 'vehicles';

// Optional filter by status or type
$statusFilter = trim($_GET['status'] ?? '');
$typeFilter = trim($_GET['type'] ?? '');

$sql = "
    SELECT 
        v.*,
        COUNT(del.id_delivery) AS delivery_count
    FROM vehicle v
    LEFT JOIN delivery del ON v.id_vehicle = del.id_vehicle
    WHERE 1=1
";
$params = [];

if ($statusFilter !== '') {
    $sql .= " AND v.status = ?";
    $params[] = $statusFilter;
}
if ($typeFilter !== '') {
    $sql .= " AND v.vehicle_type = ?";
    $params[] = $typeFilter;
}

$sql .= " GROUP BY v.id_vehicle ORDER BY v.brand ASC, v.model ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$vehicles = $stmt->fetchAll();

// Vehicle types and statuses for filter dropdowns
$vehicleTypes = ['Truck', 'Van', 'Pickup', 'Trailer', 'Semi-Trailer', 'Other'];
$vehicleStatuses = ['Available', 'On Trip', 'Maintenance', 'Out of Service'];

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="page-title-group">
        <h1>Vehicles Fleet</h1>
        <p>Monitor and maintain transport units, cargo capacities, and operational statuses</p>
    </div>
    <div class="page-actions">
        <a href="<?= url('vehicles/create.php') ?>" class="btn btn-primary">
            <i class="fa-solid fa-truck"></i> Add New Vehicle
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-truck-moving"></i> Fleet Inventory (<?= count($vehicles) ?>)
        </div>
        <div class="page-actions">
            <!-- Filter by Status -->
            <form method="GET" action="<?= url('vehicles/index.php') ?>" style="display: flex; gap: 0.5rem; align-items: center;">
                <select name="status" class="form-select" style="width: auto; padding: 0.4rem 0.75rem; font-size: 0.85rem;" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <?php foreach ($vehicleStatuses as $st): ?>
                        <option value="<?= e($st) ?>" <?= $statusFilter === $st ? 'selected' : '' ?>><?= e($st) ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="type" class="form-select" style="width: auto; padding: 0.4rem 0.75rem; font-size: 0.85rem;" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <?php foreach ($vehicleTypes as $vt): ?>
                        <option value="<?= e($vt) ?>" <?= $typeFilter === $vt ? 'selected' : '' ?>><?= e($vt) ?></option>
                    <?php endforeach; ?>
                </select>

                <?php if ($statusFilter !== '' || $typeFilter !== ''): ?>
                    <a href="<?= url('vehicles/index.php') ?>" class="btn btn-outline btn-sm" title="Clear Filters">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                <?php endif; ?>
            </form>

            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" class="search-input table-search-input" data-table="#vehiclesTable" placeholder="Search registration, brand, model...">
            </div>
        </div>
    </div>

    <div class="card-body" style="padding: 0;">
        <?php if (empty($vehicles)): ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fa-solid fa-truck"></i></div>
                <div class="empty-state-title">No Vehicles Found</div>
                <p class="empty-state-text">No vehicles match the selected criteria or none have been registered yet.</p>
                <a href="<?= url('vehicles/create.php') ?>" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i> Add New Vehicle
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table" id="vehiclesTable">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Registration</th>
                            <th>Brand &amp; Model</th>
                            <th>Vehicle Type</th>
                            <th>Max Capacity</th>
                            <th>Status</th>
                            <th>Deliveries</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vehicles as $v): ?>
                            <tr>
                                <td><span class="badge badge-default">#<?= e($v['id_vehicle']) ?></span></td>
                                <td>
                                    <strong style="letter-spacing: 0.05em; background: var(--gray-100); padding: 3px 8px; border-radius: 4px; font-family: monospace;">
                                        <?= e($v['registration_number']) ?>
                                    </strong>
                                </td>
                                <td>
                                    <strong><?= e($v['brand']) ?></strong> <?= e($v['model']) ?>
                                </td>
                                <td>
                                    <span class="badge badge-default">
                                        <i class="fa-solid fa-truck-pickup" style="font-size: 0.7rem;"></i> <?= e($v['vehicle_type']) ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?= formatWeight($v['capacity']) ?></strong>
                                </td>
                                <td>
                                    <?= renderStatusBadge($v['status'], 'vehicle') ?>
                                </td>
                                <td>
                                    <span class="badge <?= $v['delivery_count'] > 0 ? 'badge-transit' : 'badge-default' ?>">
                                        <?= (int)$v['delivery_count'] ?> trips
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <div class="table-action-group" style="justify-content: flex-end;">
                                        <a href="<?= url('vehicles/view.php?id=' . $v['id_vehicle']) ?>" class="btn-icon" title="View Vehicle Details">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="<?= url('vehicles/edit.php?id=' . $v['id_vehicle']) ?>" class="btn-icon" title="Edit Vehicle">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="<?= url('vehicles/delete.php?id=' . $v['id_vehicle']) ?>" 
                                           class="btn-icon danger" 
                                           title="Delete Vehicle"
                                           data-confirm="Are you sure you want to delete vehicle <?= e($v['brand'] . ' ' . $v['model']) ?> (<?= e($v['registration_number']) ?>)?"
                                           data-title="Delete Vehicle">
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
