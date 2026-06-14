<?php
/**
 * Banner Carousel Component
 * Expects: $banners array
 */
if (empty($banners)) return;
?>
<div class="banner-carousel">
    <div class="banner-track">
        <?php foreach ($banners as $i => $banner): ?>
            <div class="banner-slide <?= $i === 0 ? 'active' : '' ?>">
                <?php if (!empty($banner['link_url'])): ?>
                    <a href="<?= e($banner['link_url']) ?>">
                <?php endif; ?>
                <img src="<?= e($banner['image_url'] ?? '') ?>" alt="<?= e($banner['title'] ?? 'Banner') ?>" loading="lazy">
                <?php if (!empty($banner['link_url'])): ?>
                    </a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
