<?php
/** @var array<string, string> $filters */
/** @var array<int, array<string, mixed>> $properties */
/** @var array<string, string> $propertyTypes */
/** @var array<int, string> $cities */
/** @var array<int, array{city: string, image: string, tagline: string}> $cityGrid */
/** @var int $total */
/** @var int $page */
/** @var int $totalPages */

$heroImage = \App\Helpers\DestinationHelper::image('Ziguinchor');
$hasDateFilter = ($filters['check_in'] ?? '') !== '' && ($filters['check_out'] ?? '') !== '';
$activeFilters = array_filter([
    'q' => trim($filters['q'] ?? ''),
    'city' => $filters['city'] ?? '',
    'type' => $filters['type'] ?? '',
    'district' => $filters['district'] ?? '',
], static fn (string $value): bool => $value !== '');

$buildUrl = static function (array $overrides = []) use ($filters): string {
    $query = array_merge($filters, $overrides, ['page' => 1]);
    $query = array_filter($query, static fn ($value): bool => $value !== '' && $value !== null);

    return url('/properties?' . http_build_query($query));
};

$filterCities = \App\Helpers\DestinationHelper::cityFilterOptions($cities);
?>

<section class="plist-hero">
    <div class="plist-hero-bg">
        <img src="<?= e($heroImage) ?>" alt="Casamance" fetchpriority="high">
        <div class="plist-hero-overlay"></div>
    </div>

    <div class="plist-hero-inner container app-container">
        <header class="plist-topbar">
            <a href="<?= url('/') ?>" class="plist-back" aria-label="<?= e(__('plist.back_home')) ?>">
                <i class="bi bi-arrow-left"></i>
            </a>
            <?php
            $gtranslateClass = 'gt-lang--header';
            include base_path('views/partials/gtranslate.php');
            ?>
        </header>

        <div class="plist-hero-content">
            <p class="plist-region"><i class="bi bi-geo-alt"></i> <?= e(__('plist.region')) ?></p>
            <h1><?= e(__('plist.title')) ?></h1>
            <p class="plist-lead">
                <?= (int) $total ?>
                <?= $total > 1 ? e(__('plist.listings')) : e(__('plist.listing')) ?>
                <?= $hasDateFilter
                    ? e(__('plist.lead_dates', [
                        'from' => format_date($filters['check_in'], 'd/m/Y'),
                        'to' => format_date($filters['check_out'], 'd/m/Y'),
                    ]))
                    : ' ' . ($total > 1 ? e(__('plist.available_plural')) : e(__('plist.available'))) ?>
            </p>
        </div>
    </div>
</section>

