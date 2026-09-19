<?php
/**
 * Bandeau hero admin (style dashboard).
 *
 * @var string $heroLabel
 * @var string $heroValue
 * @var string|null $heroBadge
 * @var list<array{href: string, label: string, icon?: string, ghost?: bool, external?: bool}> $heroActions
 * @var string|null $heroAriaLabel
 */

$heroLabel = $heroLabel ?? '';
$heroValue = $heroValue ?? '';
$heroBadge = $heroBadge ?? null;
$heroActions = $heroActions ?? [];
$heroAriaLabel = $heroAriaLabel ?? $heroLabel;
?>
<section class="host-dash-hero" aria-label="<?= e($heroAriaLabel) ?>">
    <div class="host-dash-hero-body">
        <?php if ($heroLabel !== ''): ?>
            <p class="host-dash-hero-label"><?= e($heroLabel) ?></p>
        <?php endif; ?>
        <?php if ($heroValue !== ''): ?>
            <p class="host-dash-hero-value"><?= e($heroValue) ?></p>
        <?php endif; ?>
        <?php if ($heroActions !== []): ?>
            <div class="host-dash-hero-actions">
                <?php foreach ($heroActions as $action): ?>
                    <a href="<?= e($action['href']) ?>"
                       class="host-dash-hero-btn<?= !empty($action['ghost']) ? ' host-dash-hero-btn-ghost' : '' ?>"
                       <?php if (!empty($action['external'])): ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>>
                        <?php if (!empty($action['icon'])): ?>
                            <i class="bi bi-<?= e($action['icon']) ?>" aria-hidden="true"></i>
                        <?php endif; ?>
                        <?= e($action['label']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php if ($heroBadge !== null && $heroBadge !== ''): ?>
        <div class="host-dash-hero-badge" aria-hidden="true">
            <i class="bi bi-shield-check"></i>
            <span><?= e($heroBadge) ?></span>
        </div>
    <?php endif; ?>
</section>
