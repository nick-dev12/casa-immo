<?php
/** @var array<string, mixed>|null $project */
/** @var list<array<string, mixed>> $milestones */
/** @var list<array<string, mixed>> $properties */
/** @var bool $isEdit */

$activeNav = 'construction';
include base_path('views/admin/partials/header.php');

$p = $project ?? [];
$projectId = (int) ($p['id'] ?? 0);
$formAction = $isEdit
    ? url('/admin/construction/' . $projectId)
    : url('/admin/construction');

$typeLabels = [
    'maison_fini' => __('admin.construction_type.maison_fini'),
    'maison_finition' => __('admin.construction_type.maison_finition'),
    'villa' => __('admin.construction_type.villa'),
    'immeuble' => __('admin.construction_type.immeuble'),
    'autre' => __('admin.construction_type.autre'),
];

$statusLabels = [
    'devis' => __('admin.construction_status.devis'),
    'planning' => __('admin.construction_status.planning'),
    'chantier' => __('admin.construction_status.chantier'),
    'livraison' => __('admin.construction_status.livraison'),
    'termine' => __('admin.construction_status.termine'),
    'annule' => __('admin.construction_status.annule'),
];

$milestoneStatuses = [
    'pending' => __('admin.milestone_status.pending'),
    'in_progress' => __('admin.milestone_status.in_progress'),
    'done' => __('admin.milestone_status.done'),
];

