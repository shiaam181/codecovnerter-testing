<?php
/**
 * Admin UPI Accounts Management
 */
// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['admin_action'] ?? '') === 'delete_upi') {
    $deleteId = $_POST['upi_id'] ?? '';
    if ($deleteId) {
        delete_upi_account($deleteId);
        flash('success', 'UPI account deleted.');
    }
    redirect('/admin/upi');
}

$upiAccounts = get_upi_accounts();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UPI Accounts - Admin</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <div class="admin-main">
        <div class="admin-header-row">
            <h1>UPI Accounts</h1>
            <a href="/admin/upi/new" class="btn btn-primary">Add UPI Account</a>
        </div>
        
        <?php $flash = get_flash('success'); if ($flash): ?>
            <div class="flash flash-success"><?= e($flash) ?></div>
        <?php endif; ?>
        
        <div class="admin-info-box">
            <p><strong>How UPI Intent Works:</strong></p>
            <ul>
                <li>The VPA (UPI ID) you add here will be used in payment buttons on checkout & order pages.</li>
                <li>Each payment tap generates a <strong>unique transaction reference (tr)</strong> to avoid risk policy flags.</li>
                <li>Parameters <code>mode=00</code> and <code>orgid=000000</code> are automatically included.</li>
                <li>The highest-priority active account is used for payments.</li>
            </ul>
        </div>
        
        <?php if (empty($upiAccounts)): ?>
            <div class="empty-state">
                <p>No UPI accounts configured.</p>
                <a href="/admin/upi/new" class="btn btn-primary">Add Your First UPI Account</a>
            </div>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Display Name</th>
                        <th>VPA (UPI ID)</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($upiAccounts as $account): ?>
                        <tr>
                            <td><?= e($account['display_name'] ?? $account['name'] ?? '-') ?></td>
                            <td><code><?= e($account['vpa'] ?? $account['upi_id'] ?? '-') ?></code></td>
                            <td><?= e($account['priority'] ?? 0) ?></td>
                            <td>
                                <span class="badge <?= ($account['is_active'] ?? false) ? 'badge-success' : 'badge-muted' ?>">
                                    <?= ($account['is_active'] ?? false) ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td class="actions-cell">
                                <a href="/admin/upi/edit?id=<?= e($account['id'] ?? '') ?>" class="btn btn-sm">Edit</a>
                                <form method="POST" style="display:inline" onsubmit="return confirm('Delete this UPI account?')">
                                    <input type="hidden" name="admin_action" value="delete_upi">
                                    <input type="hidden" name="upi_id" value="<?= e($account['id'] ?? '') ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <!-- CHECK INTENT - Test UPI Payment Section -->
        <div class="admin-section" style="margin-top:30px;padding:24px;background:#f0f9ff;border:2px solid #2874f0;border-radius:12px;">
            <h2 style="margin:0 0 4px;font-size:1.3em;color:#1e293b;">Check Intent</h2>
            <p style="color:#64748b;font-size:0.9em;margin-bottom:20px;">Test if UPI payment works without risk policy errors. Use ₹1 to test. Each button generates a <strong>fresh unique transaction reference</strong>.</p>
            
            <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:end;margin-bottom:20px;">
                <div>
                    <label style="display:block;font-size:0.8em;color:#555;margin-bottom:4px;font-weight:600;">UPI ID (receiving)</label>
                    <input type="text" id="check-intent-pa" value="<?= e(!empty($upiAccounts) ? ($upiAccounts[0]['vpa'] ?? $upiAccounts[0]['upi_id'] ?? '') : '') ?>" 
                           style="padding:10px 14px;border:1px solid #cbd5e1;border-radius:8px;width:240px;font-size:0.95em;" readonly>
                </div>
                <div>
                    <label style="display:block;font-size:0.8em;color:#555;margin-bottom:4px;font-weight:600;">Test Amount (₹)</label>
                    <input type="number" id="check-intent-amount" value="1" min="1" step="0.01" 
                           style="padding:10px 14px;border:1px solid #cbd5e1;border-radius:8px;width:100px;font-size:0.95em;">
                </div>
                <button id="check-intent-btn" style="padding:10px 24px;background:#2874f0;color:#fff;border:none;border-radius:8px;cursor:pointer;font-weight:600;font-size:0.95em;">
                    Generate & Show
                </button>
            </div>
            
            <div id="check-intent-result" style="display:none;">
                <div style="margin-bottom:16px;">
                    <label style="display:block;font-size:0.8em;color:#555;margin-bottom:6px;font-weight:600;">Generated UPI URL (fresh tr each time):</label>
                    <div style="padding:12px;background:#1e293b;border-radius:8px;overflow-x:auto;">
                        <code id="check-intent-url" style="color:#4ade80;font-size:0.82em;word-break:break-all;line-height:1.6;"></code>
                    </div>
                </div>
                
                <p style="font-size:0.85em;color:#555;margin-bottom:12px;font-weight:500;">Tap any button below to test (each tap = new unique tr):</p>
                
                <div style="display:flex;flex-wrap:wrap;gap:10px;">
                    <a id="check-intent-generic" href="#" style="display:inline-flex;align-items:center;gap:8px;padding:12px 20px;background:#388e3c;color:#fff;border-radius:8px;text-decoration:none;font-weight:500;font-size:0.95em;">
                        <span style="font-size:1.2em;">💳</span> Any UPI App
                    </a>
                    <a id="check-intent-gpay" href="#" style="display:inline-flex;align-items:center;gap:8px;padding:12px 20px;background:#4285F4;color:#fff;border-radius:8px;text-decoration:none;font-weight:500;font-size:0.95em;">
                        <span style="font-size:1.2em;">G</span> Google Pay
                    </a>
                    <a id="check-intent-phonepe" href="#" style="display:inline-flex;align-items:center;gap:8px;padding:12px 20px;background:#5f259f;color:#fff;border-radius:8px;text-decoration:none;font-weight:500;font-size:0.95em;">
                        <span style="font-size:1.2em;">P</span> PhonePe
                    </a>
                    <a id="check-intent-paytm" href="#" style="display:inline-flex;align-items:center;gap:8px;padding:12px 20px;background:#00BAF2;color:#fff;border-radius:8px;text-decoration:none;font-weight:500;font-size:0.95em;">
                        <span style="font-size:1.2em;">₹</span> Paytm
                    </a>
                    <a id="check-intent-bhim" href="#" style="display:inline-flex;align-items:center;gap:8px;padding:12px 20px;background:#00897B;color:#fff;border-radius:8px;text-decoration:none;font-weight:500;font-size:0.95em;">
                        <span style="font-size:1.2em;">B</span> BHIM
                    </a>
                    <a id="check-intent-cred" href="#" style="display:inline-flex;align-items:center;gap:8px;padding:12px 20px;background:#1a1a2e;color:#fff;border-radius:8px;text-decoration:none;font-weight:500;font-size:0.95em;">
                        <span style="font-size:1.2em;">C</span> CRED
                    </a>
                </div>
                
                <p id="check-intent-info" style="margin-top:12px;font-size:0.8em;color:#888;font-family:monospace;"></p>
            </div>
        </div>
        
        <script>
        (function() {
            var accounts = <?= json_encode($upiAccounts) ?>;
            var primaryAcc = accounts.length ? accounts[0] : null;
            var payeeName = primaryAcc ? (primaryAcc.display_name || primaryAcc.name || 'Payee') : 'Payee';
            
            function generateTr() {
                var ts = Date.now().toString(36).toUpperCase();
                var rand = Math.random().toString(36).substring(2, 10).toUpperCase();
                return 'TXN' + ts + rand;
            }
            
            function buildUrl(scheme) {
                var pa = document.getElementById('check-intent-pa').value;
                var am = document.getElementById('check-intent-amount').value || '1';
                var tr = generateTr();
                var tn = 'Test payment to ' + payeeName;
                
                var parts = [];
                parts.push('pa=' + encodeURIComponent(pa));
                parts.push('pn=' + encodeURIComponent(payeeName));
                parts.push('cu=INR');
                parts.push('tr=' + encodeURIComponent(tr));
                parts.push('tn=' + encodeURIComponent(tn));
                parts.push('am=' + parseFloat(am).toFixed(2));
                parts.push('mode=00');
                parts.push('orgid=000000');
                
                var url = (scheme || 'upi') + '://pay?' + parts.join('&');
                
                document.getElementById('check-intent-url').textContent = url;
                document.getElementById('check-intent-info').textContent = 'TR: ' + tr + ' | Time: ' + new Date().toLocaleTimeString();
                document.getElementById('check-intent-result').style.display = 'block';
                
                return url;
            }
            
            // Generate button
            document.getElementById('check-intent-btn').addEventListener('click', function() {
                buildUrl('upi');
            });
            
            // App buttons - each generates fresh tr and opens
            var appButtons = {
                'check-intent-generic': 'upi',
                'check-intent-gpay': 'upi',
                'check-intent-phonepe': 'phonepe',
                'check-intent-paytm': 'paytmmp',
                'check-intent-bhim': 'upi',
                'check-intent-cred': 'credpay'
            };
            
            Object.keys(appButtons).forEach(function(btnId) {
                var btn = document.getElementById(btnId);
                if (btn) {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        var url = buildUrl(appButtons[btnId]);
                        window.location.href = url;
                    });
                }
            });
        })();
        </script>
    </div>
</div>
</body>
</html>
