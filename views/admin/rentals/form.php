<?php
/** @var list<array<string, mixed>> $properties */

$activeNav = 'rentals';
include base_path('views/admin/partials/header.php');

$contractTypes = [
    'habitation' => __('admin.rental.type.habitation'),
    'commercial' => __('admin.rental.type.commercial'),
    'saisonnier' => __('admin.rental.type.saisonnier'),
    'autre' => __('admin.rental.type.autre'),
];

$maritalOptions = [
    '' => __('admin.rental.marital_unknown'),
    'celibataire' => __('admin.rental.marital_single'),
    'marie' => __('admin.rental.marital_married'),
    'divorce' => __('admin.rental.marital_divorced'),
    'veuf' => __('admin.rental.marital_widowed'),
];

$rentalDurations = [
    'court' => __('admin.rental.duration.court'),
    'moyen' => __('admin.rental.duration.moyen'),
    'long' => __('admin.rental.duration.long'),
];

$depositMonthOptions = [
    2 => __('admin.rental.deposit_months.2'),
    3 => __('admin.rental.deposit_months.3'),
];
?>
<a href="<?= url('/admin/rentals') ?>" class="admin-listing-back">
    <i class="bi bi-arrow-left" aria-hidden="true"></i>
    <?= e(__('admin.rental.back')) ?>
</a>

<?php if (!empty($error)): ?>
    <div class="admin-alert admin-alert-error" role="alert"><?= e((string) $error) ?></div>
<?php endif; ?>

