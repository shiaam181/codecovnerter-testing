<?php
/**
 * Checkout Page - Address form + Order Summary + UPI Payment Buttons
 * This is the critical page with UPI intent buttons.
 */
if (cart_is_empty()) {
    redirect(tenant_url('/cart'));
}

$tenant = get_current_tenant();
$tenantId = $tenant['id'] ?? null;
$items = cart_items();
$total = cart_total();

// Get active UPI account for payment
$upiAccount = get_primary_upi_account($tenantId);
$upiId = $upiAccount['vpa'] ?? $upiAccount['upi_id'] ?? '';
$upiName = $upiAccount['display_name'] ?? $upiAccount['name'] ?? ($tenant['store_name'] ?? APP_NAME);

// Get payment offers
$offers = get_payment_offers($tenantId);

include __DIR__ . '/../templates/header.php';
?>

<div class="page-checkout">
    <h1 class="page-title">Checkout</h1>
    
    <form method="POST" id="checkout-form">
        <input type="hidden" name="action" value="place_order">
        
        <!-- Address Section -->
        <div class="checkout-section">
            <h2 class="section-subtitle">Delivery Address</h2>
            <div class="form-group">
                <label for="name">Full Name *</label>
                <input type="text" id="name" name="customer_name" required class="form-input" placeholder="Enter your full name">
            </div>
            <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input type="tel" id="phone" name="customer_phone" required class="form-input" placeholder="10-digit mobile number">
            </div>
            <div class="form-group">
                <label for="address">Address *</label>
                <textarea id="address" name="address" required class="form-input" rows="3" placeholder="House/Flat, Street, Landmark"></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="city">City *</label>
                    <input type="text" id="city" name="city" required class="form-input" placeholder="City">
                </div>
                <div class="form-group">
                    <label for="pincode">Pincode *</label>
                    <input type="text" id="pincode" name="pincode" required class="form-input" placeholder="6-digit pincode" maxlength="6">
                </div>
            </div>
        </div>
        
        <!-- Order Summary -->
        <div class="checkout-section">
            <h2 class="section-subtitle">Order Summary</h2>
            <div class="order-items-mini">
                <?php foreach ($items as $item): ?>
                    <div class="order-item-mini">
                        <span class="item-title"><?= e(truncate($item['title'], 30)) ?> × <?= $item['qty'] ?></span>
                        <span class="item-price"><?= format_price($item['unit_price'] * $item['qty']) ?></span>
                    </div>
                <?php endforeach; ?>
                <div class="order-total-mini">
                    <strong>Total: <?= format_price($total) ?></strong>
                </div>
            </div>
        </div>
        
        <!-- Payment Offers -->
        <?php if (!empty($offers)): ?>
            <div class="checkout-section">
                <h2 class="section-subtitle">Offers</h2>
                <?php foreach ($offers as $offer): ?>
                    <div class="offer-card">
                        <span class="offer-badge"><?= e($offer['badge'] ?? 'OFFER') ?></span>
                        <span class="offer-text"><?= e($offer['description'] ?? '') ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- UPI Payment Section -->
        <div class="checkout-section payment-section">
            <h2 class="section-subtitle">Pay via UPI</h2>
            
            <?php if (empty($upiId)): ?>
                <div class="alert alert-warning">
                    <p>Payment method not configured. Please contact the store owner.</p>
                </div>
            <?php else: ?>
                <p class="payment-info">Amount: <strong class="amount-highlight"><?= format_price($total) ?></strong></p>
                <p class="payment-note">Tap a button below to pay with your UPI app. A unique transaction reference is generated each time for secure payment.</p>
                
                <div class="upi-buttons" id="upi-buttons">
                    <!-- Buttons are generated dynamically with fresh tr each time -->
                    <a id="btn-upi-generic" class="btn-upi btn-upi-generic" href="#">
                        <span class="upi-icon">UPI</span>
                        Pay with Any UPI App
                    </a>
                    <a id="btn-upi-gpay" class="btn-upi btn-upi-gpay" href="#">
                        <span class="upi-icon">G</span>
                        Google Pay
                    </a>
                    <a id="btn-upi-phonepe" class="btn-upi btn-upi-phonepe" href="#">
                        <span class="upi-icon">P</span>
                        PhonePe
                    </a>
                    <a id="btn-upi-paytm" class="btn-upi btn-upi-paytm" href="#">
                        <span class="upi-icon">₹</span>
                        Paytm
                    </a>
                </div>
                
                <script>
                (function() {
                    'use strict';
                    
                    var upiId = <?= json_encode($upiId) ?>;
                    var upiName = <?= json_encode($upiName) ?>;
                    var amount = <?= json_encode(number_format($total, 2, '.', '')) ?>;
                    var tn = <?= json_encode('Order payment to ' . $upiName) ?>;
                    
                    // Generate unique transaction reference - MUST be unique per attempt
                    function generateTr() {
                        var ts = Date.now().toString(36);
                        var rand = Math.random().toString(36).substring(2, 10);
                        return 'TXN' + ts.toUpperCase() + rand.toUpperCase();
                    }
                    
                    // Generate unique transaction ID
                    function generateTid() {
                        return Date.now().toString() + Math.floor(Math.random() * 10000).toString();
                    }
                    
                    // Build UPI URL with all required parameters
                    function buildUpiUrl() {
                        var tr = generateTr();
                        var params = [];
                        params.push('pa=' + encodeURIComponent(upiId));
                        params.push('pn=' + encodeURIComponent(upiName));
                        params.push('cu=INR');
                        params.push('tr=' + encodeURIComponent(tr));
                        params.push('tn=' + encodeURIComponent(tn));
                        if (amount && parseFloat(amount) > 0) {
                            params.push('am=' + parseFloat(amount).toFixed(2));
                        }
                        params.push('mode=00');
                        params.push('orgid=000000');
                        return 'upi://pay?' + params.join('&');
                    }
                    
                    // Set up buttons with fresh URLs on each click
                    function setupButton(id, scheme) {
                        var btn = document.getElementById(id);
                        if (!btn) return;
                        btn.addEventListener('click', function(e) {
                            e.preventDefault();
                            var url = buildUpiUrl();
                            if (scheme && scheme !== 'upi') {
                                url = url.replace('upi://pay?', scheme + '://pay?');
                            }
                            // Generate fresh URL and navigate
                            window.location.href = url;
                        });
                    }
                    
                    setupButton('btn-upi-generic', 'upi');
                    setupButton('btn-upi-gpay', 'upi'); // GPay uses standard upi://
                    setupButton('btn-upi-phonepe', 'phonepe');
                    setupButton('btn-upi-paytm', 'paytmmp');
                })();
                </script>
                
                <!-- Place Order button (marks order as pending) -->
                <div class="place-order-section">
                    <p class="payment-note">After paying via UPI, click below to confirm your order:</p>
                    <button type="submit" class="btn btn-primary btn-lg btn-block">Confirm Order</button>
                </div>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
