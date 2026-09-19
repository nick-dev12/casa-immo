<?php
/** @var list<array<string, mixed>> $options */
/** @var string|null $emptyMessage */
?>
<ul class="reservation-detail-option-list">
    <?php if ($options === []): ?>
        <li class="reservation-detail-option-empty">
            <?= e($emptyMessage ?? __('reservations.detail.options_empty')) ?>
        </li>
    <?php else: ?>
        <?php foreach ($options as $option): ?>
            <?php
            $type = (string) ($option['type'] ?? 'link');
            $icon = (string) ($option['icon'] ?? 'bi-circle');
            $label = (string) ($option['label'] ?? '');
            $danger = !empty($option['danger']);
            $optionClass = 'reservation-detail-option' . ($danger ? ' reservation-detail-option-danger' : '');
            $panelKey = (string) ($option['panel'] ?? '');
            $panelId = 'reservation-option-panel-' . (string) ($option['key'] ?? 'item');
            ?>
            <li<?= $type === 'panel' ? ' class="reservation-detail-option-panel-item"' : '' ?>>
                <?php if ($type === 'form'): ?>
                    <form method="post"
                          action="<?= e((string) ($option['form_action'] ?? '')) ?>"
                          class="reservation-detail-cancel-form"
                          data-confirm="<?= e((string) ($option['confirm'] ?? '')) ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="<?= e($optionClass) ?>">
                            <i class="bi <?= e($icon) ?>" aria-hidden="true"></i>
                            <?= e($label) ?>
                        </button>
                    </form>
                <?php elseif ($type === 'share'): ?>
                    <button type="button"
                            class="<?= e($optionClass) ?>"
                            data-share="<?= e((string) ($option['share_title'] ?? $label)) ?>"
                            data-share-url="<?= e((string) ($option['url'] ?? '')) ?>">
                        <i class="bi <?= e($icon) ?>" aria-hidden="true"></i>
                        <?= e($label) ?>
                    </button>
                <?php elseif ($type === 'action'): ?>
                    <button type="button"
                            class="<?= e($optionClass) ?>"
                            data-action="<?= e((string) ($option['action'] ?? '')) ?>">
                        <i class="bi <?= e($icon) ?>" aria-hidden="true"></i>
                        <?= e($label) ?>
                    </button>
                <?php elseif ($type === 'panel'): ?>
                    <button type="button"
                            class="<?= e($optionClass) ?> reservation-detail-option-toggle"
                            aria-expanded="false"
                            aria-controls="<?= e($panelId) ?>">
                        <i class="bi <?= e($icon) ?>" aria-hidden="true"></i>
                        <span class="reservation-detail-option-label"><?= e($label) ?></span>
                        <i class="bi bi-chevron-down reservation-detail-option-chevron" aria-hidden="true"></i>
                    </button>
                    <div id="<?= e($panelId) ?>"
                         class="reservation-detail-option-panel"
                         hidden>
                        <?php if ($panelKey === 'emergency'): ?>
                            <?php
                            $emergencyCity = (string) ($option['city'] ?? 'Ziguinchor');
                            $emergencyServices = booking_local_emergency_services($emergencyCity);
                            $embedded = true;
                            include base_path('views/partials/reservation-emergency-services.php');
                            ?>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <a href="<?= e((string) ($option['url'] ?? '#')) ?>"
                       class="<?= e($optionClass) ?>"
                       <?php if (!empty($option['external'])): ?>
                           target="_blank" rel="noopener noreferrer"
                       <?php endif; ?>>
                        <i class="bi <?= e($icon) ?>" aria-hidden="true"></i>
                        <?= e($label) ?>
                    </a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    <?php endif; ?>
</ul>
