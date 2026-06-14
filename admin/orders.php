<?php
/**
 * Admin Orders List
 */
$orders = get_orders(null, 100);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Admin</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <div class="admin-main">
        <h1>Orders</h1>
        
        <?php if (empty($orders)): ?>
            <div class="empty-state"><p>No orders yet.</p></div>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><code><?= e(substr($order['id'] ?? '', 0, 8)) ?></code></td>
                            <td><?= e($order['customer_name'] ?? '-') ?></td>
                            <td><?= format_price($order['total_amount'] ?? 0) ?></td>
                            <td>
                                <span class="badge <?= ($order['payment_status'] ?? '') === 'paid' ? 'badge-success' : 'badge-warning' ?>">
                                    <?= e(ucfirst($order['payment_status'] ?? 'pending')) ?>
                                </span>
                            </td>
                            <td><?= e(ucfirst($order['order_status'] ?? 'placed')) ?></td>
                            <td><?= e(date('d M Y', strtotime($order['created_at'] ?? 'now'))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
