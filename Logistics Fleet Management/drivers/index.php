<?php
/**
 * Drivers List
 * Fleet Management System
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDBConnection();
$pageTitle = 'Drivers';
$activeMenu = 'drivers';

// Fetch all drivers with count of assigned deliveries
$stmt = $pdo->query("
    SELECT 
        d.*,
        COUNT(del.id_delivery) AS delivery_count
    FROM driver d
    LEFT JOIN delivery del ON d.id_driver = del.id_driver
    GROUP BY d.id_driver
    ORDER BY d.last_name ASC, d.first_name ASC
");
$drivers = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="page-title-group">
        <h1>Drivers Management</h1>
        <p>Manage fleet operators, contact information, and license records</p>
    </div>
    <div class="page-actions">
        <a href="<?= url('drivers/create.php') ?>" class="btn btn-primary">
            <i class="fa-solid fa-user-plus"></i> Add New Driver
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-users"></i> All Registered Drivers (<?= count($drivers) ?>)
        </div>
        <div class="search-box">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" class="search-input table-search-input" data-table="#driversTable" placeholder="Search driver by name, license, phone...">
        </div>
    </div>

    <div class="card-body" style="padding: 0;">
        <?php if (empty($drivers)): ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fa-solid fa-id-card"></i></div>
                <div class="empty-state-title">No Drivers Found</div>
                <p class="empty-state-text">Start by adding your first driver to assign deliveries and manage trips.</p>
                <a href="<?= url('drivers/create.php') ?>" class="btn btn-primary">
                    <i class="fa-solid fa-user-plus"></i> Add Driver Now
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table" id="driversTable">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Driver Name</th>
                            <th>Contact Phone</th>
                            <th>Email Address</th>
                            <th>License Number</th>
                            <th>Deliveries</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($drivers as $driver): ?>
                            <tr>
                                <td><span class="badge badge-default">#<?= e($driver['id_driver']) ?></span></td>
                                <td>
                                    <strong><?= e($driver['first_name'] . ' ' . $driver['last_name']) ?></strong>
                                </td>
                                <td>
                                    <i class="fa-solid fa-phone" style="font-size: 0.75rem; color: var(--gray-400); margin-right: 4px;"></i>
                                    <?= e($driver['phone']) ?>
                                </td>
                                <td>
                                    <i class="fa-solid fa-envelope" style="font-size: 0.75rem; color: var(--gray-400); margin-right: 4px;"></i>
                                    <?= e($driver['email']) ?>
                                </td>
                                <td>
                                    <code style="background: var(--gray-100); padding: 2px 6px; border-radius: 4px; font-size: 0.82rem; font-weight: 600;">
                                        <?= e($driver['license_number']) ?>
                                    </code>
                                </td>
                                <td>
                                    <span class="badge <?= $driver['delivery_count'] > 0 ? 'badge-transit' : 'badge-default' ?>">
                                        <?= (int)$driver['delivery_count'] ?> assigned
                                    </span>
                                </td>
                                <td>
                                    <div class="table-action-group" style="justify-content: flex-end;">
                                        <a href="<?= url('drivers/view.php?id=' . $driver['id_driver']) ?>" class="btn-icon" title="View Profile">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="<?= url('drivers/edit.php?id=' . $driver['id_driver']) ?>" class="btn-icon" title="Edit Driver">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="<?= url('drivers/delete.php?id=' . $driver['id_driver']) ?>" 
                                           class="btn-icon danger" 
                                           title="Delete Driver"
                                           data-confirm="Are you sure you want to delete <?= e($driver['first_name'] . ' ' . $driver['last_name']) ?>? This cannot be undone."
                                           data-title="Delete Driver">
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
