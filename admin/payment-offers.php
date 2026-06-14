<?php
/**
 * Admin Payment Offers
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['admin_action'] ?? '') === 'delete_offer') {
    $deleteId = $_POST['offer_id'] ?? '';
    if ($deleteId) supabase_request('payment_offers?id=eq.' . rawurlencode($deleteId), 'DELETE');
    redirect('/admin/payment-offers');
}
$offers = supabase_request('payment_offers?select=*&order=sort_order.asc') ?: [];
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Payment Offers - Admin</title><link rel="stylesheet" href="/assets/css/style.css"></head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <div class="admin-main">
        <div class="admin-header-row"><h1>Payment Offers</h1><a href="/admin/payment-offers/new" class="btn btn-primary">Add Offer</a></div>
        <?php if (empty($offers)): ?>
            <div class="empty-state"><p>No offers.</p></div>
        <?php else: ?>
            <table class="admin-table">
                <thead><tr><th>Badge</th><th>Description</th><th>Active</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($offers as $offer): ?>
                        <tr>
                            <td><span class="badge badge-info"><?= e($offer['badge'] ?? '') ?></span></td>
                            <td><?= e($offer['description'] ?? '') ?></td>
                            <td><span class="badge <?= ($offer['is_active'] ?? false) ? 'badge-success' : 'badge-muted' ?>"><?= ($offer['is_active'] ?? false) ? 'Yes' : 'No' ?></span></td>
                            <td><form method="POST" style="display:inline" onsubmit="return confirm('Delete?')"><input type="hidden" name="admin_action" value="delete_offer"><input type="hidden" name="offer_id" value="<?= e($offer['id'] ?? '') ?>"><button type="submit" class="btn btn-sm btn-danger">Delete</button></form></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
