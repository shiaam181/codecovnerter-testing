<?php
/**
 * Admin Tenants List
 */
$tenants = get_all_tenants();
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Tenants - Admin</title><link rel="stylesheet" href="/assets/css/style.css"></head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <div class="admin-main">
        <div class="admin-header-row"><h1>Tenants</h1><a href="/admin/tenants/new" class="btn btn-primary">Add Tenant</a></div>
        <?php if (empty($tenants)): ?>
            <div class="empty-state"><p>No tenants.</p></div>
        <?php else: ?>
            <table class="admin-table">
                <thead><tr><th>Store Name</th><th>Slug</th><th>Active</th><th>Expires</th></tr></thead>
                <tbody>
                    <?php foreach ($tenants as $t): ?>
                        <tr>
                            <td><?= e($t['store_name'] ?? $t['slug'] ?? '') ?></td>
                            <td><code><?= e($t['slug'] ?? '') ?></code></td>
                            <td><span class="badge <?= ($t['is_active'] ?? false) ? 'badge-success' : 'badge-muted' ?>"><?= ($t['is_active'] ?? false) ? 'Yes' : 'No' ?></span></td>
                            <td><?= e($t['expires_at'] ?? 'Never') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
