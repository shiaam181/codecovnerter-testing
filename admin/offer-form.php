<?php
/**
 * Admin Payment Offer Add/Edit Form
 */
$isEdit = isset($_GET['id']);
$offer = null;
if ($isEdit) {
    $result = supabase_request('payment_offers?id=eq.' . rawurlencode($_GET['id']) . '&limit=1');
    $offer = (!empty($result) && is_array($result)) ? $result[0] : null;
    if (!$offer) redirect('/admin/payment-offers');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'badge' => trim($_POST['badge'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'is_active' => isset($_POST['is_active']),
        'sort_order' => (int)($_POST['sort_order'] ?? 0),
    ];
    if ($isEdit) {
        supabase_request('payment_offers?id=eq.' . rawurlencode($_GET['id']), 'PATCH', $data);
    } else {
        supabase_request('payment_offers', 'POST', $data);
    }
    flash('success', $isEdit ? 'Offer updated.' : 'Offer added.');
    redirect('/admin/payment-offers');
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?= $isEdit ? 'Edit' : 'Add' ?> Offer - Admin</title><link rel="stylesheet" href="/assets/css/style.css"></head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <div class="admin-main">
        <h1><?= $isEdit ? 'Edit' : 'Add' ?> Payment Offer</h1>
        <form method="POST" class="admin-form">
            <div class="form-group"><label>Badge Text</label><input type="text" name="badge" class="form-input" placeholder="e.g. 10% OFF" value="<?= e($offer['badge'] ?? '') ?>"></div>
            <div class="form-group"><label>Description</label><input type="text" name="description" class="form-input" value="<?= e($offer['description'] ?? '') ?>"></div>
            <div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" class="form-input" value="<?= e($offer['sort_order'] ?? 0) ?>"></div>
            <div class="form-group"><label class="checkbox-label"><input type="checkbox" name="is_active" <?= ($offer['is_active'] ?? true) ? 'checked' : '' ?>> Active</label></div>
            <div class="form-actions"><button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Add' ?></button><a href="/admin/payment-offers" class="btn btn-outline">Cancel</a></div>
        </form>
    </div>
</div>
</body>
</html>
