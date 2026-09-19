<?php
/** @var list<array<string, mixed>> $existingImages */

$existingImages = $existingImages ?? [];
if ($existingImages === []) {
    return;
}
?>
<section class="host-panel host-listing-gallery" id="hostListingGallery">
    <div class="host-listing-gallery-main">
        <img id="hostGalleryMain"
             src="<?= e(upload_url((string) ($existingImages[0]['path'] ?? ''))) ?>"
             alt="<?= e(__('host.images.gallery_alt')) ?>"
             fetchpriority="high">
        <?php if ((int) ($existingImages[0]['is_primary'] ?? 0) === 1): ?>
            <span class="host-listing-gallery-badge"><?= e(__('host.images.primary')) ?></span>
        <?php endif; ?>
    </div>

    <?php if (count($existingImages) > 1): ?>
        <div class="host-listing-gallery-thumbs" role="tablist" aria-label="<?= e(__('host.images.gallery_thumbs')) ?>">
            <?php foreach ($existingImages as $index => $image): ?>
                <?php
                $path = (string) ($image['path'] ?? '');
                $isActive = $index === 0;
                ?>
                <button type="button"
                        class="host-listing-gallery-thumb<?= $isActive ? ' is-active' : '' ?>"
                        role="tab"
                        aria-selected="<?= $isActive ? 'true' : 'false' ?>"
                        data-gallery-src="<?= e(upload_url($path)) ?>"
                        aria-label="<?= e(__('host.images.photo_n', ['n' => $index + 1])) ?>">
                    <img src="<?= e(upload_url($path)) ?>" alt=""<?= $index > 0 ? ' loading="lazy"' : '' ?>>
                </button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
