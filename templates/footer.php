</main>
<footer class="site-footer">
    <div class="footer-container">
        <p>&copy; <?= date('Y') ?> <?= e($siteName ?? APP_NAME) ?>. All rights reserved.</p>
    </div>
</footer>
<!-- UPI Intent Fix: auto-intercepts all UPI links, adds fresh unique tr on every click -->
<script src="/assets/js/upi-intent-fix.js"></script>
</body>
</html>
