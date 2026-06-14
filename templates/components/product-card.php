<?php
/**
 * Product Card Component
 * Expects: $product array, $baseUrl string
 */
$productUrl = ($baseUrl ?? '') . '/product/' . ($product['slug'] ?? '');
$imageUrl = $product['image_url'] ?? $product['thumbnail'] ?? '';
$title = $product['title'] ?? 'Product';
$price = $product['price'] ?? $product['unit_price'] ?? 0;
$mrp = $product['mrp'] ?? null;
$rating = $product['rating'] ?? null;
$brand = $product['brand'] ?? '';
?>
<div class="product-card">
    <a href="<?= e($productUrl) ?>" class="product-card-link">
        <div class="product-card-image">
            <?php if ($imageUrl): ?>
                <img src="<?= e($imageUrl) ?>" alt="<?= e($title) ?>" loading="lazy">
            <?php else: ?>
                <div class="no-image">No Image</div>
            <?php endif; ?>
        </div>
        <div class="product-card-info">
            <?php if ($brand): ?>
                <span class="product-brand"><?= e($brand) ?></span>
            <?php endif; ?>
            <h3 class="product-title"><?= e(truncate($title, 40)) ?></h3>
            <div class="product-pricing">
                <span class="product-price"><?= format_price($price) ?></span>
                <?php if ($mrp && $mrp > $price): ?>
                    <span class="product-mrp"><?= format_price($mrp) ?></span>
                    <span class="product-discount"><?= format_discount($mrp, $price) ?></span>
                <?php endif; ?>
            </div>
            <?php if ($rating): ?>
                <div class="product-rating">
                    <span class="rating-badge"><?= number_format($rating, 1) ?> ★</span>
                </div>
            <?php endif; ?>
        </div>
    </a>
</div>
