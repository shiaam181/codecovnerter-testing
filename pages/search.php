<?php
/**
 * Search Page
 */
$tenant = get_current_tenant();
$tenantId = $tenant['id'] ?? null;
$query = trim($_GET['q'] ?? '');
$products = [];

if (!empty($query)) {
    $products = get_products(['search' => $query, 'tenant_id' => $tenantId, 'limit' => 40]);
}

include __DIR__ . '/../templates/header.php';
?>

<div class="page-search">
    <form method="GET" action="<?= tenant_url('/search') ?>" class="search-form">
        <input type="text" name="q" value="<?= e($query) ?>" placeholder="Search products..." class="search-input" autofocus>
        <button type="submit" class="btn btn-primary">Search</button>
    </form>
    
    <?php if (!empty($query)): ?>
        <h2 class="section-title">Results for "<?= e($query) ?>"</h2>
        <?php include __DIR__ . '/../templates/components/product-grid.php'; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
