<?php
/**
 * Data Access Layer
 * Functions to query Supabase for products, orders, categories, etc.
 */

// ============================================================
// PRODUCTS
// ============================================================

function get_products($options = []) {
    $limit = $options['limit'] ?? PRODUCTS_PER_PAGE;
    $offset = $options['offset'] ?? 0;
    $category = $options['category'] ?? null;
    $search = $options['search'] ?? null;
    $tenantId = $options['tenant_id'] ?? null;
    $sort = $options['sort'] ?? 'created_at.desc';

    $endpoint = "products?select=*&is_active=eq.true&order=$sort&limit=$limit&offset=$offset";
    
    if ($category) {
        $endpoint .= '&category_slug=eq.' . rawurlencode($category);
    }
    if ($search) {
        $endpoint .= '&title=ilike.*' . rawurlencode($search) . '*';
    }
    if ($tenantId) {
        $endpoint .= '&tenant_id=eq.' . $tenantId;
    }

    return supabase_request($endpoint) ?: [];
}

function get_product_by_slug($slug, $tenantId = null) {
    $endpoint = 'products?slug=eq.' . rawurlencode($slug) . '&limit=1';
    if ($tenantId) {
        $endpoint .= '&tenant_id=eq.' . $tenantId;
    }
    $result = supabase_request($endpoint);
    return (!empty($result) && is_array($result)) ? $result[0] : null;
}

function get_product_by_id($id) {
    $result = supabase_request('products?id=eq.' . rawurlencode($id) . '&limit=1');
    return (!empty($result) && is_array($result)) ? $result[0] : null;
}

// ============================================================
// CATEGORIES
// ============================================================

function get_categories($tenantId = null) {
    $endpoint = 'categories?select=*&order=sort_order.asc';
    if ($tenantId) {
        $endpoint .= '&tenant_id=eq.' . $tenantId;
    }
    return supabase_request($endpoint) ?: [];
}

function get_category_by_slug($slug) {
    $result = supabase_request('categories?slug=eq.' . rawurlencode($slug) . '&limit=1');
    return (!empty($result) && is_array($result)) ? $result[0] : null;
}

// ============================================================
// ORDERS
// ============================================================

function create_order($orderData) {
    return supabase_request('orders', 'POST', $orderData);
}

function get_order_by_id($orderId) {
    $result = supabase_request('orders?id=eq.' . rawurlencode($orderId) . '&limit=1');
    return (!empty($result) && is_array($result)) ? $result[0] : null;
}

function get_orders($tenantId = null, $limit = 50) {
    $endpoint = "orders?select=*&order=created_at.desc&limit=$limit";
    if ($tenantId) {
        $endpoint .= '&tenant_id=eq.' . $tenantId;
    }
    return supabase_request($endpoint) ?: [];
}

// ============================================================
// BANNERS
// ============================================================

function get_banners($tenantId = null) {
    $endpoint = 'banners?select=*&is_active=eq.true&order=sort_order.asc';
    if ($tenantId) {
        $endpoint .= '&tenant_id=eq.' . $tenantId;
    }
    return supabase_request($endpoint) ?: [];
}

// ============================================================
// HOMEPAGE LAYOUT
// ============================================================

function get_homepage_sections($tenantId = null) {
    $endpoint = 'homepage_sections?select=*&is_active=eq.true&order=sort_order.asc';
    if ($tenantId) {
        $endpoint .= '&tenant_id=eq.' . $tenantId;
    }
    return supabase_request($endpoint) ?: [];
}

// ============================================================
// TENANTS
// ============================================================

function get_all_tenants() {
    return supabase_request('tenants?select=*&order=created_at.desc') ?: [];
}

// ============================================================
// UPI ACCOUNTS
// ============================================================

function get_upi_accounts($tenantId = null) {
    $endpoint = 'upi_accounts?select=*&order=priority.asc';
    if ($tenantId) {
        $endpoint .= '&tenant_id=eq.' . $tenantId;
    } else {
        $endpoint .= '&tenant_id=is.null';
    }
    return supabase_request($endpoint) ?: [];
}

function get_upi_account_by_id($id) {
    $result = supabase_request('upi_accounts?id=eq.' . rawurlencode($id) . '&limit=1');
    return (!empty($result) && is_array($result)) ? $result[0] : null;
}

function create_upi_account($data) {
    return supabase_request('upi_accounts', 'POST', $data);
}

function update_upi_account($id, $data) {
    return supabase_request('upi_accounts?id=eq.' . rawurlencode($id), 'PATCH', $data);
}

function delete_upi_account($id) {
    return supabase_request('upi_accounts?id=eq.' . rawurlencode($id), 'DELETE');
}

// ============================================================
// PAYMENT OFFERS
// ============================================================

function get_payment_offers($tenantId = null) {
    $endpoint = 'payment_offers?select=*&is_active=eq.true&order=sort_order.asc';
    if ($tenantId) {
        $endpoint .= '&tenant_id=eq.' . $tenantId;
    }
    return supabase_request($endpoint) ?: [];
}
