<?php
/**
 * Delete Goods Type
 * Fleet Management System
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDBConnection();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    setFlash('danger', 'Invalid goods category ID.');
    redirect('goods-types/index.php');
}

// Fetch goods type details
$stmt = $pdo->prepare("SELECT name FROM goodstype WHERE id_goods_type = ?");
$stmt->execute([$id]);
$goodsType = $stmt->fetch();

if (!$goodsType) {
    setFlash('danger', 'Goods category not found.');
    redirect('goods-types/index.php');
}

$categoryName = $goodsType['name'];

// Check foreign key dependencies in delivery table
$count = goodsTypeDeliveryCount($pdo, $id);
if ($count > 0) {
    setFlash('danger', "Cannot delete category '{$categoryName}' because {$count} delivery record(s) contain this cargo type. Please reassign or delete related deliveries first.");
    redirect('goods-types/view.php?id=' . $id);
}

try {
    $stmt = $pdo->prepare("DELETE FROM goodstype WHERE id_goods_type = ?");
    $stmt->execute([$id]);

    setFlash('success', "Goods category '{$categoryName}' was successfully deleted.");
} catch (PDOException $e) {
    setFlash('danger', "Failed to delete goods category: " . $e->getMessage());
}

redirect('goods-types/index.php');
