<?php
/**
 * Tenant Admin Login
 */
$tenant = get_current_tenant();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (admin_login($email, $password)) {
        redirect('/t/' . $tenant['slug'] . '/admin');
    } else {
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?= e($tenant['store_name'] ?? $tenant['slug']) ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="admin-login-page">
    <div class="login-card">
        <h1><?= e($tenant['store_name'] ?? $tenant['slug']) ?> Admin</h1>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required class="form-input" placeholder="admin@example.com">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required class="form-input">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
    </div>
</div>
</body>
</html>
