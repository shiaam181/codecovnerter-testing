<?php
/**
 * Admin Product Add/Edit Form
 */
$isEdit = isset($_GET['id']);
$product = null;
$error = '';

if ($isEdit) {
    $product = get_product_by_id($_GET['id']);
    if (!$product) redirect('/admin/products');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'slug' => slugify(trim($_POST['title'] ?? '')),
        'price' => (float)($_POST['price'] ?? 0),
        'unit_price' => (float)($_POST['price'] ?? 0),
        'mrp' => !empty($_POST['mrp']) ? (float)$_POST['mrp'] : null,
        'description' => trim($_POST['description'] ?? ''),
        'image_url' => trim($_POST['image_url'] ?? ''),
        'brand' => trim($_POST['brand'] ?? ''),
        'category_slug' => trim($_POST['category_slug'] ?? ''),
        'is_active' => isset($_POST['is_active']),
    ];
    
    if (empty($data['title'])) {
        $error = 'Title is required.';
    } else {
        if ($isEdit) {
            supabase_request('products?id=eq.' . rawurlencode($_GET['id']), 'PATCH', $data);
        } else {
            supabase_request('products', 'POST', $data);
        }
        flash('success', $isEdit ? 'Product updated.' : 'Product added.');
        redirect('/admin/products');
    }
}

$categories = get_categories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Edit' : 'Add' ?> Product - Admin</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <div class="admin-main">
        <h1><?= $isEdit ? 'Edit' : 'Add' ?> Product</h1>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="POST" class="admin-form">
            <div class="form-group">
                <label>Title *</label>
                <input type="text" name="title" required class="form-input" value="<?= e($product['title'] ?? '') ?>">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Price *</label>
                    <input type="number" name="price" required class="form-input" step="0.01" value="<?= e($product['price'] ?? $product['unit_price'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>MRP</label>
                    <input type="number" name="mrp" class="form-input" step="0.01" value="<?= e($product['mrp'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Image URL</label>
                <input type="url" name="image_url" class="form-input" value="<?= e($product['image_url'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Brand</label>
                <input type="text" name="brand" class="form-input" value="<?= e($product['brand'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category_slug" class="form-input">
                    <option value="">-- None --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= e($cat['slug'] ?? '') ?>" <?= ($product['category_slug'] ?? '') === ($cat['slug'] ?? '') ? 'selected' : '' ?>>
                            <?= e($cat['name'] ?? '') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-input" rows="4"><?= e($product['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" <?= ($product['is_active'] ?? true) ? 'checked' : '' ?>>
                    Active
                </label>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Add' ?> Product</button>
                <a href="/admin/products" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