if ($milestones === []) {
    $milestones = [
        ['title' => __('admin.milestone.default_1'), 'due_date' => '', 'status' => 'pending'],
        ['title' => __('admin.milestone.default_2'), 'due_date' => '', 'status' => 'pending'],
        ['title' => __('admin.milestone.default_3'), 'due_date' => '', 'status' => 'pending'],
    ];
}
?>
<section class="admin-panel admin-form-panel">
    <p class="admin-panel-lead"><?= e(__('admin.construction_form_lead')) ?></p>

    <form method="post" action="<?= e($formAction) ?>" class="admin-form">
        <?= csrf_field() ?>

        <div class="admin-form-grid">
            <div class="admin-form-field admin-form-field-full">
                <label for="title"><?= e(__('admin.construction_field.title')) ?> *</label>
                <input type="text" id="title" name="title" required value="<?= e((string) ($p['title'] ?? '')) ?>" placeholder="<?= e(__('admin.construction_field.title_ph')) ?>">
            </div>

            <div class="admin-form-field">
                <label for="project_type"><?= e(__('admin.construction_field.type')) ?></label>
                <select id="project_type" name="project_type">
                    <?php foreach ($typeLabels as $value => $label): ?>
                        <option value="<?= e($value) ?>"<?= (($p['project_type'] ?? 'maison_finition') === $value) ? ' selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="admin-form-field">
                <label for="status"><?= e(__('admin.construction_field.status')) ?></label>
                <select id="status" name="status">
                    <?php foreach ($statusLabels as $value => $label): ?>
                        <option value="<?= e($value) ?>"<?= (($p['status'] ?? 'devis') === $value) ? ' selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="admin-form-field">
                <label for="city"><?= e(__('admin.construction_field.city')) ?> *</label>
                <input type="text" id="city" name="city" required value="<?= e((string) ($p['city'] ?? '')) ?>">
            </div>

            <div class="admin-form-field">
                <label for="district"><?= e(__('admin.construction_field.district')) ?></label>
                <input type="text" id="district" name="district" value="<?= e((string) ($p['district'] ?? '')) ?>">
            </div>

            <div class="admin-form-field admin-form-field-full">
                <label for="address"><?= e(__('admin.construction_field.address')) ?></label>
                <input type="text" id="address" name="address" value="<?= e((string) ($p['address'] ?? '')) ?>">
            </div>

            <div class="admin-form-field">
                <label for="quote_amount"><?= e(__('admin.construction_field.quote')) ?></label>
                <input type="number" id="quote_amount" name="quote_amount" min="0" step="1" value="<?= e((string) ($p['quote_amount'] ?? '')) ?>">
            </div>

            <div class="admin-form-field">
                <label for="progress"><?= e(__('admin.construction_field.progress')) ?></label>
                <input type="number" id="progress" name="progress" min="0" max="100" value="<?= (int) ($p['progress'] ?? 0) ?>">
            </div>

            <div class="admin-form-field">
                <label for="start_date"><?= e(__('admin.construction_field.start_date')) ?></label>
                <input type="date" id="start_date" name="start_date" value="<?= e((string) ($p['start_date'] ?? '')) ?>">
            </div>

            <div class="admin-form-field">
                <label for="delivery_date"><?= e(__('admin.construction_field.delivery_date')) ?></label>
                <input type="date" id="delivery_date" name="delivery_date" value="<?= e((string) ($p['delivery_date'] ?? '')) ?>">
            </div>

            <div class="admin-form-field admin-form-field-full">
                <label for="property_id"><?= e(__('admin.construction_field.property')) ?></label>
                <select id="property_id" name="property_id">
                    <option value=""><?= e(__('admin.construction_field.property_none')) ?></option>
                    <?php foreach ($properties as $property): ?>
                        <option value="<?= (int) $property['id'] ?>"<?= ((int) ($p['property_id'] ?? 0) === (int) $property['id']) ? ' selected' : '' ?>>
                            <?= e((string) $property['title']) ?> — <?= e(property_type((string) ($property['type'] ?? 'autre'))) ?> (<?= e((string) ($property['city'] ?? '')) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="admin-form-hint"><?= e(__('admin.construction_field.property_hint')) ?></p>
            </div>

            <div class="admin-form-field">
                <label for="client_name"><?= e(__('admin.construction_field.client')) ?></label>
                <input type="text" id="client_name" name="client_name" value="<?= e((string) ($p['client_name'] ?? '')) ?>">
            </div>

            <div class="admin-form-field">
                <label for="client_phone"><?= e(__('admin.construction_field.phone')) ?></label>
                <input type="tel" id="client_phone" name="client_phone" value="<?= e((string) ($p['client_phone'] ?? '')) ?>">
            </div>

            <div class="admin-form-field admin-form-field-full">
                <label for="description"><?= e(__('admin.construction_field.description')) ?></label>
                <textarea id="description" name="description" rows="3"><?= e((string) ($p['description'] ?? '')) ?></textarea>
            </div>

            <div class="admin-form-field admin-form-field-full">
                <label for="notes"><?= e(__('admin.construction_field.notes')) ?></label>
                <textarea id="notes" name="notes" rows="2"><?= e((string) ($p['notes'] ?? '')) ?></textarea>
            </div>
        </div>

        <div class="admin-milestones">
            <div class="admin-milestones-head">
                <h3><?= e(__('admin.construction_milestones')) ?></h3>
                <p><?= e(__('admin.construction_milestones_lead')) ?></p>
            </div>
            <div id="milestonesList" class="admin-milestones-list">
                <?php foreach ($milestones as $index => $milestone): ?>
                    <div class="admin-milestone-row">
                        <input type="text" name="milestone_title[]" value="<?= e((string) ($milestone['title'] ?? '')) ?>" placeholder="<?= e(__('admin.milestone.title_ph')) ?>">
                        <input type="date" name="milestone_due_date[]" value="<?= e((string) ($milestone['due_date'] ?? '')) ?>">
                        <select name="milestone_status[]">
                            <?php foreach ($milestoneStatuses as $value => $label): ?>
                                <option value="<?= e($value) ?>"<?= (($milestone['status'] ?? 'pending') === $value) ? ' selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="admin-btn admin-btn-sm admin-btn-ghost" id="addMilestoneBtn">
                <i class="bi bi-plus-lg" aria-hidden="true"></i> <?= e(__('admin.milestone.add')) ?>
            </button>
        </div>

        <div class="admin-form-actions">
            <a href="<?= url('/admin/construction') ?>" class="admin-btn admin-btn-ghost"><?= e(__('admin.construction_cancel')) ?></a>
            <button type="submit" class="admin-btn admin-btn-primary"><?= e($isEdit ? __('admin.construction_save') : __('admin.construction_create')) ?></button>
        </div>
    </form>
</section>

<template id="milestoneRowTemplate">
    <div class="admin-milestone-row">
        <input type="text" name="milestone_title[]" placeholder="<?= e(__('admin.milestone.title_ph')) ?>">
        <input type="date" name="milestone_due_date[]">
        <select name="milestone_status[]">
            <?php foreach ($milestoneStatuses as $value => $label): ?>
                <option value="<?= e($value) ?>"><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</template>

<script>
(function () {
    const btn = document.getElementById('addMilestoneBtn');
    const list = document.getElementById('milestonesList');
    const tpl = document.getElementById('milestoneRowTemplate');
    if (!btn || !list || !tpl) return;
    btn.addEventListener('click', function () {
        list.appendChild(tpl.content.cloneNode(true));
    });
})();
</script>
<?php include base_path('views/admin/partials/footer.php'); ?>
