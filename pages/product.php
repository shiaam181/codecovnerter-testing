<?php
/**
 * Product Detail Page
 * Expects: $slug variable from router
 */
$tenant = get_current_tenant();
$tenantId = $tenant['id'] ?? null;
$product = get_product_by_slug($slug, $tenantId);

if (!$product) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    return;
}

$title = $product['title'] ?? 'Product';
$price = $product['price'] ?? $product['unit_price'] ?? 0;
$mrp = $product['mrp'] ?? null;
$imageUrl = $product['image_url'] ?? '';
$description = $product['description'] ?? '';
$brand = $product['brand'] ?? '';
$rating = $product['rating'] ?? null;
$ratingCount = $product['rating_count'] ?? null;
$maxQty = $product['max_quantity'] ?? 10;

include __DIR__ . '/../templates/header.php';
?>

<div class="product-detail">
    <div class="product-detail-image">
        <?php if ($imageUrl): ?>
            <img src="<?= e($imageUrl) ?>" alt="<?= e($title) ?>">
        <?php else: ?>
            <div class="no-image large">No Image</div>
        <?php endif; ?>
    </div>
    
    <div class="product-detail-info">
        <?php if ($brand): ?>
            <span class="product-brand"><?= e($brand) ?></span>
        <?php endif; ?>
        
        <h1 class="product-detail-title"><?= e($title) ?></h1>
        
        <?php if ($rating): ?>
            <div class="product-rating">
                <span class="rating-badge"><?= number_format($rating, 1) ?> ★</span>
                <?php if ($ratingCount): ?>
                    <span class="rating-count">(<?= number_format($ratingCount) ?> ratings)</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="product-detail-pricing">
            <span class="price-large"><?= format_price($price) ?></span>
            <?php if ($mrp && $mrp > $price): ?>
                <span class="product-mrp"><?= format_price($mrp) ?></span>
                <span class="product-discount"><?= format_discount($mrp, $price) ?></span>
            <?php endif; ?>
        </div>
        
        <?php if ($description): ?>
            <div class="product-description">
                <h3>Description</h3>
                <p><?= nl2br(e($description)) ?></p>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="<?= tenant_url('/product/' . e($product['slug'])) ?>">
            <input type="hidden" name="action" value="add_to_cart">
            <input type="hidden" name="product_id" value="<?= e($product['id']) ?>">
            <input type="hidden" name="tenant_id" value="<?= e($tenantId ?? '') ?>">
            <input type="hidden" name="tenant_slug" value="<?= e($tenant['slug'] ?? '') ?>">
            <input type="hidden" name="slug" value="<?= e($product['slug']) ?>">
            <input type="hidden" name="title" value="<?= e($title) ?>">
            <input type="hidden" name="image_url" value="<?= e($imageUrl) ?>">
            <input type="hidden" name="unit_price" value="<?= e($price) ?>">
            <input type="hidden" name="mrp" value="<?= e($mrp ?? '') ?>">
            <input type="hidden" name="max_quantity" value="<?= e($maxQty) ?>">
            <input type="hidden" name="rating" value="<?= e($rating ?? '') ?>">
            <input type="hidden" name="brand" value="<?= e($brand) ?>">
            <input type="hidden" name="redirect" value="<?= e(tenant_url('/product/' . $product['slug'])) ?>">
            <button type="submit" class="btn btn-primary btn-lg">Add to Cart</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
