<?php
/**
 * Tenant Admin Dashboard
 */
$tenant = get_current_tenant();
if (!is_admin()) {
    redirect('/t/' . $tenant['slug'] . '/admin/login');
}
$stats = get_admin_stats($tenant['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= e($tenant['store_name'] ?? '') ?> Admin</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <a href="/t/<?= e($tenant['slug']) ?>/admin" class="sidebar-logo"><?= e($tenant['store_name'] ?? $tenant['slug']) ?></a>
        </div>
        <nav class="sidebar-nav">
            <a href="/t/<?= e($tenant['slug']) ?>/admin" class="sidebar-link active">Dashboard</a>
            <a href="/t/<?= e($tenant['slug']) ?>/admin/products" class="sidebar-link">Products</a>
            <a href="/t/<?= e($tenant['slug']) ?>/admin/upi" class="sidebar-link">UPI Accounts</a>
            <a href="/t/<?= e($tenant['slug']) ?>" class="sidebar-link">View Store</a>
        </nav>
        <div class="sidebar-footer">
            <form method="POST" action="/admin">
                <input type="hidden" name="admin_action" value="logout">
                <button type="submit" class="btn btn-sm btn-outline">Logout</button>
            </form>
        </div>
    </aside>
    <div class="admin-main">
        <h1>Dashboard</h1>
        <div class="stats-grid">
            <div class="stat-card"><h3><?= $stats['total_products'] ?></h3><p>Products</p></div>
            <div class="stat-card"><h3><?= $stats['total_orders'] ?></h3><p>Orders</p></div>
            <div class="stat-card"><h3><?= format_price($stats['total_revenue']) ?></h3><p>Revenue</p></div>
            <div class="stat-card"><h3><?= $stats['pending_orders'] ?></h3><p>Pending</p></div>
        </div>
    </div>
</div>
</body>
</html>