<section class="plist-filters-wrap" data-plist-filters>
    <div class="container app-container">
        <form method="get" action="<?= url('/properties') ?>" class="plist-search" id="plistSearchForm">
            <?php foreach (['check_in', 'check_out', 'district', 'guests', 'adults', 'children', 'rooms', 'pets', 'business', 'price_min', 'price_max', 'bedrooms'] as $hiddenKey): ?>
                <?php if (($filters[$hiddenKey] ?? '') !== ''): ?>
                    <input type="hidden" name="<?= e($hiddenKey) ?>" value="<?= e($filters[$hiddenKey]) ?>">
                <?php endif; ?>
            <?php endforeach; ?>

            <div class="plist-search-fields">
                <div class="plist-search-field plist-search-field-grow">
                    <i class="bi bi-search"></i>
                    <input type="search"
                           name="q"
                           placeholder="<?= e(__('plist.search_placeholder')) ?>"
                           value="<?= e($filters['q']) ?>"
                           aria-label="<?= e(__('plist.search')) ?>">
                </div>
                <div class="plist-search-field">
                    <i class="bi bi-geo-alt"></i>
                    <select name="city" aria-label="<?= e(__('plist.city')) ?>">
                        <option value=""><?= e(__('plist.all_cities')) ?></option>
                        <?php foreach ($filterCities as $city): ?>
                            <option value="<?= e($city) ?>" <?= ($filters['city'] ?? '') === $city ? 'selected' : '' ?>>
                                <?= e($city) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="plist-search-field">
                    <i class="bi bi-house"></i>
                    <select name="type" aria-label="<?= e(__('plist.property_type')) ?>">
                        <option value=""><?= e(__('plist.all_types')) ?></option>
                        <?php foreach ($propertyTypes as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= ($filters['type'] ?? '') === $key ? 'selected' : '' ?>>
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="plist-search-btn">
                    <i class="bi bi-search d-md-none"></i>
                    <span class="d-none d-md-inline"><?= e(__('plist.search')) ?></span>
                </button>
            </div>

            <div class="plist-type-chips" role="group" aria-label="<?= e(__('dest.filter_by_type')) ?>">
                <a href="<?= e($buildUrl(['type' => ''])) ?>"
                   class="plist-chip<?= ($filters['type'] ?? '') === '' ? ' active' : '' ?>"><?= e(__('plist.filter_all')) ?></a>
                <?php foreach ($propertyTypes as $key => $label): ?>
                    <a href="<?= e($buildUrl(['type' => $key])) ?>"
                       class="plist-chip<?= ($filters['type'] ?? '') === $key ? ' active' : '' ?>">
                        <?= e($label) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </form>
    </div>
</section>

<section class="plist-cities" id="propertiesCityScroll" data-plist-cities>
    <div class="container app-container">
        <header class="plist-section-head">
            <h2><?= e(__('plist.explore_cities')) ?></h2>
            <p><?= e(__('plist.explore_subtitle')) ?></p>
        </header>
        <div class="plist-city-scroll">
            <?php foreach ($cityGrid as $dest): ?>
                <a href="<?= e($buildUrl(['city' => $dest['city'], 'q' => ''])) ?>"
                   class="plist-city-card<?= ($filters['city'] ?? '') === $dest['city'] ? ' active' : '' ?>">
                    <img src="<?= e($dest['image']) ?>" alt="<?= e($dest['city']) ?>" loading="lazy">
                    <div class="plist-city-card-body">
                        <strong><?= e($dest['city']) ?></strong>
                        <span><?= e($dest['tagline']) ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="plist-listings">
    <div class="container app-container">
        <header class="plist-results-head">
            <div>
                <h2>
                    <?php if (($filters['city'] ?? '') !== ''): ?>
                        <?= e(__('plist.in_city', ['city' => $filters['city']])) ?>
                    <?php else: ?>
                        <?= e(__('plist.all_properties')) ?>
                    <?php endif; ?>
                </h2>
                <p><?= (int) $total ?> <?= $total > 1 ? e(__('plist.results_plural')) : e(__('plist.results_singular')) ?></p>
            </div>
            <?php if ($activeFilters !== []): ?>
                <a href="<?= url('/properties') ?>" class="plist-reset"><?= e(__('plist.clear_filters')) ?></a>
            <?php endif; ?>
        </header>

        <?php if ($activeFilters !== []): ?>
            <div class="plist-active-filters">
                <?php if (($filters['q'] ?? '') !== ''): ?>
                    <a href="<?= e($buildUrl(['q' => ''])) ?>" class="plist-filter-tag">
                        « <?= e($filters['q']) ?> » <i class="bi bi-x"></i>
                    </a>
                <?php endif; ?>
                <?php if (($filters['city'] ?? '') !== ''): ?>
                    <a href="<?= e($buildUrl(['city' => ''])) ?>" class="plist-filter-tag">
                        <?= e($filters['city']) ?> <i class="bi bi-x"></i>
                    </a>
                <?php endif; ?>
                <?php if (($filters['district'] ?? '') !== ''): ?>
                    <a href="<?= e($buildUrl(['district' => ''])) ?>" class="plist-filter-tag">
                        <?= e($filters['district']) ?> <i class="bi bi-x"></i>
                    </a>
                <?php endif; ?>
                <?php if (($filters['type'] ?? '') !== ''): ?>
                    <a href="<?= e($buildUrl(['type' => ''])) ?>" class="plist-filter-tag">
                        <?= e($propertyTypes[$filters['type']] ?? $filters['type']) ?> <i class="bi bi-x"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($properties)): ?>
            <div class="plist-empty">
                <div class="plist-empty-icon"><i class="bi bi-house-heart"></i></div>
                <h3><?= e(__('plist.no_results')) ?></h3>
                <p>
                    <?php if ($hasDateFilter): ?>
                        <?= e(__('plist.no_results_dates', [
                            'from' => format_date($filters['check_in'], 'd/m/Y'),
                            'to' => format_date($filters['check_out'], 'd/m/Y'),
                        ])) ?>
                    <?php else: ?>
                        <?= e(__('plist.no_results_hint')) ?>
                    <?php endif; ?>
                </p>
                <a href="<?= url('/properties') ?>" class="plist-btn-outline"><?= e(__('plist.view_all_properties')) ?></a>
            </div>
        <?php else: ?>
            <div class="plist-grid">
                <?php foreach ($properties as $item): ?>
                    <?php include base_path('views/partials/property-card.php'); ?>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav class="plist-pagination" aria-label="<?= e(__('plist.pagination')) ?>">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a class="plist-page-btn<?= $i === $page ? ' active' : '' ?>"
                           href="<?= url('/properties?' . http_build_query(array_merge($filters, ['page' => $i]))) ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
