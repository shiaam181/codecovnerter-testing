<?php
/**
 * Tenant Admin UPI Accounts
 */
$tenant = get_current_tenant();
if (!is_admin()) redirect('/t/' . $tenant['slug'] . '/admin/login');

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['admin_action'] ?? '';
    
    if ($action === 'add_upi') {
        $data = [
            'vpa' => trim($_POST['vpa'] ?? ''),
            'upi_id' => trim($_POST['vpa'] ?? ''),
            'display_name' => trim($_POST['display_name'] ?? ''),
            'name' => trim($_POST['display_name'] ?? ''),
            'priority' => (int)($_POST['priority'] ?? 0),
            'is_active' => true,
            'tenant_id' => $tenant['id'],
        ];
        if (!empty($data['vpa']) && strpos($data['vpa'], '@') !== false) {
            create_upi_account($data);
            flash('success', 'UPI account added.');
        }
    } elseif ($action === 'delete_upi') {
        $deleteId = $_POST['upi_id'] ?? '';
        if ($deleteId) delete_upi_account($deleteId);
        flash('success', 'Deleted.');
    }
    redirect('/t/' . $tenant['slug'] . '/admin/upi');
}

$upiAccounts = get_upi_accounts($tenant['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UPI Accounts - <?= e($tenant['store_name'] ?? '') ?> Admin</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="sidebar-header"><a href="/t/<?= e($tenant['slug']) ?>/admin" class="sidebar-logo"><?= e($tenant['store_name'] ?? $tenant['slug']) ?></a></div>
        <nav class="sidebar-nav">
            <a href="/t/<?= e($tenant['slug']) ?>/admin" class="sidebar-link">Dashboard</a>
            <a href="/t/<?= e($tenant['slug']) ?>/admin/products" class="sidebar-link">Products</a>
            <a href="/t/<?= e($tenant['slug']) ?>/admin/upi" class="sidebar-link active">UPI Accounts</a>
            <a href="/t/<?= e($tenant['slug']) ?>" class="sidebar-link">View Store</a>
        </nav>
    </aside>
    <div class="admin-main">
        <h1>UPI Accounts</h1>
        
        <?php $flash = get_flash('success'); if ($flash): ?>
            <div class="flash flash-success"><?= e($flash) ?></div>
        <?php endif; ?>
        
        <!-- Add UPI Form -->
        <div class="admin-section">
            <h2>Add UPI Account</h2>
            <form method="POST" class="admin-form form-inline">
                <input type="hidden" name="admin_action" value="add_upi">
                <div class="form-group">
                    <input type="text" name="vpa" required class="form-input" placeholder="UPI ID (e.g. name@ybl)">
                </div>
                <div class="form-group">
                    <input type="text" name="display_name" class="form-input" placeholder="Display Name">
                </div>
                <div class="form-group">
                    <input type="number" name="priority" class="form-input" value="0" style="width:80px;" placeholder="Priority">
                </div>
                <button type="submit" class="btn btn-primary">Add</button>
            </form>
        </div>
        
        <!-- Existing Accounts -->
        <?php if (!empty($upiAccounts)): ?>
            <table class="admin-table">
                <thead><tr><th>Name</th><th>VPA</th><th>Priority</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($upiAccounts as $acc): ?>
                        <tr>
                            <td><?= e($acc['display_name'] ?? $acc['name'] ?? '-') ?></td>
                            <td><code><?= e($acc['vpa'] ?? $acc['upi_id'] ?? '') ?></code></td>
                            <td><?= e($acc['priority'] ?? 0) ?></td>
                            <td>
                                <form method="POST" style="display:inline" onsubmit="return confirm('Delete?')">
                                    <input type="hidden" name="admin_action" value="delete_upi">
                                    <input type="hidden" name="upi_id" value="<?= e($acc['id'] ?? '') ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state"><p>No UPI accounts. Add one above.</p></div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
