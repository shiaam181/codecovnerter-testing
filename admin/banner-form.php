<?php
/**
 * Admin Banner Add/Edit Form
 */
$isEdit = isset($_GET['id']);
$banner = null;
if ($isEdit) {
    $result = supabase_request('banners?id=eq.' . rawurlencode($_GET['id']) . '&limit=1');
    $banner = (!empty($result) && is_array($result)) ? $result[0] : null;
    if (!$banner) redirect('/admin/banners');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'image_url' => trim($_POST['image_url'] ?? ''),
        'link_url' => trim($_POST['link_url'] ?? ''),
        'is_active' => isset($_POST['is_active']),
        'sort_order' => (int)($_POST['sort_order'] ?? 0),
    ];
    if ($isEdit) {
        supabase_request('banners?id=eq.' . rawurlencode($_GET['id']), 'PATCH', $data);
    } else {
        supabase_request('banners', 'POST', $data);
    }
    flash('success', $isEdit ? 'Banner updated.' : 'Banner added.');
    redirect('/admin/banners');
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?= $isEdit ? 'Edit' : 'Add' ?> Banner - Admin</title><link rel="stylesheet" href="/assets/css/style.css"></head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <div class="admin-main">
        <h1><?= $isEdit ? 'Edit' : 'Add' ?> Banner</h1>
        <form method="POST" class="admin-form">
            <div class="form-group"><label>Title</label><input type="text" name="title" class="form-input" value="<?= e($banner['title'] ?? '') ?>"></div>
            <div class="form-group"><label>Image URL *</label><input type="url" name="image_url" required class="form-input" value="<?= e($banner['image_url'] ?? '') ?>"></div>
            <div class="form-group"><label>Link URL</label><input type="url" name="link_url" class="form-input" value="<?= e($banner['link_url'] ?? '') ?>"></div>
            <div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" class="form-input" value="<?= e($banner['sort_order'] ?? 0) ?>"></div>
            <div class="form-group"><label class="checkbox-label"><input type="checkbox" name="is_active" <?= ($banner['is_active'] ?? true) ? 'checked' : '' ?>> Active</label></div>
            <div class="form-actions"><button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Add' ?></button><a href="/admin/banners" class="btn btn-outline">Cancel</a></div>
        </form>
    </div>
</div>
</body>
</html>
