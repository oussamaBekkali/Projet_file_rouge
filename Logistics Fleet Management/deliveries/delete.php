<?php
/**
 * Delete Delivery
 * Fleet Management System
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDBConnection();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    setFlash('danger', 'Invalid delivery ID.');
    redirect('deliveries/index.php');
}

// Fetch delivery info
$stmt = $pdo->prepare("SELECT departure_location, destination FROM delivery WHERE id_delivery = ?");
$stmt->execute([$id]);
$delivery = $stmt->fetch();

if (!$delivery) {
    setFlash('danger', 'Delivery not found.');
    redirect('deliveries/index.php');
}

$routeName = $delivery['departure_location'] . ' → ' . $delivery['destination'];

try {
    $stmt = $pdo->prepare("DELETE FROM delivery WHERE id_delivery = ?");
    $stmt->execute([$id]);

    setFlash('success', "Delivery #{$id} ({$routeName}) was successfully removed.");
} catch (PDOException $e) {
    setFlash('danger', "Failed to delete delivery: " . $e->getMessage());
}

redirect('deliveries/index.php');
