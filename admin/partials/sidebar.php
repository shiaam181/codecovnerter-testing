<?php
/**
 * Admin Sidebar Navigation
 */
$currentPath = current_path();
?>
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <a href="/admin" class="sidebar-logo"><?= APP_NAME ?> Admin</a>
    </div>
    <nav class="sidebar-nav">
        <a href="/admin" class="sidebar-link <?= $currentPath === '/admin' ? 'active' : '' ?>">Dashboard</a>
        <a href="/admin/orders" class="sidebar-link <?= strpos($currentPath, '/admin/orders') === 0 ? 'active' : '' ?>">Orders</a>
        <a href="/admin/products" class="sidebar-link <?= strpos($currentPath, '/admin/products') === 0 ? 'active' : '' ?>">Products</a>
        <a href="/admin/categories" class="sidebar-link <?= strpos($currentPath, '/admin/categories') === 0 ? 'active' : '' ?>">Categories</a>
        <a href="/admin/upi" class="sidebar-link <?= strpos($currentPath, '/admin/upi') === 0 ? 'active' : '' ?>">UPI Accounts</a>
        <a href="/admin/banners" class="sidebar-link <?= strpos($currentPath, '/admin/banners') === 0 ? 'active' : '' ?>">Banners</a>
        <a href="/admin/payment-offers" class="sidebar-link <?= strpos($currentPath, '/admin/payment-offers') === 0 ? 'active' : '' ?>">Payment Offers</a>
        <a href="/admin/tenants" class="sidebar-link <?= strpos($currentPath, '/admin/tenants') === 0 ? 'active' : '' ?>">Tenants</a>
        <a href="/admin/homepage-layout" class="sidebar-link <?= strpos($currentPath, '/admin/homepage-layout') === 0 ? 'active' : '' ?>">Homepage Layout</a>
        <a href="/admin/theme" class="sidebar-link <?= strpos($currentPath, '/admin/theme') === 0 ? 'active' : '' ?>">Theme</a>
        <a href="/admin/icon-settings" class="sidebar-link <?= strpos($currentPath, '/admin/icon-settings') === 0 ? 'active' : '' ?>">Icon Settings</a>
    </nav>
    <div class="sidebar-footer">
        <form method="POST">
            <input type="hidden" name="admin_action" value="logout">
            <button type="submit" class="btn btn-sm btn-outline">Logout</button>
        </form>
        <p class="admin-user"><?= e($_SESSION['admin_username'] ?? 'Admin') ?></p>
    </div>
</aside>
