<?php
/**
 * Core Helper Functions
 * Fleet Management System
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Escape output for safe HTML display.
 */
function e(?string $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Return project base URL dynamically.
 */
function url(string $path = ''): string
{
    static $base = null;

    if ($base === null) {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        // Find position of known project root folders or scripts
        // Normalize slashes
        $scriptDir = str_replace('\\', '/', dirname($scriptName));

        // If we are inside a subdirectory like /drivers, /vehicles, /deliveries, /goods-types
        $subdirs = ['/drivers', '/vehicles', '/deliveries', '/goods-types', '/config', '/includes'];
        $root = $scriptDir;
        foreach ($subdirs as $sub) {
            if (str_ends_with($root, $sub)) {
                $root = substr($root, 0, -strlen($sub));
                break;
            }
        }
        $base = rtrim($root, '/');
    }

    $cleanPath = ltrim($path, '/');
    return ($base === '' ? '' : $base) . '/' . $cleanPath;
}

/**
 * Set a flash notification message stored in session.
 */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type'    => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message,
    ];
}

/**
 * Retrieve and clear flash notification.
 */
function getFlash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Redirect safely to another page.
 */
function redirect(string $path): void
{
    header("Location: " . url($path));
    exit;
}

/**
 * Format datetime to human readable format.
 */
function formatDateTime(?string $datetime, string $format = 'M d, Y H:i'): string
{
    if (!$datetime) return 'N/A';
    try {
        $dt = new DateTime($datetime);
        return $dt->format($format);
    } catch (Exception) {
        return $datetime;
    }
}

/**
 * Format numeric weight.
 */
function formatWeight(float|int|string|null $weight): string
{
    $val = (float)$weight;
    if ($val >= 1000) {
        return number_format($val, 0) . ' kg (' . number_format($val / 1000, 2) . ' T)';
    }
    return number_format($val, 0) . ' kg';
}

/**
 * Render an HTML status badge.
 */
function renderStatusBadge(string $status, string $context = 'delivery'): string
{
    $clean = e($status);
    $colorClass = 'badge-default';

    switch (strtolower($status)) {
        // Delivery statuses
        case 'pending':
            $colorClass = 'badge-pending';
            break;
        case 'in transit':
            $colorClass = 'badge-transit';
            break;
        case 'delivered':
            $colorClass = 'badge-success';
            break;
        case 'cancelled':
            $colorClass = 'badge-danger';
            break;

        // Vehicle statuses
        case 'available':
            $colorClass = 'badge-success';
            break;
        case 'on trip':
            $colorClass = 'badge-transit';
            break;
        case 'maintenance':
            $colorClass = 'badge-warning';
            break;
        case 'out of service':
        case 'inactive':
            $colorClass = 'badge-danger';
            break;
    }

    return "<span class=\"badge {$colorClass}\">{$clean}</span>";
}

// ========================================================
// Database Access Helpers
// ========================================================

/**
 * Get dashboard metrics.
 */
function getDashboardMetrics(PDO $pdo): array
{
    $metrics = [
        'total_drivers'     => (int)$pdo->query("SELECT COUNT(*) FROM driver")->fetchColumn(),
        'total_vehicles'    => (int)$pdo->query("SELECT COUNT(*) FROM vehicle")->fetchColumn(),
        'total_deliveries'  => (int)$pdo->query("SELECT COUNT(*) FROM delivery")->fetchColumn(),
        'total_goods_types' => (int)$pdo->query("SELECT COUNT(*) FROM goodstype")->fetchColumn(),
        'active_trips'      => (int)$pdo->query("SELECT COUNT(*) FROM delivery WHERE status = 'In Transit'")->fetchColumn(),
        'pending_trips'     => (int)$pdo->query("SELECT COUNT(*) FROM delivery WHERE status = 'Pending'")->fetchColumn(),
        'available_vehicles'=> (int)$pdo->query("SELECT COUNT(*) FROM vehicle WHERE status = 'Available'")->fetchColumn(),
    ];

    // Recent deliveries
    $stmt = $pdo->query("
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
            v.model,
            g.name AS goods_type
        FROM delivery d
        JOIN driver dr ON d.id_driver = dr.id_driver
        JOIN vehicle v ON d.id_vehicle = v.id_vehicle
        JOIN goodstype g ON d.id_goods_type = g.id_goods_type
        ORDER BY d.delivery_date DESC
        LIMIT 6
    ");
    $metrics['recent_deliveries'] = $stmt->fetchAll();

    // Deliveries by status
    $stmt = $pdo->query("
        SELECT status, COUNT(*) as count 
        FROM delivery 
        GROUP BY status
    ");
    $metrics['status_breakdown'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    return $metrics;
}

/**
 * Check if driver is used in deliveries.
 */
function driverDeliveryCount(PDO $pdo, int $id): int
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM delivery WHERE id_driver = ?");
    $stmt->execute([$id]);
    return (int)$stmt->fetchColumn();
}

/**
 * Check if vehicle is used in deliveries.
 */
function vehicleDeliveryCount(PDO $pdo, int $id): int
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM delivery WHERE id_vehicle = ?");
    $stmt->execute([$id]);
    return (int)$stmt->fetchColumn();
}

/**
 * Check if goods type is used in deliveries.
 */
function goodsTypeDeliveryCount(PDO $pdo, int $id): int
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM delivery WHERE id_goods_type = ?");
    $stmt->execute([$id]);
    return (int)$stmt->fetchColumn();
}
