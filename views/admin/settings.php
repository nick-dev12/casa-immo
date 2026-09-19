<?php
/** @var list<array<string, mixed>> $items */
/** @var int $currentUserId */

$activeNav = 'admins';
include base_path('views/admin/partials/header.php');
?>
<section class="admin-panel">
    <div class="admin-panel-toolbar">
        <p class="admin-panel-lead"><?= e(__('admin.admins_lead')) ?></p>
        <a href="<?= url('/admin/settings/new') ?>" class="admin-btn admin-btn-primary">
            <i class="bi bi-shield-plus" aria-hidden="true"></i>
            <?= e(__('admin.admins_create_btn')) ?>
        </a>
    </div>
</section>

<section class="admin-panel">
    <h2 class="admin-panel-title"><?= e(__('admin.admins_list_title')) ?></h2>
    <?php if ($items === []): ?>
        <div class="admin-empty"><p><?= e(__('admin.admins_empty')) ?></p></div>
    <?php else: ?>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th><?= e(__('admin.col_user')) ?></th>
                        <th><?= e(__('admin.col_email')) ?></th>
                        <th><?= e(__('profile.phone')) ?></th>
                        <th><?= e(__('admin.col_status')) ?></th>
                        <th><?= e(__('admin.admins_col.since')) ?></th>
                        <th><?= e(__('admin.col_actions')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <?php
                        $adminId = (int) ($item['id'] ?? 0);
                        $status = (string) ($item['status'] ?? 'active');
                        $isSelf = $adminId === $currentUserId;
                        ?>
                        <tr>
                            <td>
                                <strong><?= e(trim((string) ($item['first_name'] ?? '') . ' ' . (string) ($item['last_name'] ?? ''))) ?></strong>
                                <span class="admin-table-sub"><?= e(__('admin.super_admin_badge')) ?><?= $isSelf ? ' · ' . e(__('admin.admins_you')) : '' ?></span>
                            </td>
                            <td><?= e((string) ($item['email'] ?? '')) ?></td>
                            <td><?= e((string) ($item['phone'] ?? '—')) ?></td>
                            <td><span class="admin-pill admin-pill-<?= e($status === 'active' ? 'approved' : ($status === 'banned' ? 'rejected' : 'draft')) ?>"><?= e(__('admin.moderation.owner_status.' . $status)) ?></span></td>
                            <td><?= e(format_date((string) ($item['created_at'] ?? ''))) ?></td>
                            <td>
                                <div class="admin-table-actions">
                                    <?php if ($status === 'active' && !$isSelf): ?>
                                        <form method="post" action="<?= url('/admin/settings/' . $adminId . '/deactivate') ?>">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="admin-btn admin-btn-sm admin-btn-ghost"
                                                    data-confirm="<?= e(__('admin.admins_confirm_deactivate')) ?>">
                                                <?= e(__('admin.admins_deactivate')) ?>
                                            </button>
                                        </form>
                                    <?php elseif ($status !== 'active'): ?>
                                        <form method="post" action="<?= url('/admin/settings/' . $adminId . '/activate') ?>">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="admin-btn admin-btn-sm admin-btn-primary">
                                                <?= e(__('admin.admins_activate')) ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if (!$isSelf): ?>
                                        <form method="post" action="<?= url('/admin/settings/' . $adminId . '/delete') ?>">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger"
                                                    data-confirm="<?= e(__('admin.admins_confirm_delete')) ?>">
                                                <?= e(__('admin.admins_delete')) ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<script>
document.querySelectorAll('.admin-table-actions button[data-confirm]').forEach(function (btn) {
    btn.addEventListener('click', function (event) {
        if (!window.confirm(btn.getAttribute('data-confirm'))) {
            event.preventDefault();
        }
    });
});
</script>
<?php include base_path('views/admin/partials/footer.php'); ?>
