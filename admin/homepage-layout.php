<?php
/**
 * Admin Homepage Layout
 */
$sections = supabase_request('homepage_sections?select=*&order=sort_order.asc') ?: [];
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Homepage Layout - Admin</title><link rel="stylesheet" href="/assets/css/style.css"></head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <div class="admin-main">
        <div class="admin-header-row"><h1>Homepage Layout</h1><a href="/admin/homepage-layout/new" class="btn btn-primary">Add Section</a></div>
        <?php if (empty($sections)): ?>
            <div class="empty-state"><p>No sections configured.</p></div>
        <?php else: ?>
            <table class="admin-table">
                <thead><tr><th>Type</th><th>Title</th><th>Order</th><th>Active</th></tr></thead>
                <tbody>
                    <?php foreach ($sections as $s): ?>
                        <tr>
                            <td><?= e($s['type'] ?? '-') ?></td>
                            <td><?= e($s['title'] ?? '-') ?></td>
                            <td><?= e($s['sort_order'] ?? 0) ?></td>
                            <td><span class="badge <?= ($s['is_active'] ?? false) ? 'badge-success' : 'badge-muted' ?>"><?= ($s['is_active'] ?? false) ? 'Yes' : 'No' ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
