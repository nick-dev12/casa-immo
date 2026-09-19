<?php
/** @var string $planMode live|static */
/** @var float|null $planLength */
/** @var float|null $planWidth */
/** @var float|null $planArea */
/** @var string $planUnit */

$planMode = $planMode ?? 'live';
$planLength = isset($planLength) ? (float) $planLength : 0.0;
$planWidth = isset($planWidth) ? (float) $planWidth : 0.0;
$planArea = isset($planArea) ? (float) $planArea : 0.0;
$planUnit = $planUnit ?? 'm2';

$titleKey = $planMode === 'live' ? 'host.land_plan.title' : 'land.plan.title';
$emptyKey = $planMode === 'live' ? 'host.land_plan.empty' : 'land.plan.empty';
$hasDimensions = $planLength > 0 && $planWidth > 0;
$showEmpty = $planMode === 'live' || !$hasDimensions;
$showSvg = $planMode === 'static' && $hasDimensions;
?>
<div class="land-plan-preview"
     data-land-plan="<?= e($planMode) ?>"
     data-empty-text="<?= e(__($emptyKey)) ?>"
     <?php if ($planMode === 'static' && $hasDimensions): ?>
     data-length="<?= e((string) $planLength) ?>"
     data-width="<?= e((string) $planWidth) ?>"
     data-area="<?= e((string) $planArea) ?>"
     data-unit="<?= e($planUnit) ?>"
     <?php endif; ?>>
    <h3 class="land-plan-title"><?= e(__($titleKey)) ?></h3>
    <div class="land-plan-stage">
        <p class="land-plan-empty"<?= $showEmpty ? '' : ' hidden' ?>><?= e(__($emptyKey)) ?></p>
        <svg class="land-plan-svg"
             xmlns="http://www.w3.org/2000/svg"
             role="img"
             aria-label="<?= e(__($titleKey)) ?>"
             <?= $showSvg ? '' : ' hidden' ?>></svg>
    </div>
</div>
