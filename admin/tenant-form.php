<?php
/**
 * Admin Tenant Add Form
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'store_name' => trim($_POST['store_name'] ?? ''),
        'slug' => slugify(trim($_POST['slug'] ?? trim($_POST['store_name'] ?? ''))),
        'is_active' => isset($_POST['is_active']),
    ];
    supabase_request('tenants', 'POST', $data);
    flash('success', 'Tenant added.');
    redirect('/admin/tenants');
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Add Tenant - Admin</title><link rel="stylesheet" href="/assets/css/style.css"></head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <div class="admin-main">
        <h1>Add Tenant</h1>
        <form method="POST" class="admin-form">
            <div class="form-group"><label>Store Name *</label><input type="text" name="store_name" required class="form-input"></div>
            <div class="form-group"><label>Slug</label><input type="text" name="slug" class="form-input" placeholder="auto-generated from name"></div>
            <div class="form-group"><label class="checkbox-label"><input type="checkbox" name="is_active" checked> Active</label></div>
            <div class="form-actions"><button type="submit" class="btn btn-primary">Add Tenant</button><a href="/admin/tenants" class="btn btn-outline">Cancel</a></div>
        </form>
    </div>
</div>
</body>
</html>
