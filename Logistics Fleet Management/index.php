<?php
/**
 * Fleet Management Dashboard
 * Fleet Management System
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getDBConnection();
$pageTitle = 'Fleet Dashboard';
$activeMenu = 'dashboard';

// Fetch metrics
$metrics = getDashboardMetrics($pdo);

// Vehicles by status breakdown
$vehicleStatusCounts = $pdo->query("
    SELECT status, COUNT(*) as count 
    FROM vehicle 
    GROUP BY status
")->fetchAll(PDO::FETCH_KEY_PAIR);

// Goods types breakdown with delivery count
$topGoods = $pdo->query("
    SELECT g.name, COUNT(d.id_delivery) as count
    FROM goodstype g
    LEFT JOIN delivery d ON g.id_goods_type = d.id_goods_type
    GROUP BY g.id_goods_type
    ORDER BY count DESC
    LIMIT 4
")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div class="page-title-group">
        <h1>Logistics &amp; Fleet Overview</h1>
        <p>Real-time telemetry, trip dispatching, and asset allocation</p>
    </div>
    <div class="page-actions">
        <a href="<?= url('deliveries/create.php') ?>" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> New Delivery
        </a>
    </div>
</div>

<!-- Primary Key Performance Metric Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-details">
            <span class="stat-label">Active Drivers</span>
            <span class="stat-value"><?= number_format($metrics['total_drivers']) ?></span>
            <span class="stat-meta">
                <a href="<?= url('drivers/index.php') ?>">View all drivers &rarr;</a>
            </span>
        </div>
        <div class="stat-icon purple">
            <i class="fa-solid fa-id-card"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-details">
            <span class="stat-label">Fleet Vehicles</span>
            <span class="stat-value"><?= number_format($metrics['total_vehicles']) ?></span>
            <span class="stat-meta" style="color: var(--success-hover);">
                <strong><?= $metrics['available_vehicles'] ?></strong> available right now
            </span>
        </div>
        <div class="stat-icon blue">
            <i class="fa-solid fa-truck-moving"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-details">
            <span class="stat-label">Total Deliveries</span>
            <span class="stat-value"><?= number_format($metrics['total_deliveries']) ?></span>
            <span class="stat-meta" style="color: var(--info);">
                <strong><?= $metrics['active_trips'] ?></strong> currently in transit
            </span>
        </div>
        <div class="stat-icon green">
            <i class="fa-solid fa-boxes-stacked"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-details">
            <span class="stat-label">Cargo Types</span>
            <span class="stat-value"><?= number_format($metrics['total_goods_types']) ?></span>
            <span class="stat-meta">
                <a href="<?= url('goods-types/index.php') ?>">Manage categories &rarr;</a>
            </span>
        </div>
        <div class="stat-icon amber">
            <i class="fa-solid fa-tags"></i>
        </div>
    </div>
</div>

<!-- Two-Column Section: Recent Activity & Fleet Status breakdown -->
<div class="dashboard-columns">
    <!-- Recent Deliveries Table Card -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-clock-rotate-left"></i> Recent Shipments
            </div>
            <a href="<?= url('deliveries/index.php') ?>" class="btn btn-outline btn-sm">
                View All Deliveries &rarr;
            </a>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($metrics['recent_deliveries'])): ?>
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="fa-solid fa-boxes-packing"></i></div>
                    <div class="empty-state-title">No Deliveries Found</div>
                    <p class="empty-state-text">Create your first shipment to see real-time dispatch data here.</p>
                    <a href="<?= url('deliveries/create.php') ?>" class="btn btn-primary btn-sm">
                        Schedule a Delivery
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th>Route (Origin &rarr; Destination)</th>
                                <th>Scheduled</th>
                                <th>Driver</th>
                                <th>Vehicle</th>
                                <th>Status</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($metrics['recent_deliveries'] as $del): ?>
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
                                    </td>
                                    <td><?= renderStatusBadge($del['status']) ?></td>
                                    <td style="text-align: right;">
                                        <a href="<?= url('deliveries/view.php?id=' . $del['id_delivery']) ?>" class="btn-icon" title="View Shipment">
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

    <!-- Fleet Status & Quick Health Summary -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        <!-- Vehicle Status Breakdown -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header">
                <div class="card-title">
                    <i class="fa-solid fa-truck-front"></i> Vehicle Availability
                </div>
                <a href="<?= url('vehicles/index.php') ?>" class="btn btn-outline btn-sm">Manage</a>
            </div>
            <div class="card-body">
                <div style="display: flex; flex-direction: column; gap: 0.9rem;">
                    <?php
                    $vStatuses = [
                        'Available'      => ['color' => 'var(--success)', 'icon' => 'fa-check'],
                        'On Trip'        => ['color' => 'var(--info)',    'icon' => 'fa-route'],
                        'Maintenance'    => ['color' => 'var(--warning)', 'icon' => 'fa-wrench'],
                        'Out of Service' => ['color' => 'var(--danger)',  'icon' => 'fa-ban'],
                    ];
                    $totalV = max(1, $metrics['total_vehicles']);
                    foreach ($vStatuses as $sName => $meta):
                        $count = (int)($vehicleStatusCounts[$sName] ?? 0);
                        $pct = round(($count / $totalV) * 100);
                    ?>
                        <div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 0.25rem;">
                                <span>
                                    <i class="fa-solid <?= $meta['icon'] ?>" style="color: <?= $meta['color'] ?>; width: 16px;"></i>
                                    <strong><?= $sName ?></strong>
                                </span>
                                <span style="font-weight: 600; color: var(--gray-600);"><?= $count ?> units (<?= $pct ?>%)</span>
                            </div>
                            <div style="width: 100%; height: 6px; background: var(--gray-100); border-radius: 99px; overflow: hidden;">
                                <div style="width: <?= $pct ?>%; height: 100%; background: <?= $meta['color'] ?>;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Top Cargo Classifications -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header">
                <div class="card-title">
                    <i class="fa-solid fa-layer-group"></i> Active Cargo Types
                </div>
                <a href="<?= url('goods-types/index.php') ?>" class="btn btn-outline btn-sm">Explore</a>
            </div>
            <div class="card-body">
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <?php foreach ($topGoods as $tg): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0.75rem; background: var(--gray-50); border-radius: var(--radius-md); font-size: 0.88rem;">
                            <span>
                                <i class="fa-solid fa-box" style="color: var(--primary); margin-right: 6px;"></i>
                                <?= e($tg['name']) ?>
                            </span>
                            <span class="badge badge-default">
                                <?= (int)$tg['count'] ?> shipments
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
