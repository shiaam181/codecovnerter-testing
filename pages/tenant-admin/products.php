<?php
/**
 * Tenant Admin Products
 */
$tenant = get_current_tenant();
if (!is_admin()) redirect('/t/' . $tenant['slug'] . '/admin/login');

$products = get_products(['tenant_id' => $tenant['id'], 'limit' => 100]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - <?= e($tenant['store_name'] ?? '') ?> Admin</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="sidebar-header"><a href="/t/<?= e($tenant['slug']) ?>/admin" class="sidebar-logo"><?= e($tenant['store_name'] ?? $tenant['slug']) ?></a></div>
        <nav class="sidebar-nav">
            <a href="/t/<?= e($tenant['slug']) ?>/admin" class="sidebar-link">Dashboard</a>
            <a href="/t/<?= e($tenant['slug']) ?>/admin/products" class="sidebar-link active">Products</a>
            <a href="/t/<?= e($tenant['slug']) ?>/admin/upi" class="sidebar-link">UPI Accounts</a>
            <a href="/t/<?= e($tenant['slug']) ?>" class="sidebar-link">View Store</a>
        </nav>
    </aside>
    <div class="admin-main">
        <h1>Products (<?= count($products) ?>)</h1>
        <?php if (empty($products)): ?>
            <div class="empty-state"><p>No products for this store.</p></div>
        <?php else: ?>
            <table class="admin-table">
                <thead><tr><th>Title</th><th>Price</th><th>Active</th></tr></thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td><?= e(truncate($p['title'] ?? '', 40)) ?></td>
                            <td><?= format_price($p['price'] ?? $p['unit_price'] ?? 0) ?></td>
                            <td><span class="badge <?= ($p['is_active'] ?? false) ? 'badge-success' : 'badge-muted' ?>"><?= ($p['is_active'] ?? false) ? 'Yes' : 'No' ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
