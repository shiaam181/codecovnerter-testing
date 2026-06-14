<?php
/**
 * Homepage
 */
$tenant = get_current_tenant();
$tenantId = $tenant['id'] ?? null;

$banners = get_banners($tenantId);
$categories = get_categories($tenantId);
$products = get_products(['tenant_id' => $tenantId, 'limit' => 20]);

include __DIR__ . '/../templates/header.php';
?>

<?php if (!empty($banners)): ?>
    <?php include __DIR__ . '/../templates/components/banner-carousel.php'; ?>
<?php endif; ?>

<?php if (!empty($categories)): ?>
    <?php include __DIR__ . '/../templates/components/category-strip.php'; ?>
<?php endif; ?>

<section class="section">
    <h2 class="section-title">Products</h2>
    <?php include __DIR__ . '/../templates/components/product-grid.php'; ?>
</section>

<?php include __DIR__ . '/../templates/footer.php'; ?>
