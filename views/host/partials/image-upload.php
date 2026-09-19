<?php
/** @var list<array<string, mixed>> $existingImages */
/** @var int $imagesMin */
/** @var int $imagesMax */

$existingImages = $existingImages ?? [];
$imagesMin = $imagesMin ?? (int) config('app', 'property_images_min', 4);
$imagesMax = $imagesMax ?? (int) config('app', 'property_images_max', 10);
?>
<section class="host-panel host-form-section host-images-section">
    <div class="host-images-head">
        <div>
            <h2 class="host-section-title"><?= e(__('host.section.photos')) ?></h2>
            <p class="host-section-hint"><?= e(__('host.images.lead', ['min' => $imagesMin, 'max' => $imagesMax])) ?></p>
        </div>
        <span class="host-images-counter"
              id="hostImagesCounter"
              data-min="<?= $imagesMin ?>"
              data-max="<?= $imagesMax ?>"
              data-initial-count="<?= count($existingImages) ?>"
              data-label-primary="<?= e(__('host.images.primary')) ?>"
              data-label-remove="<?= e(__('host.images.remove')) ?>"
              data-msg-compressing="<?= e(__('host.images.compressing')) ?>"
              data-msg-uploading="<?= e(__('host.images.uploading')) ?>"
              data-msg-wait-photos="<?= e(__('host.images.wait_photos')) ?>"
              data-msg-processing="<?= e(__('host.images.processing')) ?>"
              data-msg-compress-error="<?= e(__('host.images.compress_error')) ?>"
              data-msg-upload-error="<?= e(__('host.images.error_upload')) ?>">
            <?= e(__('host.images.counter', ['count' => count($existingImages), 'max' => $imagesMax])) ?>
        </span>
    </div>

    <div class="host-images-grid" id="hostImagesGrid">
        <?php foreach ($existingImages as $image): ?>
            <?php
            $imageId = (int) ($image['id'] ?? 0);
            $path = (string) ($image['path'] ?? '');
            $isPrimary = (int) ($image['is_primary'] ?? 0) === 1;
            ?>
            <article class="host-image-card host-image-card-existing" data-image-id="<?= $imageId ?>">
                <img src="<?= e(upload_url($path)) ?>" alt="">
                <?php if ($isPrimary): ?>
                    <span class="host-image-badge"><?= e(__('host.images.primary')) ?></span>
                <?php endif; ?>
                <button type="button"
                        class="host-image-remove"
                        data-remove-existing="<?= $imageId ?>"
                        aria-label="<?= e(__('host.images.remove')) ?>">
                    <i class="bi bi-x-lg"></i>
                </button>
            </article>
        <?php endforeach; ?>

        <label class="host-image-card host-image-add" id="hostImageAdd">
            <input type="file"
                   id="hostImagesInput"
                   class="host-images-file-input"
                   accept="image/*"
                   multiple>
            <i class="bi bi-camera"></i>
            <span><?= e(__('host.images.add')) ?></span>
            <small><?= e(__('host.images.formats')) ?></small>
        </label>
    </div>

    <div id="hostRemoveImageInputs"></div>
    <p class="host-images-note" id="hostImagesNote"><?= e(__('host.images.note_save', ['min' => $imagesMin])) ?></p>
</section>
