<?php
/**
 * Delete Vehicle
 * Fleet Management System
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDBConnection();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    setFlash('danger', 'Invalid vehicle ID.');
    redirect('vehicles/index.php');
}

// Fetch vehicle details
$stmt = $pdo->prepare("SELECT brand, model, registration_number FROM vehicle WHERE id_vehicle = ?");
$stmt->execute([$id]);
$vehicle = $stmt->fetch();

if (!$vehicle) {
    setFlash('danger', 'Vehicle not found.');
    redirect('vehicles/index.php');
}

$vehicleName = $vehicle['brand'] . ' ' . $vehicle['model'] . ' (' . $vehicle['registration_number'] . ')';

// Check foreign key dependencies in delivery table
$count = vehicleDeliveryCount($pdo, $id);
if ($count > 0) {
    setFlash('danger', "Cannot delete vehicle '{$vehicleName}' because it is assigned to {$count} delivery trip(s). Please reassign or remove related deliveries first.");
    redirect('vehicles/view.php?id=' . $id);
}

try {
    $stmt = $pdo->prepare("DELETE FROM vehicle WHERE id_vehicle = ?");
    $stmt->execute([$id]);

    setFlash('success', "Vehicle '{$vehicleName}' was successfully removed from the fleet.");
} catch (PDOException $e) {
    setFlash('danger', "Failed to delete vehicle: " . $e->getMessage());
}

redirect('vehicles/index.php');
