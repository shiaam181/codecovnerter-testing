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
        
        <!-- Test UPI Link Section -->
        <div class="admin-section" style="margin-top:30px;">
            <h2>Test UPI Intent Link</h2>
            <p>Enter a test amount to generate a UPI intent link and test it on your phone.</p>
            <div class="test-upi-form">
                <input type="number" id="test-amount" placeholder="Enter amount (e.g. 1)" value="1" step="0.01" min="1" class="form-input" style="max-width:200px;display:inline-block;">
                <button onclick="testUpiLink()" class="btn btn-primary">Generate Test Link</button>
            </div>
            <div id="test-result" style="margin-top:15px;display:none;">
                <p><strong>Generated UPI URL:</strong></p>
                <code id="test-url" style="word-break:break-all;display:block;padding:10px;background:#f5f5f5;border-radius:5px;"></code>
                <br>
                <a id="test-link" href="#" class="btn btn-primary">Open UPI App</a>
            </div>
            <script>
            function testUpiLink() {
                var accounts = <?= json_encode($upiAccounts) ?>;
                if (!accounts.length) { alert('No UPI accounts configured!'); return; }
                var acc = accounts[0];
                var upiId = acc.vpa || acc.upi_id || '';
                var upiName = acc.display_name || acc.name || 'Test';
                var amount = document.getElementById('test-amount').value || '1';
                
                var tr = 'TXN' + Date.now().toString(36).toUpperCase() + Math.random().toString(36).substring(2,8).toUpperCase();
                var params = [];
                params.push('pa=' + encodeURIComponent(upiId));
                params.push('pn=' + encodeURIComponent(upiName));
                params.push('cu=INR');
                params.push('tr=' + encodeURIComponent(tr));
                params.push('tn=' + encodeURIComponent('Test payment to ' + upiName));
                params.push('am=' + parseFloat(amount).toFixed(2));
                params.push('mode=00');
                params.push('orgid=000000');
                var url = 'upi://pay?' + params.join('&');
                
                document.getElementById('test-url').textContent = url;
                document.getElementById('test-link').href = url;
                document.getElementById('test-result').style.display = 'block';
            }
            </script>
        </div>
    </div>
</div>
</body>
</html>
