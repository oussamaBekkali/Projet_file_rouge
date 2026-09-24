<?php
/**
 * Global Sidebar Navigation
 * Fleet Management System
 */
$activeMenu = $activeMenu ?? 'dashboard';

// Fetch quick counts for badges if database connection is available
$navCounts = [
    'drivers'     => 0,
    'vehicles'    => 0,
    'deliveries'  => 0,
    'goods-types' => 0,
];
if (isset($pdo)) {
    try {
        $navCounts['drivers']     = (int)$pdo->query("SELECT COUNT(*) FROM driver")->fetchColumn();
        $navCounts['vehicles']    = (int)$pdo->query("SELECT COUNT(*) FROM vehicle")->fetchColumn();
        $navCounts['deliveries']  = (int)$pdo->query("SELECT COUNT(*) FROM delivery")->fetchColumn();
        $navCounts['goods-types'] = (int)$pdo->query("SELECT COUNT(*) FROM goodstype")->fetchColumn();
    } catch (Exception) {
        // Fallback silently
    }
}
?>
<aside class="sidebar" id="appSidebar">
    <div class="sidebar-header">
        <a href="<?= url('index.php') ?>" class="brand-logo">
            <div class="brand-icon">
                <i class="fa-solid fa-truck-fast"></i>
            </div>
            <div class="brand-text">
                <span class="brand-title">FleetTrack</span>
                <span class="brand-subtitle">Logistics Hub</span>
            </div>
        </a>
    </div>

    <div class="sidebar-nav-container">
        <div class="nav-section-label">MAIN MENU</div>
        <ul class="nav-list">
            <li class="nav-item">
                <a href="<?= url('index.php') ?>" class="nav-link <?= $activeMenu === 'dashboard' ? 'active' : '' ?>">
                    <i class="fa-solid fa-chart-pie nav-icon"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= url('deliveries/index.php') ?>" class="nav-link <?= $activeMenu === 'deliveries' ? 'active' : '' ?>">
                    <i class="fa-solid fa-boxes-packing nav-icon"></i>
                    <span class="nav-text">Deliveries</span>
                    <span class="nav-badge"><?= $navCounts['deliveries'] ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= url('vehicles/index.php') ?>" class="nav-link <?= $activeMenu === 'vehicles' ? 'active' : '' ?>">
                    <i class="fa-solid fa-truck-front nav-icon"></i>
                    <span class="nav-text">Vehicles</span>
                    <span class="nav-badge"><?= $navCounts['vehicles'] ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= url('drivers/index.php') ?>" class="nav-link <?= $activeMenu === 'drivers' ? 'active' : '' ?>">
                    <i class="fa-solid fa-id-card-clip nav-icon"></i>
                    <span class="nav-text">Drivers</span>
                    <span class="nav-badge"><?= $navCounts['drivers'] ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= url('goods-types/index.php') ?>" class="nav-link <?= $activeMenu === 'goods-types' ? 'active' : '' ?>">
                    <i class="fa-solid fa-tags nav-icon"></i>
                    <span class="nav-text">Goods Types</span>
                    <span class="nav-badge"><?= $navCounts['goods-types'] ?></span>
                </a>
            </li>
        </ul>

        <div class="nav-section-label">QUICK ACTIONS</div>
        <div class="sidebar-quick-action">
            <a href="<?= url('deliveries/create.php') ?>" class="btn btn-primary btn-block">
                <i class="fa-solid fa-plus"></i> New Delivery
            </a>
        </div>
    </div>

    <div class="sidebar-footer">
        <div class="user-profile-widget">
            <div class="user-avatar">
                <i class="fa-solid fa-user-tie"></i>
            </div>
            <div class="user-info">
                <span class="user-name">Fleet Manager</span>
                <span class="user-role">Administrator</span>
            </div>
        </div>
    </div>
</aside>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>
