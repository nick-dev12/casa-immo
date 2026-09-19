<?php
/** @var string $emergencyCity */
/** @var list<array<string, string>> $emergencyServices */

$emergencyCity = $emergencyCity ?? (string) config('app', 'default_city', 'Ziguinchor');
$emergencyServices = $emergencyServices ?? array_values(array_filter(
    booking_local_emergency_services($emergencyCity),
    static fn (array $service): bool => in_array($service['key'] ?? '', ['police', 'gendarmerie', 'fire'], true)
));
?>
<section class="help-emergency" aria-labelledby="help-emergency-title">
    <h2 id="help-emergency-title" class="help-emergency-title"><?= e(__('help.emergency_title')) ?></h2>
    <p class="help-emergency-lead"><?= e(__('reservations.emergency.lead', ['city' => $emergencyCity])) ?></p>

    <div class="help-emergency-grid">
        <?php foreach ($emergencyServices as $service): ?>
            <a href="tel:<?= e((string) $service['tel']) ?>"
               class="help-emergency-card help-emergency-card-<?= e((string) $service['tone']) ?>">
                <span class="help-emergency-icon" aria-hidden="true">
                    <i class="bi <?= e((string) $service['icon']) ?>"></i>
                </span>
                <span class="help-emergency-body">
                    <span class="help-emergency-label"><?= e((string) $service['label']) ?></span>
                    <span class="help-emergency-number"><?= e((string) $service['number']) ?></span>
                    <span class="help-emergency-desc"><?= e((string) $service['description']) ?></span>
                </span>
            </a>
        <?php endforeach; ?>
    </div>

    <p class="help-emergency-note"><?= e(__('reservations.emergency.note')) ?></p>
</section>
