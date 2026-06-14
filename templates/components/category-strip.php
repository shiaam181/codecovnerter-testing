<?php
/**
 * Category Navigation Strip
 * Expects: $categories array, $baseUrl string (optional)
 */
$baseUrl = $baseUrl ?? (get_current_tenant() ? '/t/' . get_current_tenant()['slug'] : '');
if (empty($categories)) return;
?>
<div class="category-strip">
    <?php foreach ($categories as $cat): ?>
        <a href="<?= $baseUrl ?>/category/<?= e($cat['slug'] ?? '') ?>" class="category-item">
            <?php if (!empty($cat['icon_url'])): ?>
                <img src="<?= e($cat['icon_url']) ?>" alt="<?= e($cat['name'] ?? '') ?>" class="category-icon">
            <?php else: ?>
                <div class="category-icon-placeholder"><?= strtoupper(substr($cat['name'] ?? '?', 0, 1)) ?></div>
            <?php endif; ?>
            <span class="category-name"><?= e(truncate($cat['name'] ?? '', 12)) ?></span>
        </a>
    <?php endforeach; ?>
</div>
