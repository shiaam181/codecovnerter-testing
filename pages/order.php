<?php
/**
 * Order Confirmation / Status Page
 * Shows order details and UPI payment buttons if not yet paid.
 * Expects: $orderId from router
 */
$order = get_order_by_id($orderId);

if (!$order) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    return;
}

$tenant = get_current_tenant();
$tenantId = $tenant['id'] ?? null;
$isPaid = ($order['payment_status'] ?? '') === 'paid' || ($order['payment_status'] ?? '') === 'submitted';
$orderItems = json_decode($order['items'] ?? '[]', true);
$totalAmount = $order['total_amount'] ?? 0;

// Get UPI account for payment
$upiAccount = get_primary_upi_account($tenantId);
$upiId = $upiAccount['vpa'] ?? $upiAccount['upi_id'] ?? '';
$upiName = $upiAccount['display_name'] ?? $upiAccount['name'] ?? ($tenant['store_name'] ?? APP_NAME);

include __DIR__ . '/../templates/header.php';
?>

<div class="page-order">
    <div class="order-status-header">
        <?php if ($isPaid): ?>
            <div class="status-badge status-paid">Payment Confirmed</div>
        <?php else: ?>
            <div class="status-badge status-pending">Payment Pending</div>
        <?php endif; ?>
        <h1 class="page-title">Order Details</h1>
        <p class="order-id">Order ID: <?= e(substr($orderId, 0, 8)) ?>...</p>
    </div>
    
    <!-- Order Items -->
    <div class="order-section">
        <h2 class="section-subtitle">Items</h2>
        <?php foreach ($orderItems as $item): ?>
            <div class="order-item">
                <span><?= e($item['title'] ?? 'Item') ?> × <?= $item['quantity'] ?? 1 ?></span>
                <span><?= format_price(($item['unit_price'] ?? 0) * ($item['quantity'] ?? 1)) ?></span>
            </div>
        <?php endforeach; ?>
        <div class="order-total">
            <strong>Total: <?= format_price($totalAmount) ?></strong>
        </div>
    </div>
    
    <!-- Delivery Address -->
    <div class="order-section">
        <h2 class="section-subtitle">Delivery Address</h2>
        <p><?= e($order['customer_name'] ?? '') ?></p>
        <p><?= e($order['address'] ?? '') ?></p>
        <p><?= e($order['city'] ?? '') ?> - <?= e($order['pincode'] ?? '') ?></p>
        <p>Phone: <?= e($order['customer_phone'] ?? '') ?></p>
    </div>
    
    <!-- UPI Payment (if not paid) -->
    <?php if (!$isPaid && !empty($upiId)): ?>
        <div class="order-section payment-section">
            <h2 class="section-subtitle">Complete Payment</h2>
            <p class="payment-info">Amount: <strong class="amount-highlight"><?= format_price($totalAmount) ?></strong></p>
            <p class="payment-note">Tap below to pay. Each tap generates a fresh unique reference for secure payment.</p>
            
            <div class="upi-buttons" id="upi-buttons">
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
                var amount = <?= json_encode(number_format($totalAmount, 2, '.', '')) ?>;
                var orderId = <?= json_encode(substr($orderId, 0, 8)) ?>;
                var tn = 'Order ' + orderId + ' payment to ' + upiName;
                
                function generateTr() {
                    var ts = Date.now().toString(36);
                    var rand = Math.random().toString(36).substring(2, 10);
                    return 'TXN' + ts.toUpperCase() + rand.toUpperCase();
                }
                
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
                
                function setupButton(id, scheme) {
                    var btn = document.getElementById(id);
                    if (!btn) return;
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        var url = buildUpiUrl();
                        if (scheme && scheme !== 'upi') {
                            url = url.replace('upi://pay?', scheme + '://pay?');
                        }
                        window.location.href = url;
                    });
                }
                
                setupButton('btn-upi-generic', 'upi');
                setupButton('btn-upi-gpay', 'upi');
                setupButton('btn-upi-phonepe', 'phonepe');
                setupButton('btn-upi-paytm', 'paytmmp');
            })();
            </script>
            
            <!-- Mark as paid button -->
            <form method="POST" class="mark-paid-form">
                <input type="hidden" name="action" value="mark_paid">
                <input type="hidden" name="order_id" value="<?= e($orderId) ?>">
                <p class="payment-note">Already paid? Click below to confirm:</p>
                <button type="submit" class="btn btn-success btn-block">I've Completed Payment</button>
            </form>
        </div>
    <?php elseif ($isPaid): ?>
        <div class="order-section">
            <div class="success-message">
                <p>✓ Payment received! Your order is being processed.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
