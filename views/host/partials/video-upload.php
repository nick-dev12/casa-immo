<?php
/** @var string|null $existingVideoPath */

$existingVideoPath = $existingVideoPath ?? null;
$maxMb = (int) config('app', 'property_video_max_mb', 50);
?>
<section class="host-panel host-form-section host-video-section" data-listing-section="logement"<?= ($listingKind ?? 'logement') === 'terrain' ? ' hidden' : '' ?>>
    <div class="host-video-head">
        <div>
            <h2 class="host-section-title"><?= e(__('host.section.video')) ?></h2>
            <p class="host-section-hint"><?= e(__('host.video.lead', ['max' => $maxMb])) ?></p>
        </div>
        <span class="host-video-badge"><?= e(__('host.video.optional')) ?></span>
    </div>

    <div class="host-video-slot" id="hostVideoSlot">
        <?php if ($existingVideoPath !== null && $existingVideoPath !== ''): ?>
            <article class="host-video-card host-video-card-existing" id="hostVideoExisting">
                <video class="host-video-preview"
                       controls
                       playsinline
                       preload="metadata"
                       src="<?= e(upload_url($existingVideoPath)) ?>"></video>
                <button type="button"
                        class="host-video-remove"
                        id="hostVideoRemoveBtn"
                        aria-label="<?= e(__('host.video.remove')) ?>">
                    <i class="bi bi-x-lg"></i>
                </button>
            </article>
        <?php else: ?>
            <label class="host-video-card host-video-add" id="hostVideoAdd">
                <input type="file"
                       id="hostVideoInput"
                       class="host-video-file-input"
                       accept="video/mp4,video/webm,video/quicktime,.mp4,.webm,.mov">
                <i class="bi bi-camera-video"></i>
                <span><?= e(__('host.video.add')) ?></span>
                <small><?= e(__('host.video.formats', ['max' => $maxMb])) ?></small>
            </label>
        <?php endif; ?>
    </div>

    <p class="host-video-note"
       id="hostVideoNote"
       data-msg-uploading="<?= e(__('host.video.uploading')) ?>"
       data-msg-upload-error="<?= e(__('host.video.error_upload')) ?>"
       data-msg-remove-error="<?= e(__('host.video.error_remove')) ?>"
       data-msg-invalid-type="<?= e(__('host.video.error_type')) ?>"
       data-msg-queued="<?= e(__('host.video.note_save')) ?>"><?= e(__('host.video.note')) ?></p>
</section>
