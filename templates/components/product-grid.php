<?php
/**
 * Product Grid Component
 * Expects: $products array, $baseUrl string (optional)
 */
$baseUrl = $baseUrl ?? (get_current_tenant() ? '/t/' . get_current_tenant()['slug'] : '');
?>
<?php if (empty($products)): ?>
    <div class="empty-state">
        <p>No products found.</p>
    </div>
<?php else: ?>
    <div class="product-grid">
        <?php foreach ($products as $product): ?>
            <?php include __DIR__ . '/product-card.php'; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
