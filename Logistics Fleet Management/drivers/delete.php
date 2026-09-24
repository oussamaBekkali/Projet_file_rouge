<?php
/**
 * Delete Driver
 * Fleet Management System
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDBConnection();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    setFlash('danger', 'Invalid driver ID.');
    redirect('drivers/index.php');
}

// Fetch driver details
$stmt = $pdo->prepare("SELECT first_name, last_name FROM driver WHERE id_driver = ?");
$stmt->execute([$id]);
$driver = $stmt->fetch();

if (!$driver) {
    setFlash('danger', 'Driver not found.');
    redirect('drivers/index.php');
}

$driverName = $driver['first_name'] . ' ' . $driver['last_name'];

// Check foreign key dependencies in delivery table
$count = driverDeliveryCount($pdo, $id);
if ($count > 0) {
    setFlash('danger', "Cannot delete driver '{$driverName}' because they are assigned to {$count} delivery record(s). Please reassign or delete related deliveries first.");
    redirect('drivers/view.php?id=' . $id);
}

try {
    $stmt = $pdo->prepare("DELETE FROM driver WHERE id_driver = ?");
    $stmt->execute([$id]);

    setFlash('success', "Driver '{$driverName}' was successfully deleted.");
} catch (PDOException $e) {
    setFlash('danger', "Failed to delete driver: " . $e->getMessage());
}

redirect('drivers/index.php');
