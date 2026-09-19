<?php
/**
 * @var string $moderationTargetType property|land
 * @var int $moderationTargetId
 * @var array<string, string> $motives
 * @var bool $canRestore
 */
?>
<section class="admin-panel admin-moderation-panel">
    <h2 class="admin-panel-title"><?= e(__('admin.moderation.actions_title')) ?></h2>
    <p class="admin-panel-lead"><?= e(__('admin.moderation.actions_lead')) ?></p>

    <form method="post" action="<?= url('/admin/moderation') ?>" class="admin-moderation-form">
        <?= csrf_field() ?>
        <input type="hidden" name="target_type" value="<?= e($moderationTargetType) ?>">
        <input type="hidden" name="target_id" value="<?= (int) $moderationTargetId ?>">

        <fieldset class="admin-moderation-motives-field">
            <legend><?= e(__('admin.moderation.motives_label')) ?></legend>
            <div class="admin-moderation-motives-grid">
                <?php foreach ($motives as $slug => $label): ?>
                    <label class="admin-moderation-motive">
                        <input type="checkbox" name="motives[]" value="<?= e($slug) ?>">
                        <span><?= e($label) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </fieldset>

        <label class="admin-form-field">
            <span><?= e(__('admin.moderation.notes_label')) ?></span>
            <textarea name="notes" rows="3" placeholder="<?= e(__('admin.moderation.notes_ph')) ?>"></textarea>
        </label>

        <div class="admin-moderation-actions">
            <button type="submit" name="action" value="warning" class="admin-btn admin-btn-outline">
                <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                <?= e(__('admin.moderation.btn_warning')) ?>
            </button>
            <button type="submit" name="action" value="suspend" class="admin-btn admin-btn-danger"
                    data-confirm="<?= e(__('admin.moderation.confirm_suspend')) ?>">
                <i class="bi bi-slash-circle" aria-hidden="true"></i>
                <?= e(__('admin.moderation.btn_suspend')) ?>
            </button>
            <button type="submit" name="action" value="reject" class="admin-btn admin-btn-danger"
                    data-confirm="<?= e(__('admin.moderation.confirm_reject')) ?>">
                <i class="bi bi-x-circle" aria-hidden="true"></i>
                <?= e(__('admin.moderation.btn_reject')) ?>
            </button>
            <?php if (!empty($canRestore)): ?>
                <button type="submit" name="action" value="restore" class="admin-btn admin-btn-primary"
                        data-confirm="<?= e(__('admin.moderation.confirm_restore')) ?>">
                    <i class="bi bi-check-circle" aria-hidden="true"></i>
                    <?= e(__('admin.moderation.btn_restore')) ?>
                </button>
            <?php endif; ?>
        </div>
    </form>
</section>
<script>
document.querySelectorAll('.admin-moderation-form button[data-confirm]').forEach(function (btn) {
    btn.addEventListener('click', function (event) {
        if (!window.confirm(btn.getAttribute('data-confirm'))) {
            event.preventDefault();
        }
    });
});
</script>
