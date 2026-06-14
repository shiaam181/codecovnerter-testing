<?php
/**
 * Category Page
 * Expects: $slug variable from router
 */
$tenant = get_current_tenant();
$tenantId = $tenant['id'] ?? null;
$category = get_category_by_slug($slug);
$products = get_products(['category' => $slug, 'tenant_id' => $tenantId, 'limit' => 40]);

include __DIR__ . '/../templates/header.php';
?>

<div class="page-category">
    <h1 class="page-title"><?= e($category['name'] ?? ucfirst($slug)) ?></h1>
    <?php include __DIR__ . '/../templates/components/product-grid.php'; ?>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
