<?php
/**
 * UPI Intent URL Helper
 * 
 * Generates proper UPI deep-link URLs with all required parameters
 * to avoid "Risk Threshold Exceeded" and risk policy failures.
 * 
 * KEY FIX: Each URL includes a unique `tr` (transaction reference) parameter.
 * Without a unique `tr`, UPI apps (especially Google Pay) flag the transaction
 * as risky or show "Risk Threshold Exceeded" errors.
 * 
 * INCLUDE THIS FILE in your checkout/order pages:
 *   require_once __DIR__ . '/../includes/upi-helper.php';
 * 
 * USAGE:
 *   $urls = upi_get_intent_urls([
 *       'pa' => 'paytm.s1zxhz3@pty',
 *       'pn' => 'Farhana Khatun',
 *       'am' => '599.00',
 *       'tn' => 'Order payment ORD12345 Thankyou',
 *   ]);
 *   // $urls['gpay'], $urls['phonepe'], $urls['paytm'], $urls['bhim'], $urls['generic']
 */

/**
 * Generate a unique transaction reference ID.
 * CRITICAL: Must be unique per payment attempt to avoid risk policy flags.
 */
function upi_generate_tr() {
    return 'TXN' . strtoupper(base_convert(time(), 10, 36)) . strtoupper(bin2hex(random_bytes(4)));
}

/**
 * Build a UPI intent URL with all required parameters.
 * 
 * @param array $params Payment parameters:
 *   - pa: (string) Payee VPA/UPI ID (REQUIRED)
 *   - pn: (string) Payee name (REQUIRED)
 *   - am: (string|float) Amount
 *   - cu: (string) Currency code (default: INR)
 *   - tn: (string) Transaction note
 *   - tr: (string) Transaction reference (auto-generated if empty)
 *   - mc: (string) Merchant category code
 *   - mode: (string) Payment mode (default: 00 for P2P)
 *   - scheme: (string) URL scheme (default: upi)
 * @return string|null UPI intent URL
 */
function upi_build_url($params) {
    $pa = $params['pa'] ?? '';
    $pn = $params['pn'] ?? '';
    $am = $params['am'] ?? '';
    $cu = $params['cu'] ?? 'INR';
    $tn = $params['tn'] ?? '';
    $tr = $params['tr'] ?? upi_generate_tr();
    $mc = $params['mc'] ?? '';
    $mode = $params['mode'] ?? '00';
    $scheme = $params['scheme'] ?? 'upi';

    if (empty($pa)) return null;

    // Build name from VPA if not provided
    if (empty($pn)) {
        $pn = explode('@', $pa)[0];
    }

    // Build transaction note if not provided
    if (empty($tn)) {
        $tn = 'Payment to ' . $pn;
    }

    $queryParts = [];
    $queryParts[] = 'pa=' . rawurlencode($pa);
    $queryParts[] = 'pn=' . rawurlencode($pn);
    $queryParts[] = 'cu=' . rawurlencode($cu);
    
    // Transaction reference - MUST be unique per attempt
    $queryParts[] = 'tr=' . rawurlencode($tr);
    
    // Transaction note
    $queryParts[] = 'tn=' . rawurlencode($tn);

    // Amount (properly formatted to 2 decimal places)
    if (!empty($am) && is_numeric($am) && (float)$am > 0) {
        $queryParts[] = 'am=' . number_format((float)$am, 2, '.', '');
    }

    // Merchant code
    if (!empty($mc)) {
        $queryParts[] = 'mc=' . rawurlencode($mc);
    }

    // Payment mode (00 = P2P)
    $queryParts[] = 'mode=' . rawurlencode($mode);

    // NPCI org ID
    $queryParts[] = 'orgid=000000';

    return $scheme . '://pay?' . implode('&', $queryParts);
}

/**
 * Get all UPI intent URLs for different apps.
 * 
 * @param array $params Same as upi_build_url params (without scheme)
 * @return array URLs keyed by app: generic, gpay, phonepe, paytm, bhim, cred
 */
