<?php
/**
 * Goods Types List
 * Fleet Management System
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDBConnection();
$pageTitle = 'Goods Types';
$activeMenu = 'goods-types';

$stmt = $pdo->query("
    SELECT 
        g.*,
        COUNT(d.id_delivery) AS delivery_count
    FROM goodstype g
    LEFT JOIN delivery d ON g.id_goods_type = d.id_goods_type
    GROUP BY g.id_goods_type
    ORDER BY g.name ASC
");
$goodsTypes = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="page-title-group">
        <h1>Goods &amp; Cargo Types</h1>
        <p>Classify transport items, cargo categories, and handling descriptions</p>
    </div>
    <div class="page-actions">
        <a href="<?= url('goods-types/create.php') ?>" class="btn btn-primary">
            <i class="fa-solid fa-tag"></i> Add New Goods Type
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-tags"></i> Registered Cargo Categories (<?= count($goodsTypes) ?>)
        </div>
        <div class="search-box">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" class="search-input table-search-input" data-table="#goodsTable" placeholder="Search goods type or description...">
        </div>
    </div>

    <div class="card-body" style="padding: 0;">
        <?php if (empty($goodsTypes)): ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fa-solid fa-box"></i></div>
                <div class="empty-state-title">No Goods Types Found</div>
                <p class="empty-state-text">Add your first goods category to classify cargo on fleet shipments.</p>
                <a href="<?= url('goods-types/create.php') ?>" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i> Add Goods Type
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table" id="goodsTable">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Category Name</th>
                            <th>Description</th>
                            <th>Deliveries Carried</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($goodsTypes as $g): ?>
                            <tr>
                                <td><span class="badge badge-default">#<?= e($g['id_goods_type']) ?></span></td>
                                <td>
                                    <strong><i class="fa-solid fa-box" style="color: var(--primary); margin-right: 6px;"></i><?= e($g['name']) ?></strong>
                                </td>
                                <td>
                                    <?= e($g['description'] ?: 'No description provided') ?>
                                </td>
                                <td>
                                    <span class="badge <?= $g['delivery_count'] > 0 ? 'badge-transit' : 'badge-default' ?>">
                                        <?= (int)$g['delivery_count'] ?> deliveries
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <div class="table-action-group" style="justify-content: flex-end;">
                                        <a href="<?= url('goods-types/view.php?id=' . $g['id_goods_type']) ?>" class="btn-icon" title="View Related Deliveries">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="<?= url('goods-types/edit.php?id=' . $g['id_goods_type']) ?>" class="btn-icon" title="Edit Goods Type">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="<?= url('goods-types/delete.php?id=' . $g['id_goods_type']) ?>" 
                                           class="btn-icon danger" 
                                           title="Delete Goods Type"
                                           data-confirm="Are you sure you want to delete category '<?= e($g['name']) ?>'?"
                                           data-title="Delete Goods Type">
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
