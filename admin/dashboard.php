<?php
/**
 * Admin Dashboard
 */
$stats = get_admin_stats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <div class="admin-main">
        <h1>Dashboard</h1>
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?= $stats['total_products'] ?></h3>
                <p>Products</p>
            </div>
            <div class="stat-card">
                <h3><?= $stats['total_orders'] ?></h3>
                <p>Total Orders</p>
            </div>
            <div class="stat-card">
                <h3><?= format_price($stats['total_revenue']) ?></h3>
                <p>Revenue</p>
            </div>
            <div class="stat-card">
                <h3><?= $stats['pending_orders'] ?></h3>
                <p>Pending Orders</p>
            </div>
        </div>
    </div>
</div>
</body>
</html>
