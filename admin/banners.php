<?php
/**
 * Admin Banners List
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['admin_action'] ?? '') === 'delete_banner') {
    $deleteId = $_POST['banner_id'] ?? '';
    if ($deleteId) {
        supabase_request('banners?id=eq.' . rawurlencode($deleteId), 'DELETE');
        flash('success', 'Banner deleted.');
    }
    redirect('/admin/banners');
}

$banners = supabase_request('banners?select=*&order=sort_order.asc') ?: [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banners - Admin</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <div class="admin-main">
        <div class="admin-header-row">
            <h1>Banners</h1>
            <a href="/admin/banners/new" class="btn btn-primary">Add Banner</a>
        </div>
        <?php $flash = get_flash('success'); if ($flash): ?>
            <div class="flash flash-success"><?= e($flash) ?></div>
        <?php endif; ?>
        <?php if (empty($banners)): ?>
            <div class="empty-state"><p>No banners.</p></div>
        <?php else: ?>
            <table class="admin-table">
                <thead><tr><th>Image</th><th>Title</th><th>Active</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($banners as $banner): ?>
                        <tr>
                            <td><img src="<?= e($banner['image_url'] ?? '') ?>" style="height:40px;border-radius:4px;"></td>
                            <td><?= e($banner['title'] ?? '-') ?></td>
                            <td><span class="badge <?= ($banner['is_active'] ?? false) ? 'badge-success' : 'badge-muted' ?>"><?= ($banner['is_active'] ?? false) ? 'Yes' : 'No' ?></span></td>
                            <td class="actions-cell">
                                <a href="/admin/banners/edit?id=<?= e($banner['id'] ?? '') ?>" class="btn btn-sm">Edit</a>
                                <form method="POST" style="display:inline" onsubmit="return confirm('Delete?')">
                                    <input type="hidden" name="admin_action" value="delete_banner">
                                    <input type="hidden" name="banner_id" value="<?= e($banner['id'] ?? '') ?>">
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
