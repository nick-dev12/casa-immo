<?php
/** @var list<array<string, string>> $emergencyServices */
/** @var string $emergencyCity */
/** @var bool $embedded */

$embedded = !empty($embedded);

$primaryServices = array_values(array_filter(
    $emergencyServices,
    static fn (array $service): bool => in_array($service['key'] ?? '', ['police', 'fire', 'samu'], true)
));
$secondaryServices = array_values(array_filter(
    $emergencyServices,
    static fn (array $service): bool => !in_array($service['key'] ?? '', ['police', 'fire', 'samu'], true)
));
?>
<div class="reservation-emergency<?= $embedded ? ' reservation-emergency-embedded' : '' ?>"
     <?= $embedded ? '' : 'aria-labelledby="reservation-emergency-title"' ?>>
    <?php if (!$embedded): ?>
        <div class="reservation-emergency-head">
            <div class="reservation-emergency-badge" aria-hidden="true">
                <i class="bi bi-exclamation-octagon-fill"></i>
            </div>
            <div>
                <h2 id="reservation-emergency-title" class="reservation-emergency-title">
                    <?= e(__('reservations.detail.emergency')) ?>
                </h2>
                <p class="reservation-emergency-lead">
                    <?= e(__('reservations.emergency.lead', ['city' => $emergencyCity !== '' ? $emergencyCity : 'Ziguinchor'])) ?>
                </p>
            </div>
        </div>
    <?php else: ?>
        <p class="reservation-emergency-lead reservation-emergency-lead-embedded">
            <?= e(__('reservations.emergency.lead', ['city' => $emergencyCity !== '' ? $emergencyCity : 'Ziguinchor'])) ?>
        </p>
    <?php endif; ?>

    <div class="reservation-emergency-primary">
        <?php foreach ($primaryServices as $service): ?>
            <a href="tel:<?= e((string) $service['tel']) ?>"
               class="reservation-emergency-card reservation-emergency-card-<?= e((string) $service['tone']) ?>">
                <span class="reservation-emergency-card-icon" aria-hidden="true">
                    <i class="bi <?= e((string) $service['icon']) ?>"></i>
                </span>
                <span class="reservation-emergency-card-body">
                    <span class="reservation-emergency-card-label"><?= e((string) $service['label']) ?></span>
                    <span class="reservation-emergency-card-number"><?= e((string) $service['number']) ?></span>
                    <span class="reservation-emergency-card-desc"><?= e((string) $service['description']) ?></span>
                </span>
                <span class="reservation-emergency-card-action" aria-hidden="true">
                    <i class="bi bi-telephone-fill"></i>
                </span>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if ($secondaryServices !== []): ?>
        <div class="reservation-emergency-secondary">
            <h3 class="reservation-emergency-subtitle"><?= e(__('reservations.emergency.local_contacts')) ?></h3>
            <div class="reservation-emergency-secondary-grid">
                <?php foreach ($secondaryServices as $service): ?>
                    <a href="tel:<?= e((string) $service['tel']) ?>"
                       class="reservation-emergency-mini reservation-emergency-mini-<?= e((string) $service['tone']) ?>">
                        <span class="reservation-emergency-mini-icon" aria-hidden="true">
                            <i class="bi <?= e((string) $service['icon']) ?>"></i>
                        </span>
                        <span class="reservation-emergency-mini-body">
                            <strong><?= e((string) $service['label']) ?></strong>
                            <span><?= e((string) $service['number']) ?></span>
                            <small><?= e((string) $service['description']) ?></small>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <p class="reservation-emergency-note">
        <i class="bi bi-info-circle" aria-hidden="true"></i>
        <?= e(__('reservations.emergency.note')) ?>
    </p>
</div>
