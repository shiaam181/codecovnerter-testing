<?php
/**
 * Admin Helper Functions
 * Utilities for admin panel operations.
 */

/**
 * Get admin dashboard stats
 */
function get_admin_stats($tenantId = null) {
    $stats = [
        'total_products' => 0,
        'total_orders' => 0,
        'total_revenue' => 0,
        'pending_orders' => 0,
    ];
    
    // Count products
    $endpoint = 'products?select=id&is_active=eq.true';
    if ($tenantId) $endpoint .= '&tenant_id=eq.' . $tenantId;
    $products = supabase_request($endpoint);
    $stats['total_products'] = is_array($products) ? count($products) : 0;
    
    // Count orders
    $endpoint = 'orders?select=id,total_amount,payment_status';
    if ($tenantId) $endpoint .= '&tenant_id=eq.' . $tenantId;
    $orders = supabase_request($endpoint);
    if (is_array($orders)) {
        $stats['total_orders'] = count($orders);
        foreach ($orders as $order) {
            if (($order['payment_status'] ?? '') === 'paid') {
                $stats['total_revenue'] += (float)($order['total_amount'] ?? 0);
            }
            if (($order['payment_status'] ?? '') === 'pending') {
                $stats['pending_orders']++;
            }
        }
    }
    
    return $stats;
}

/**
 * Handle admin login
 */
function admin_login($email, $password) {
    $result = supabase_auth_signin($email, $password);
    
    if ($result && isset($result['access_token'])) {
        $_SESSION['admin_token'] = $result['access_token'];
        $_SESSION['admin_user_id'] = $result['user']['id'] ?? '';
        $_SESSION['admin_username'] = $result['user']['email'] ?? $email;
        return true;
    }
    
    return false;
}

/**
 * Handle file/image upload via Supabase Storage (placeholder)
 */
function upload_image($file, $bucket = 'products') {
    // For now, return empty - implement Supabase storage upload if needed
    return '';
}

/**
 * Paginate results
 */
function paginate($items, $page = 1, $perPage = 20) {
    $total = count($items);
    $totalPages = ceil($total / $perPage);
    $offset = ($page - 1) * $perPage;
    
    return [
        'items' => array_slice($items, $offset, $perPage),
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage,
        'total_pages' => $totalPages,
    ];
}
