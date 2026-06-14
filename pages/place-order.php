<?php
/**
 * Place Order Handler (POST only)
 * Creates the order in Supabase and redirects to order confirmation.
 */
if (cart_is_empty()) {
    redirect(tenant_url('/'));
}

$tenant = get_current_tenant();
$tenantId = $tenant['id'] ?? null;
$items = cart_items();
$total = cart_total();

// Collect address info
$customerName = trim($_POST['customer_name'] ?? '');
$customerPhone = trim($_POST['customer_phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$city = trim($_POST['city'] ?? '');
$pincode = trim($_POST['pincode'] ?? '');

// Validate
if (empty($customerName) || empty($customerPhone) || empty($address) || empty($city) || empty($pincode)) {
    flash('error', 'Please fill in all required fields.');
    redirect(tenant_url('/checkout'));
}

// Build order data
$orderItems = [];
foreach ($items as $item) {
    $orderItems[] = [
        'product_id' => $item['product_id'],
        'title' => $item['title'],
        'unit_price' => (float)$item['unit_price'],
        'quantity' => (int)$item['qty'],
        'image_url' => $item['image_url'] ?? null,
    ];
}

$orderData = [
    'customer_name' => $customerName,
    'customer_phone' => $customerPhone,
    'address' => $address,
    'city' => $city,
    'pincode' => $pincode,
    'items' => json_encode($orderItems),
    'total_amount' => $total,
    'payment_status' => 'pending',
    'order_status' => 'placed',
    'tenant_id' => $tenantId,
];

// Create order
$result = create_order($orderData);

if ($result && is_array($result) && !empty($result[0]['id'])) {
    $orderId = $result[0]['id'];
    cart_clear();
    
    $orderUrl = $tenant ? "/t/{$tenant['slug']}/order/{$orderId}" : "/order/{$orderId}";
    redirect($orderUrl);
} else {
    flash('error', 'Failed to place order. Please try again.');
    redirect(tenant_url('/checkout'));
}
