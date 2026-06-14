<?php
/**
 * Admin Products List
 */
// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['admin_action'] ?? '') === 'delete_product') {
    $deleteId = $_POST['product_id'] ?? '';
    if ($deleteId) {
        supabase_request('products?id=eq.' . rawurlencode($deleteId), 'DELETE');
        flash('success', 'Product deleted.');
    }
    redirect('/admin/products');
}

$products = get_products(['limit' => 100]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Admin</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <div class="admin-main">
        <div class="admin-header-row">
            <h1>Products</h1>
            <a href="/admin/products/new" class="btn btn-primary">Add Product</a>
        </div>
        
        <?php $flash = get_flash('success'); if ($flash): ?>
            <div class="flash flash-success"><?= e($flash) ?></div>
        <?php endif; ?>
        
        <?php if (empty($products)): ?>
            <div class="empty-state"><p>No products yet.</p></div>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Price</th>
                        <th>MRP</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <?php if (!empty($product['image_url'])): ?>
                                    <img src="<?= e($product['image_url']) ?>" alt="" style="width:40px;height:40px;object-fit:cover;border-radius:4px;">
                                <?php else: ?>
                                    <span>-</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e(truncate($product['title'] ?? '', 40)) ?></td>
                            <td><?= format_price($product['price'] ?? $product['unit_price'] ?? 0) ?></td>
                            <td><?= format_price($product['mrp'] ?? '') ?></td>
                            <td class="actions-cell">
                                <a href="/admin/products/edit?id=<?= e($product['id'] ?? '') ?>" class="btn btn-sm">Edit</a>
                                <form method="POST" style="display:inline" onsubmit="return confirm('Delete this product?')">
                                    <input type="hidden" name="admin_action" value="delete_product">
                                    <input type="hidden" name="product_id" value="<?= e($product['id'] ?? '') ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
