<?php
/**
 * Global Header Include
 * Fleet Management System
 */
if (!isset($pdo)) {
    require_once __DIR__ . '/../config/database.php';
    $pdo = getDBConnection();
}
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? 'Fleet Management System';
$activeMenu = $activeMenu ?? 'dashboard';
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> — FleetTrack Admin</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Custom Modern Vanilla CSS -->
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>
<div class="app-layout">
    <?php require __DIR__ . '/sidebar.php'; ?>

    <div class="main-wrapper">
        <!-- Top Navigation Bar -->
        <header class="topbar">
            <div class="topbar-left">
                <button type="button" class="btn-sidebar-toggle" id="sidebarToggle" aria-label="Toggle Navigation">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="topbar-breadcrumb">
                    <span class="breadcrumb-item"><i class="fa-solid fa-truck-ramp-box"></i> FleetTrack</span>
                    <span class="breadcrumb-separator"><i class="fa-solid fa-chevron-right"></i></span>
                    <span class="breadcrumb-active"><?= e($pageTitle) ?></span>
                </div>
            </div>
            <div class="topbar-right">
                <div class="quick-status">
                    <span class="status-indicator online"></span>
                    <span class="status-label">MySQL Connected</span>
                </div>
                <div class="system-time" id="systemClock">
                    <?= date('H:i') ?>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="page-content">
            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible" role="alert">
                    <div class="alert-icon">
                        <?php if ($flash['type'] === 'success'): ?>
                            <i class="fa-solid fa-circle-check"></i>
                        <?php elseif ($flash['type'] === 'danger'): ?>
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        <?php elseif ($flash['type'] === 'warning'): ?>
                            <i class="fa-solid fa-circle-exclamation"></i>
                        <?php else: ?>
                            <i class="fa-solid fa-circle-info"></i>
                        <?php endif; ?>
                    </div>
                    <div class="alert-message"><?= e($flash['message']) ?></div>
                    <button type="button" class="alert-close" onclick="this.parentElement.remove();">&times;</button>
                </div>
            <?php endif; ?>
