<?php
/**
 * Admin Category Add/Edit Form
 */
$isEdit = isset($_GET['id']);
$category = null;

if ($isEdit) {
    $result = supabase_request('categories?id=eq.' . rawurlencode($_GET['id']) . '&limit=1');
    $category = (!empty($result) && is_array($result)) ? $result[0] : null;
    if (!$category) redirect('/admin/categories');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'slug' => slugify(trim($_POST['name'] ?? '')),
        'icon_url' => trim($_POST['icon_url'] ?? ''),
        'sort_order' => (int)($_POST['sort_order'] ?? 0),
    ];
    if ($isEdit) {
        supabase_request('categories?id=eq.' . rawurlencode($_GET['id']), 'PATCH', $data);
    } else {
        supabase_request('categories', 'POST', $data);
    }
    flash('success', $isEdit ? 'Category updated.' : 'Category added.');
    redirect('/admin/categories');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Edit' : 'Add' ?> Category - Admin</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <div class="admin-main">
        <h1><?= $isEdit ? 'Edit' : 'Add' ?> Category</h1>
        <form method="POST" class="admin-form">
            <div class="form-group">
                <label>Name *</label>
                <input type="text" name="name" required class="form-input" value="<?= e($category['name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Icon URL</label>
                <input type="url" name="icon_url" class="form-input" value="<?= e($category['icon_url'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Sort Order</label>
                <input type="number" name="sort_order" class="form-input" value="<?= e($category['sort_order'] ?? 0) ?>">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Add' ?></button>
                <a href="/admin/categories" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
