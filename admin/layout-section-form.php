<?php
/**
 * Admin Homepage Layout Section Form
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'type' => trim($_POST['type'] ?? ''),
        'title' => trim($_POST['title'] ?? ''),
        'sort_order' => (int)($_POST['sort_order'] ?? 0),
        'is_active' => isset($_POST['is_active']),
    ];
    supabase_request('homepage_sections', 'POST', $data);
    flash('success', 'Section added.');
    redirect('/admin/homepage-layout');
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Add Section - Admin</title><link rel="stylesheet" href="/assets/css/style.css"></head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <div class="admin-main">
        <h1>Add Homepage Section</h1>
        <form method="POST" class="admin-form">
            <div class="form-group"><label>Type</label><select name="type" class="form-input"><option value="banner">Banner</option><option value="products">Products</option><option value="categories">Categories</option></select></div>
            <div class="form-group"><label>Title</label><input type="text" name="title" class="form-input"></div>
            <div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" class="form-input" value="0"></div>
            <div class="form-group"><label class="checkbox-label"><input type="checkbox" name="is_active" checked> Active</label></div>
            <div class="form-actions"><button type="submit" class="btn btn-primary">Add</button><a href="/admin/homepage-layout" class="btn btn-outline">Cancel</a></div>
        </form>
    </div>
</div>
</body>
</html>
