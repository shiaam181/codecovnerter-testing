<?php
/**
 * Admin UPI Account Add/Edit Form
 */
$isEdit = isset($_GET['id']);
$account = null;
$error = '';

if ($isEdit) {
    $account = get_upi_account_by_id($_GET['id']);
    if (!$account) {
        redirect('/admin/upi');
    }
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'vpa' => trim($_POST['vpa'] ?? ''),
        'display_name' => trim($_POST['display_name'] ?? ''),
        'name' => trim($_POST['display_name'] ?? ''),
        'priority' => (int)($_POST['priority'] ?? 0),
        'is_active' => isset($_POST['is_active']),
    ];
    
    // Also store as upi_id for compatibility
    $data['upi_id'] = $data['vpa'];
    
    if (empty($data['vpa'])) {
        $error = 'VPA (UPI ID) is required.';
    } elseif (strpos($data['vpa'], '@') === false) {
        $error = 'Invalid VPA format. Must contain @ (e.g., name@ybl)';
    } else {
        if ($isEdit) {
            update_upi_account($_GET['id'], $data);
        } else {
            create_upi_account($data);
        }
        flash('success', $isEdit ? 'UPI account updated.' : 'UPI account added.');
        redirect('/admin/upi');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Edit' : 'Add' ?> UPI Account - Admin</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <div class="admin-main">
        <h1><?= $isEdit ? 'Edit' : 'Add' ?> UPI Account</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" class="admin-form">
            <div class="form-group">
                <label for="vpa">VPA (UPI ID) *</label>
                <input type="text" id="vpa" name="vpa" required class="form-input" 
                       placeholder="e.g. yourname@ybl, shop@okaxis"
                       value="<?= e($account['vpa'] ?? $account['upi_id'] ?? '') ?>">
                <p class="form-help">This is the UPI address that receives payments. Must contain @.</p>
            </div>
            
            <div class="form-group">
                <label for="display_name">Display Name</label>
                <input type="text" id="display_name" name="display_name" class="form-input" 
                       placeholder="e.g. My Shop"
                       value="<?= e($account['display_name'] ?? $account['name'] ?? '') ?>">
                <p class="form-help">Name shown to customers during payment. Used in 'pn' parameter.</p>
            </div>
            
            <div class="form-group">
                <label for="priority">Priority</label>
                <input type="number" id="priority" name="priority" class="form-input" 
                       value="<?= e($account['priority'] ?? 0) ?>" min="0">
                <p class="form-help">Lower number = higher priority. The highest priority active account is used for payments.</p>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" <?= ($account['is_active'] ?? true) ? 'checked' : '' ?>>
                    Active (receives payments)
                </label>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Add' ?> UPI Account</button>
                <a href="/admin/upi" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
