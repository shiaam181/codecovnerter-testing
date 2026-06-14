<?php
/**
 * Admin Categories List
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['admin_action'] ?? '') === 'delete_category') {
    $deleteId = $_POST['category_id'] ?? '';
    if ($deleteId) {
        supabase_request('categories?id=eq.' . rawurlencode($deleteId), 'DELETE');
        flash('success', 'Category deleted.');
    }
    redirect('/admin/categories');
}

$categories = get_categories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - Admin</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <div class="admin-main">
        <div class="admin-header-row">
            <h1>Categories</h1>
            <a href="/admin/categories/new" class="btn btn-primary">Add Category</a>
        </div>
        <?php $flash = get_flash('success'); if ($flash): ?>
            <div class="flash flash-success"><?= e($flash) ?></div>
        <?php endif; ?>
        <?php if (empty($categories)): ?>
            <div class="empty-state"><p>No categories.</p></div>
        <?php else: ?>
            <table class="admin-table">
                <thead><tr><th>Name</th><th>Slug</th><th>Sort</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><?= e($cat['name'] ?? '') ?></td>
                            <td><code><?= e($cat['slug'] ?? '') ?></code></td>
                            <td><?= e($cat['sort_order'] ?? 0) ?></td>
                            <td class="actions-cell">
                                <a href="/admin/categories/edit?id=<?= e($cat['id'] ?? '') ?>" class="btn btn-sm">Edit</a>
                                <form method="POST" style="display:inline" onsubmit="return confirm('Delete?')">
                                    <input type="hidden" name="admin_action" value="delete_category">
                                    <input type="hidden" name="category_id" value="<?= e($cat['id'] ?? '') ?>">
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
