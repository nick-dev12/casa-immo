<?php
/** @var array<string, mixed> $establishment */
/** @var array<int, array<string, mixed>> $properties */
/** @var array<int, array<string, mixed>> $lands */

$activeNav = 'properties';
include base_path('views/host/partials/header.php');

$statusLabels = [
    'draft' => __('host.status.draft'),
    'pending' => __('host.status.pending'),
    'approved' => __('host.status.approved'),
    'rejected' => __('host.status.rejected'),
    'suspended' => __('host.status.suspended'),
    'sold' => __('host.status.sold'),
];

$lands = $lands ?? [];
$allListings = array_merge($properties, $lands);
$totalListings = count($allListings);
$publishedCount = 0;
$draftCount = 0;
foreach ($allListings as $listing) {
    $status = (string) ($listing['status'] ?? 'draft');
    if ($status === 'approved') {
        $publishedCount++;
    }
    if ($status === 'draft') {
        $draftCount++;
    }
}

$publicHomeUrl = url('/');
$hasListings = $properties !== [] || $lands !== [];
?>
<?php
$heroLabel = __('host.listings_hero_label');
$heroValue = (string) $totalListings;
$heroBadge = __('host.dashboard_verified');
$heroActions = [
    ['href' => url('/host/properties/new'), 'label' => __('host.add_listing'), 'icon' => 'plus-lg', 'ghost' => true],
    ['href' => $publicHomeUrl, 'label' => __('host.dashboard_view_shop'), 'icon' => 'shop-window'],
];
include base_path('views/host/partials/admin-hero.php');
?>

<div class="host-dash-stats host-dash-stats-compact">
    <article class="host-dash-stat">
        <span class="host-dash-stat-icon host-dash-stat-icon-blue" aria-hidden="true"><i class="bi bi-houses-fill"></i></span>
        <div>
            <p class="host-dash-stat-label"><?= e(__('host.stat_listings_total')) ?></p>
            <p class="host-dash-stat-value"><?= $totalListings ?></p>
        </div>
    </article>
    <article class="host-dash-stat">
        <span class="host-dash-stat-icon host-dash-stat-icon-orange" aria-hidden="true"><i class="bi bi-check-circle-fill"></i></span>
        <div>
            <p class="host-dash-stat-label"><?= e(__('host.stat_listings_published')) ?></p>
            <p class="host-dash-stat-value"><?= $publishedCount ?></p>
        </div>
    </article>
    <article class="host-dash-stat">
        <span class="host-dash-stat-icon host-dash-stat-icon-yellow" aria-hidden="true"><i class="bi bi-pencil-square"></i></span>
        <div>
            <p class="host-dash-stat-label"><?= e(__('host.status.draft')) ?></p>
            <p class="host-dash-stat-value"><?= $draftCount ?></p>
        </div>
    </article>
    <article class="host-dash-stat">
        <span class="host-dash-stat-icon host-dash-stat-icon-green" aria-hidden="true"><i class="bi bi-building"></i></span>
        <div>
            <p class="host-dash-stat-label"><?= e(__('host.group.logements')) ?></p>
            <p class="host-dash-stat-value"><?= count($properties) ?></p>
        </div>
    </article>
</div>

<section class="host-panel host-dash-panel host-listings-panel">
    <?php if (!$hasListings): ?>
        <div class="host-empty host-empty-compact">
            <i class="bi bi-house-door"></i>
            <p><?= e(__('host.no_listings')) ?></p>
            <a href="<?= url('/host/properties/new') ?>" class="host-btn host-btn-primary"><?= e(__('host.add_first_listing')) ?></a>
        </div>
    <?php else: ?>
        <?php if ($properties !== []): ?>
            <h3 class="host-list-group-title"><?= e(__('host.group.logements')) ?></h3>
            <div class="host-listing-grid">
                <?php foreach ($properties as $property): ?>
                    <?php
                    $listing = $property;
                    $kind = 'logement';
                    include base_path('views/host/partials/listing-card.php');
                    ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($lands !== []): ?>
            <h3 class="host-list-group-title"><?= e(__('host.group.terrains')) ?></h3>
            <div class="host-listing-grid">
                <?php foreach ($lands as $land): ?>
                    <?php
                    $listing = $land;
                    $kind = 'terrain';
                    include base_path('views/host/partials/listing-card.php');
                    ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>
<?php include base_path('views/host/partials/footer.php'); ?>