function upi_get_intent_urls($params) {
    $baseParams = $params;
    unset($baseParams['scheme']);

    // Generate ONE fresh tr for this set (JS will regenerate on each click)
    $tr = upi_generate_tr();
    $baseParams['tr'] = $tr;

    return [
        'generic' => upi_build_url(array_merge($baseParams, ['scheme' => 'upi'])),
        'gpay'    => upi_build_url(array_merge($baseParams, ['scheme' => 'upi'])),     // GPay uses standard upi://
        'phonepe' => upi_build_url(array_merge($baseParams, ['scheme' => 'phonepe'])),
        'paytm'   => upi_build_url(array_merge($baseParams, ['scheme' => 'paytmmp'])),
        'bhim'    => upi_build_url(array_merge($baseParams, ['scheme' => 'upi'])),     // BHIM uses standard upi://
        'cred'    => upi_build_url(array_merge($baseParams, ['scheme' => 'credpay'])), // CRED scheme
    ];
}

/**
 * Render UPI intent buttons HTML with JavaScript that regenerates tr on each click.
 * 
 * This is the KEY function - it outputs buttons that generate a FRESH unique
 * transaction reference every time they are clicked, avoiding risk policy flags.
 * 
 * @param array $params UPI parameters (pa, pn, am, tn)
 * @return string HTML + JavaScript for UPI buttons
 */
function upi_render_intent_buttons($params) {
    $pa = $params['pa'] ?? '';
    $pn = $params['pn'] ?? '';
    $am = $params['am'] ?? '';
    $tn = $params['tn'] ?? 'Payment to ' . ($pn ?: explode('@', $pa)[0]);

    $apps = [
        ['id' => 'gpay', 'name' => 'Google Pay', 'scheme' => 'upi', 'color' => '#4285F4'],
        ['id' => 'phonepe', 'name' => 'PhonePe', 'scheme' => 'phonepe', 'color' => '#5f259f'],
        ['id' => 'paytm', 'name' => 'Paytm', 'scheme' => 'paytmmp', 'color' => '#00BAF2'],
        ['id' => 'bhim', 'name' => 'BHIM', 'scheme' => 'upi', 'color' => '#00897B'],
        ['id' => 'cred', 'name' => 'CRED', 'scheme' => 'credpay', 'color' => '#1a1a2e'],
    ];

    ob_start();
    ?>
    <div class="upi-intent-buttons" id="upi-intent-buttons">
        <?php foreach ($apps as $app): ?>
        <a href="#" class="upi-btn upi-btn-<?= $app['id'] ?>" data-scheme="<?= $app['scheme'] ?>" 
           style="display:inline-block;padding:12px 20px;margin:5px;border-radius:8px;color:#fff;text-decoration:none;font-weight:500;background:<?= $app['color'] ?>;">
            <?= htmlspecialchars($app['name']) ?>
        </a>
        <?php endforeach; ?>
    </div>
    <script>
    (function() {
        var pa = <?= json_encode($pa) ?>;
        var pn = <?= json_encode($pn) ?>;
        var am = <?= json_encode($am) ?>;
        var tn = <?= json_encode($tn) ?>;

        function generateTr() {
            var ts = Date.now().toString(36).toUpperCase();
            var rand = Math.random().toString(36).substring(2, 10).toUpperCase();
            return 'TXN' + ts + rand;
        }

        function buildUrl(scheme) {
            var tr = generateTr(); // FRESH every time
            var parts = [];
            parts.push('pa=' + encodeURIComponent(pa));
            parts.push('pn=' + encodeURIComponent(pn));
            parts.push('cu=INR');
            parts.push('tr=' + encodeURIComponent(tr));
            parts.push('tn=' + encodeURIComponent(tn));
            if (am && parseFloat(am) > 0) {
                parts.push('am=' + parseFloat(am).toFixed(2));
            }
            parts.push('mode=00');
            parts.push('orgid=000000');
            return (scheme || 'upi') + '://pay?' + parts.join('&');
        }

        var buttons = document.querySelectorAll('#upi-intent-buttons a[data-scheme]');
        for (var i = 0; i < buttons.length; i++) {
            buttons[i].addEventListener('click', function(e) {
                e.preventDefault();
                var scheme = this.getAttribute('data-scheme');
                var url = buildUrl(scheme);
                console.log('[UPI] Opening:', url);
                window.location.href = url;
            });
        }
    })();
    </script>
    <?php
    return ob_get_clean();
}