<form method="post" action="<?= url('/admin/rentals') ?>" enctype="multipart/form-data" class="admin-rental-form">
    <?= csrf_field() ?>

    <div class="admin-rental-form-layout">
        <div class="admin-rental-form-main">
            <section class="admin-rental-section">
                <header class="admin-rental-section-head">
                    <span class="admin-rental-section-num">1</span>
                    <div>
                        <h2><?= e(__('admin.rental.section.identity')) ?></h2>
                        <p><?= e(__('admin.rental.section.identity_lead')) ?></p>
                    </div>
                </header>
                <div class="admin-rental-fields">
                    <label class="admin-rental-field">
                        <span><?= e(__('admin.rental.field.last_name')) ?> *</span>
                        <input type="text" name="tenant_last_name" required autocomplete="family-name">
                    </label>
                    <label class="admin-rental-field">
                        <span><?= e(__('admin.rental.field.first_name')) ?> *</span>
                        <input type="text" name="tenant_first_name" required autocomplete="given-name">
                    </label>
                </div>
            </section>

            <section class="admin-rental-section">
                <header class="admin-rental-section-head">
                    <span class="admin-rental-section-num">2</span>
                    <div>
                        <h2><?= e(__('admin.rental.section.contact')) ?></h2>
                        <p><?= e(__('admin.rental.section.contact_lead')) ?></p>
                    </div>
                </header>
                <div class="admin-rental-fields admin-rental-fields-3">
                    <label class="admin-rental-field admin-rental-field-full">
                        <span><?= e(__('admin.rental.field.property')) ?> *</span>
                        <select name="property_id" required>
                            <option value=""><?= e(__('admin.rental.field.property_placeholder')) ?></option>
                            <?php foreach ($properties as $property): ?>
                                <option value="<?= (int) $property['id'] ?>">
                                    <?= e((string) $property['title']) ?> — <?= e((string) $property['city']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="admin-rental-field">
                        <span><?= e(__('admin.rental.field.phone')) ?></span>
                        <input type="tel" name="tenant_phone" placeholder="+221 …" autocomplete="tel">
                    </label>
                    <label class="admin-rental-field">
                        <span><?= e(__('admin.rental.field.email')) ?></span>
                        <input type="email" name="tenant_email" autocomplete="email">
                    </label>
                    <label class="admin-rental-field">
                        <span><?= e(__('admin.rental.field.start_date')) ?> *</span>
                        <input type="date" name="start_date" required>
                    </label>
                    <label class="admin-rental-field">
                        <span><?= e(__('admin.rental.field.end_date')) ?> *</span>
                        <input type="date" name="end_date" required>
                    </label>
                    <label class="admin-rental-field">
                        <span><?= e(__('admin.rental.field.marital')) ?></span>
                        <select name="marital_status">
                            <?php foreach ($maritalOptions as $value => $label): ?>
                                <option value="<?= e($value) ?>"><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="admin-rental-field">
                        <span><?= e(__('admin.rental.field.contract_type')) ?></span>
                        <select name="contract_type">
                            <?php foreach ($contractTypes as $value => $label): ?>
                                <option value="<?= e($value) ?>"><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
            </section>

            <section class="admin-rental-section">
                <header class="admin-rental-section-head">
                    <span class="admin-rental-section-num">3</span>
                    <div>
                        <h2><?= e(__('admin.rental.section.payment')) ?></h2>
                        <p><?= e(__('admin.rental.section.payment_lead')) ?></p>
                    </div>
                </header>
                <div class="admin-rental-fields admin-rental-fields-3">
                    <label class="admin-rental-field">
                        <span><?= e(__('admin.rental.field.monthly_rent')) ?> *</span>
                        <input type="number" name="monthly_amount" min="1" step="1" required placeholder="<?= e(__('admin.rental.field.monthly_placeholder')) ?>" data-rental-monthly>
                    </label>
                    <label class="admin-rental-field">
                        <span><?= e(__('admin.rental.field.rental_duration')) ?> *</span>
                        <select name="rental_duration" required>
                            <?php foreach ($rentalDurations as $value => $label): ?>
                                <option value="<?= e($value) ?>"<?= $value === 'long' ? ' selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="admin-rental-field">
                        <span><?= e(__('admin.rental.field.deposit_months')) ?> *</span>
                        <select name="deposit_months" required data-rental-deposit-months>
                            <?php foreach ($depositMonthOptions as $value => $label): ?>
                                <option value="<?= (int) $value ?>"<?= $value === 2 ? ' selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="admin-rental-field">
                        <span><?= e(__('admin.rental.field.deposit')) ?></span>
                        <input type="number" name="deposit_amount" min="0" step="1" placeholder="<?= e(__('admin.rental.field.deposit_placeholder')) ?>" data-rental-deposit>
                    </label>
                    <label class="admin-rental-field admin-rental-field-full">
                        <span><?= e(__('admin.rental.field.notes')) ?></span>
                        <textarea name="notes" rows="3" placeholder="<?= e(__('admin.rental.field.notes_placeholder')) ?>"></textarea>
                    </label>
                </div>
            </section>
        </div>

        <aside class="admin-rental-form-side">
            <section class="admin-rental-section">
                <header class="admin-rental-section-head">
                    <span class="admin-rental-section-num">4</span>
                    <div>
                        <h2><?= e(__('admin.rental.section.attachments')) ?></h2>
                        <p><?= e(__('admin.rental.section.attachments_lead')) ?></p>
                    </div>
                </header>

                <div class="admin-rental-upload">
                    <div class="admin-rental-upload-photos" id="rentalPropertyPhotos" data-max="5">
                        <label class="admin-rental-upload-box" data-rental-photos-trigger>
                            <input type="file"
                                   name="property_photos[]"
                                   accept="image/jpeg,image/png,image/webp"
                                   multiple
                                   data-rental-photos-input>
                            <span class="admin-rental-upload-icon" aria-hidden="true"><i class="bi bi-camera"></i></span>
                            <strong><?= e(__('admin.rental.upload.property_photos')) ?></strong>
                            <span><?= e(__('admin.rental.upload.property_photos_hint')) ?></span>
                            <span class="admin-rental-upload-counter" data-rental-photos-counter>0 / 5</span>
                            <span class="admin-rental-upload-btn"><?= e(__('admin.rental.upload.choose')) ?></span>
                        </label>
                        <div class="admin-rental-photos-preview-grid" data-rental-photos-grid hidden></div>
                    </div>

                    <label class="admin-rental-upload-box admin-rental-upload-box-required">
                        <input type="file" name="contract_pdf" accept="application/pdf" required>
                        <span class="admin-rental-upload-icon admin-rental-upload-icon-pdf" aria-hidden="true"><i class="bi bi-file-earmark-pdf"></i></span>
                        <strong><?= e(__('admin.rental.upload.contract')) ?> *</strong>
                        <span><?= e(__('admin.rental.upload.contract_hint')) ?></span>
                        <span class="admin-rental-upload-btn"><?= e(__('admin.rental.upload.choose')) ?></span>
                    </label>
                </div>
            </section>

            <div class="admin-rental-form-foot">
                <p class="admin-rental-required-note">* <?= e(__('admin.rental.required_note')) ?></p>
                <div class="admin-rental-form-actions">
                    <a href="<?= url('/admin/rentals') ?>" class="admin-btn admin-btn-ghost"><?= e(__('admin.construction_cancel')) ?></a>
                    <button type="submit" class="admin-btn admin-btn-rental">
                        <i class="bi bi-check-lg" aria-hidden="true"></i>
                        <?= e(__('admin.rental.save')) ?>
                    </button>
                </div>
            </div>
        </aside>
    </div>
</form>
<script>
(function () {
    var monthlyInput = document.querySelector('[data-rental-monthly]');
    var depositMonthsInput = document.querySelector('[data-rental-deposit-months]');
    var depositInput = document.querySelector('[data-rental-deposit]');

    function syncDepositAmount() {
        if (!monthlyInput || !depositMonthsInput || !depositInput || depositInput.dataset.manual === '1') {
            return;
        }

        var rent = parseFloat(monthlyInput.value) || 0;
        var months = parseInt(depositMonthsInput.value, 10) || 0;
        if (rent > 0 && months > 0) {
            depositInput.value = String(Math.round(rent * months));
        }
    }

    if (depositInput) {
        depositInput.addEventListener('input', function () {
            depositInput.dataset.manual = depositInput.value === '' ? '0' : '1';
        });
    }

    if (monthlyInput) monthlyInput.addEventListener('input', syncDepositAmount);
    if (depositMonthsInput) depositMonthsInput.addEventListener('change', syncDepositAmount);
})();
</script>
<script>
(function () {
    var root = document.getElementById('rentalPropertyPhotos');
    if (!root) return;

    var max = parseInt(root.getAttribute('data-max') || '5', 10);
    var input = root.querySelector('[data-rental-photos-input]');
    var grid = root.querySelector('[data-rental-photos-grid]');
    var counter = root.querySelector('[data-rental-photos-counter]');
    var trigger = root.querySelector('[data-rental-photos-trigger]');
    var files = [];

    function syncInput() {
        if (!input || typeof DataTransfer === 'undefined') return;
        var dt = new DataTransfer();
        files.forEach(function (file) { dt.items.add(file); });
        input.files = dt.files;
    }

    function render() {
        if (!grid || !counter) return;

        grid.innerHTML = '';

        files.forEach(function (file, index) {
            var card = document.createElement('div');
            card.className = 'admin-rental-photos-preview';
            var img = document.createElement('img');
            img.alt = '';
            img.src = URL.createObjectURL(file);
            var remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'admin-rental-photos-remove';
            remove.setAttribute('aria-label', 'Retirer');
            remove.innerHTML = '<i class="bi bi-x-lg" aria-hidden="true"></i>';
            remove.addEventListener('click', function () {
                files.splice(index, 1);
                syncInput();
                render();
            });
            card.appendChild(img);
            card.appendChild(remove);
            grid.appendChild(card);
        });

        counter.textContent = files.length + ' / ' + max;
        grid.hidden = files.length === 0;

        if (trigger) {
            trigger.classList.toggle('is-full', files.length >= max);
        }
    }

    if (!input) return;

    input.addEventListener('change', function () {
        Array.from(input.files || []).forEach(function (file) {
            if (files.length >= max) return;
            var exists = files.some(function (existing) {
                return existing.name === file.name
                    && existing.size === file.size
                    && existing.lastModified === file.lastModified;
            });
            if (!exists) files.push(file);
        });
        input.value = '';
        syncInput();
        render();
    });
})();
</script>
<?php include base_path('views/admin/partials/footer.php'); ?>
