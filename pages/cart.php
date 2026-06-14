<?php
/**
 * Shopping Cart Page
 */
$items = cart_items();
$total = cart_total();
$mrpTotal = cart_mrp_total();
$discount = cart_discount();

include __DIR__ . '/../templates/header.php';
?>

<div class="page-cart">
    <h1 class="page-title">Shopping Cart</h1>
    
    <?php if (cart_is_empty()): ?>
        <div class="empty-state">
            <p>Your cart is empty.</p>
            <a href="<?= tenant_url('/') ?>" class="btn btn-primary">Continue Shopping</a>
        </div>
    <?php else: ?>
        <div class="cart-items">
            <?php foreach ($items as $item): ?>
                <div class="cart-item">
                    <div class="cart-item-image">
                        <?php if (!empty($item['image_url'])): ?>
                            <img src="<?= e($item['image_url']) ?>" alt="<?= e($item['title']) ?>">
                        <?php endif; ?>
                    </div>
                    <div class="cart-item-info">
                        <h3 class="cart-item-title"><?= e($item['title']) ?></h3>
                        <div class="cart-item-price"><?= format_price($item['unit_price']) ?></div>
                        <div class="cart-item-qty">
                            <form method="POST" class="qty-form">
                                <input type="hidden" name="action" value="update_qty">
                                <input type="hidden" name="product_id" value="<?= e($item['product_id']) ?>">
                                <input type="hidden" name="redirect" value="<?= tenant_url('/cart') ?>">
                                <button type="submit" name="qty" value="<?= max(0, $item['qty'] - 1) ?>" class="qty-btn">-</button>
                                <span class="qty-value"><?= $item['qty'] ?></span>
                                <button type="submit" name="qty" value="<?= $item['qty'] + 1 ?>" class="qty-btn">+</button>
                            </form>
                        </div>
                    </div>
                    <div class="cart-item-actions">
                        <form method="POST">
                            <input type="hidden" name="action" value="remove_from_cart">
                            <input type="hidden" name="product_id" value="<?= e($item['product_id']) ?>">
                            <input type="hidden" name="redirect" value="<?= tenant_url('/cart') ?>">
                            <button type="submit" class="btn-remove">Remove</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="cart-summary">
            <h3>Price Details</h3>
            <div class="summary-row">
                <span>Price (<?= cart_count() ?> items)</span>
                <span><?= format_price($mrpTotal) ?></span>
            </div>
            <?php if ($discount > 0): ?>
                <div class="summary-row discount">
                    <span>Discount</span>
                    <span class="text-green">-<?= format_price($discount) ?></span>
                </div>
            <?php endif; ?>
            <div class="summary-row total">
                <span>Total Amount</span>
                <span><?= format_price($total) ?></span>
            </div>
            <a href="<?= tenant_url('/checkout') ?>" class="btn btn-primary btn-lg btn-block">Proceed to Checkout</a>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
