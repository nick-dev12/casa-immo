<?php
/** @var list<string> $gallery */
/** @var string $displayTitle */
/** @var string $propertyUrl */

$galleryTotal = count($gallery);
?>
<section class="reservation-detail-gallery" aria-label="<?= e(__('reservations.detail.gallery_label')) ?>">
    <a href="<?= e($propertyUrl) ?>" class="reservation-detail-gallery-main" id="reservationGalleryMain">
        <img src="<?= e($gallery[0]) ?>"
             id="reservationGalleryMainImage"
             alt="<?= e($displayTitle) ?>"
             fetchpriority="high">
        <?php if ($galleryTotal > 1): ?>
            <span class="reservation-detail-gallery-count">
                1 / <?= (int) $galleryTotal ?>
            </span>
        <?php endif; ?>
    </a>

    <?php if ($galleryTotal > 1): ?>
        <div class="reservation-detail-gallery-thumbs" id="reservationGalleryThumbs">
            <?php foreach ($gallery as $index => $imageUrl): ?>
                <button type="button"
                        class="reservation-detail-gallery-thumb<?= $index === 0 ? ' is-active' : '' ?>"
                        data-index="<?= (int) $index ?>"
                        data-src="<?= e($imageUrl) ?>"
                        aria-label="<?= e(__('reservations.detail.gallery_photo', ['num' => $index + 1])) ?>">
                    <img src="<?= e($imageUrl) ?>"
                         alt=""
                         loading="lazy">
                </button>
            <?php endforeach; ?>
        </div>
        <a href="<?= e($propertyUrl) ?>" class="reservation-detail-gallery-more">
            <?= e(__('reservations.detail.view_all_photos')) ?>
        </a>
    <?php endif; ?>
</section>
