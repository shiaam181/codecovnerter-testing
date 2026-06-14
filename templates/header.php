<?php
/**
 * Site Header Template
 */
$tenant = get_current_tenant();
$siteName = $tenant ? ($tenant['store_name'] ?? $tenant['slug']) : APP_NAME;
$baseUrl = $tenant ? '/t/' . $tenant['slug'] : '';
$cartItemCount = cart_count();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($siteName) ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="header-container">
        <a href="<?= $baseUrl ?>/" class="logo"><?= e($siteName) ?></a>
        <nav class="header-nav">
            <a href="<?= $baseUrl ?>/search" class="nav-link">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            </a>
            <a href="<?= $baseUrl ?>/cart" class="nav-link cart-link">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                <?php if ($cartItemCount > 0): ?>
                    <span class="cart-badge"><?= $cartItemCount ?></span>
                <?php endif; ?>
            </a>
        </nav>
    </div>
</header>
<?php
$flashSuccess = get_flash('success');
$flashError = get_flash('error');
if ($flashSuccess): ?>
    <div class="flash flash-success"><?= e($flashSuccess) ?></div>
<?php endif;
if ($flashError): ?>
    <div class="flash flash-error"><?= e($flashError) ?></div>
<?php endif; ?>
<main class="main-content">
