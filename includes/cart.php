<?php
/**
 * Cart Management (Session-based)
 * Handles add, remove, update, and clear operations on the shopping cart.
 */

/**
 * Initialize cart in session if not exists
 */
function cart_init() {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

/**
 * Add item to cart
 */
function cart_add($item, $qty = 1) {
    cart_init();
    $productId = $item['product_id'];
    
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['qty'] += $qty;
        // Enforce max quantity
        $max = $_SESSION['cart'][$productId]['max_quantity'] ?? null;
        if ($max && $_SESSION['cart'][$productId]['qty'] > $max) {
            $_SESSION['cart'][$productId]['qty'] = $max;
        }
    } else {
        $_SESSION['cart'][$productId] = array_merge($item, ['qty' => $qty]);
    }
}

/**
 * Set quantity for a cart item
 */
function cart_set_qty($productId, $qty) {
    cart_init();
    if (isset($_SESSION['cart'][$productId])) {
        if ($qty <= 0) {
            cart_remove($productId);
        } else {
            $max = $_SESSION['cart'][$productId]['max_quantity'] ?? null;
            if ($max && $qty > $max) $qty = $max;
            $_SESSION['cart'][$productId]['qty'] = $qty;
        }
    }
}

/**
 * Remove item from cart
 */
function cart_remove($productId) {
    cart_init();
    unset($_SESSION['cart'][$productId]);
}

/**
 * Clear entire cart
 */
function cart_clear() {
    $_SESSION['cart'] = [];
}

/**
 * Get all cart items
 */
function cart_items() {
    cart_init();
    return $_SESSION['cart'];
}

/**
 * Get cart item count
 */
function cart_count() {
    cart_init();
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['qty'];
    }
    return $count;
}

/**
 * Get cart total price
 */
function cart_total() {
    cart_init();
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += ($item['unit_price'] ?? 0) * ($item['qty'] ?? 1);
    }
    return $total;
}

/**
 * Get cart MRP total (for discount display)
 */
function cart_mrp_total() {
    cart_init();
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $mrp = $item['mrp'] ?? $item['unit_price'] ?? 0;
        $total += $mrp * ($item['qty'] ?? 1);
    }
    return $total;
}

/**
 * Get cart discount amount
 */
function cart_discount() {
    return cart_mrp_total() - cart_total();
}

/**
 * Check if cart is empty
 */
function cart_is_empty() {
    cart_init();
    return empty($_SESSION['cart']);
}
