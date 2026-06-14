<?php
/**
 * 404 Not Found Page
 */
include __DIR__ . '/../templates/header.php';
?>

<div class="error-page">
    <h1>404</h1>
    <p>Page not found</p>
    <a href="<?= tenant_url('/') ?>" class="btn btn-primary">Go Home</a>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
