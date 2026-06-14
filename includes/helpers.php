<?php
/**
 * Helper Functions
 * Utility functions for formatting, UPI deep-link generation, routing, etc.
 */

// ============================================================
// ROUTING & REQUEST HELPERS
// ============================================================

/**
 * Get current request path (cleaned)
 */
function current_path() {
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($uri, PHP_URL_PATH);
    return $path ?: '/';
}

/**
 * Redirect to a URL
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Flash message (session-based)
 */
function flash($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

/**
 * Get and clear flash message
 */
function get_flash($type) {
    $msg = $_SESSION['flash'][$type] ?? null;
    unset($_SESSION['flash'][$type]);
    return $msg;
}

/**
 * Escape HTML
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// ============================================================
// FORMATTING HELPERS
// ============================================================

/**
 * Format price in INR
 */
function format_price($amount) {
    if ($amount === null) return '';
    return CURRENCY_SYMBOL . number_format((float)$amount, 2);
}

/**
 * Format discount percentage
 */
function format_discount($mrp, $price) {
    if (!$mrp || $mrp <= $price) return '';
    $discount = round((($mrp - $price) / $mrp) * 100);
    return $discount . '% off';
}

/**
 * Truncate text
 */
function truncate($text, $length = 50) {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

// ============================================================
// UPI DEEP-LINK GENERATION (CORE PAYMENT LOGIC)
// ============================================================

/**
 * Generate a unique transaction reference ID.
 * This MUST be unique per transaction attempt to avoid risk policy flags.
 */
function generate_transaction_ref() {
    return 'TXN' . strtoupper(base_convert(time(), 10, 36)) . strtoupper(bin2hex(random_bytes(4)));
}

/**
 * Generate a unique transaction ID
 */
function generate_transaction_id() {
    return time() . mt_rand(1000, 9999);
}

/**
 * Build a proper UPI intent URL with all required parameters.
 * 
 * This function generates a compliant UPI deep-link URL that includes:
 * - pa: Payee VPA (required)
 * - pn: Payee name (required)
 * - am: Amount (optional but recommended)
 * - cu: Currency code (INR)
 * - tr: Unique transaction reference (CRITICAL - must be unique per attempt)
 * - tn: Transaction note (helps legitimize the transaction)
 * - mode: Payment mode (00=P2P default)
 * - orgid: NPCI org ID (000000)
 * 
 * @param array $params Payment parameters
 * @return string|null UPI intent URL or null on failure
 */
function build_upi_url($params) {
    $pa = $params['pa'] ?? '';       // Payee VPA (required)
    $pn = $params['pn'] ?? '';       // Payee name
    $am = $params['am'] ?? '';       // Amount
    $cu = $params['cu'] ?? 'INR';    // Currency
    $tn = $params['tn'] ?? '';       // Transaction note
    $tr = $params['tr'] ?? '';       // Transaction reference
    $tid = $params['tid'] ?? '';     // Transaction ID
    $mc = $params['mc'] ?? '';       // Merchant code
    $mode = $params['mode'] ?? '00'; // Mode: 00=P2P, 04=merchant QR

    if (empty($pa)) return null;

    // Generate unique tr if not provided - CRITICAL for avoiding risk flags
    if (empty($tr)) {
        $tr = generate_transaction_ref();
    }

    if (empty($tid)) {
        $tid = generate_transaction_id();
    }

    // Build name from VPA if not provided
    if (empty($pn)) {
        $pn = explode('@', $pa)[0];
    }

    // Build query parameters
    $queryParts = [];
    $queryParts[] = 'pa=' . rawurlencode($pa);
    $queryParts[] = 'pn=' . rawurlencode($pn);
    $queryParts[] = 'cu=' . rawurlencode($cu);

    // Transaction reference - CRITICAL for avoiding risk policy flags
    $queryParts[] = 'tr=' . rawurlencode($tr);

    // Transaction note
    if (empty($tn)) {
        $tn = 'Payment to ' . $pn;
    }
    $queryParts[] = 'tn=' . rawurlencode($tn);

    // Amount (properly formatted to 2 decimal places)
    if (!empty($am) && is_numeric($am) && (float)$am > 0) {
        $queryParts[] = 'am=' . number_format((float)$am, 2, '.', '');
    }

    // Merchant code (if available)
    if (!empty($mc)) {
        $queryParts[] = 'mc=' . rawurlencode($mc);
    }

    // Payment mode
    $queryParts[] = 'mode=' . rawurlencode($mode);

    // NPCI org ID
    $queryParts[] = 'orgid=000000';

    return 'upi://pay?' . implode('&', $queryParts);
}

/**
 * Build app-specific UPI intent URLs
 * 
 * @param string $baseUrl The standard upi:// URL
 * @param string $app App identifier (gpay, phonepe, paytm, bhim)
 * @return string App-specific intent URL
 */
function build_app_intent_url($baseUrl, $app) {
    switch ($app) {
        case 'gpay':
            // Google Pay uses tez:// scheme on some devices, but upi:// works universally
            return $baseUrl;
        case 'phonepe':
            return str_replace('upi://pay?', 'phonepe://pay?', $baseUrl);
        case 'paytm':
            return str_replace('upi://pay?', 'paytmmp://pay?', $baseUrl);
        case 'bhim':
            // BHIM uses standard upi:// scheme
            return $baseUrl;
        default:
            return $baseUrl;
    }
}

/**
 * Get all UPI intent URLs for a payment
 * Returns array with generic + app-specific URLs
 * 
 * @param array $params Payment parameters (pa, pn, am, tn, etc.)
 * @return array Array of intent URLs keyed by app name
 */
function get_upi_intent_urls($params) {
    $baseUrl = build_upi_url($params);
    
    if (!$baseUrl) return [];
    
    return [
        'generic' => $baseUrl,
        'gpay' => build_app_intent_url($baseUrl, 'gpay'),
        'phonepe' => build_app_intent_url($baseUrl, 'phonepe'),
        'paytm' => build_app_intent_url($baseUrl, 'paytm'),
        'bhim' => build_app_intent_url($baseUrl, 'bhim'),
    ];
}

/**
 * Get UPI accounts from database for a tenant or global
 */
function get_active_upi_accounts($tenantId = null) {
    $endpoint = 'upi_accounts?is_active=eq.true&order=priority.asc';
    if ($tenantId) {
        $endpoint .= '&tenant_id=eq.' . $tenantId;
    } else {
        $endpoint .= '&tenant_id=is.null';
    }
    
    $result = supabase_request($endpoint);
    return $result ?: [];
}

/**
 * Get primary UPI account (highest priority active account)
 */
function get_primary_upi_account($tenantId = null) {
    $accounts = get_active_upi_accounts($tenantId);
    return !empty($accounts) ? $accounts[0] : null;
}

// ============================================================
// TENANT HELPERS
// ============================================================

/**
 * Get tenant by slug
 */
function get_tenant_by_slug($slug) {
    $result = supabase_request('tenants?slug=eq.' . rawurlencode($slug) . '&limit=1');
    return (!empty($result) && is_array($result)) ? $result[0] : null;
}

/**
 * Get current tenant from session
 */
function get_current_tenant() {
    return $_SESSION['current_tenant'] ?? null;
}

/**
 * Get tenant base URL path
 */
function tenant_url($path = '/') {
    $tenant = get_current_tenant();
    if ($tenant) {
        return '/t/' . $tenant['slug'] . $path;
    }
    return $path;
}

// ============================================================
// ADMIN HELPERS
// ============================================================

/**
 * Check if admin is authenticated
 */
function is_admin() {
    return !empty($_SESSION['admin_token']);
}

/**
 * Require admin authentication
 */
function require_admin() {
    if (!is_admin()) {
        redirect('/admin/login');
    }
}

// ============================================================
// MISC HELPERS
// ============================================================

/**
 * Generate a URL-friendly slug
 */
function slugify($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

/**
 * Check if request is from mobile device
 */
function is_mobile() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return preg_match('/Mobile|Android|iPhone|iPad/i', $userAgent);
}

/**
 * Get base URL of the application
 */
function base_url() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $protocol . '://' . $host;
}
