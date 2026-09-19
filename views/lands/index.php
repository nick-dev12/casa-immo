<?php
/** @var array<string, string> $filters */
/** @var array<int, array<string, mixed>> $lands */
/** @var array<string, string> $landTypes */
/** @var array<int, string> $cities */
/** @var array<int, array{city: string, image: string, tagline: string}> $cityGrid */
/** @var int $total */
/** @var int $page */
/** @var int $totalPages */

$heroImage = \App\Helpers\DestinationHelper::image(
    ($filters['city'] ?? '') !== '' ? (string) $filters['city'] : 'Ziguinchor'
);

$activeFilters = array_filter([
    'q' => trim($filters['q'] ?? ''),
    'city' => $filters['city'] ?? '',
    'type' => $filters['type'] ?? '',
], static fn (string $value): bool => $value !== '');

$buildUrl = static function (array $overrides = []) use ($filters): string {
    $query = array_merge($filters, $overrides, ['page' => 1]);
    $query = array_filter($query, static fn ($value): bool => $value !== '' && $value !== null);

    return url('/lands?' . http_build_query($query));
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
            <h1><?= e(__('lands.title')) ?></h1>
            <p class="plist-lead">
                <?= (int) $total ?>
                <?= $total > 1 ? e(__('lands.found_plural')) : e(__('lands.found_singular')) ?>
                <?= $total > 1 ? e(__('llist.available_plural')) : e(__('llist.available')) ?>
            </p>
        </div>
    </div>
</section>

<section class="plist-filters-wrap" data-plist-filters>
    <div class="container app-container">
        <form method="get" action="<?= url('/lands') ?>" class="plist-search" id="landsListSearchForm">
            <div class="plist-search-fields">
                <div class="plist-search-field plist-search-field-grow">
                    <i class="bi bi-search"></i>
                    <input type="search"
                           name="q"
                           placeholder="<?= e(__('llist.search_placeholder')) ?>"
                           value="<?= e($filters['q']) ?>"
                           aria-label="<?= e(__('lands.search')) ?>">
                </div>
                <div class="plist-search-field">
                    <i class="bi bi-geo-alt"></i>
                    <select name="city" aria-label="<?= e(__('plist.city')) ?>">
                        <option value=""><?= e(__('lands.all_cities')) ?></option>
                        <?php foreach ($filterCities as $city): ?>
                            <option value="<?= e($city) ?>" <?= ($filters['city'] ?? '') === $city ? 'selected' : '' ?>>
                                <?= e($city) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="plist-search-field">
                    <i class="bi bi-map"></i>
                    <select name="type" aria-label="<?= e(__('search.lands.type')) ?>">
                        <option value=""><?= e(__('lands.all_types')) ?></option>
                        <?php foreach ($landTypes as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= ($filters['type'] ?? '') === $key ? 'selected' : '' ?>>
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="plist-search-btn">
                    <i class="bi bi-search d-md-none"></i>
                    <span class="d-none d-md-inline"><?= e(__('lands.filter')) ?></span>
                </button>
            </div>

            <div class="plist-type-chips" role="group" aria-label="<?= e(__('dest.filter_by_type')) ?>">
                <a href="<?= e($buildUrl(['type' => ''])) ?>"
                   class="plist-chip<?= ($filters['type'] ?? '') === '' ? ' active' : '' ?>"><?= e(__('plist.filter_all')) ?></a>
                <?php foreach ($landTypes as $key => $label): ?>
                    <a href="<?= e($buildUrl(['type' => $key])) ?>"
                       class="plist-chip<?= ($filters['type'] ?? '') === $key ? ' active' : '' ?>">
                        <?= e($label) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </form>
    </div>
</section>

<section class="plist-cities" id="landsCityScroll" data-plist-cities>
    <div class="container app-container">
        <header class="plist-section-head">
            <h2><?= e(__('llist.explore_cities')) ?></h2>
            <p><?= e(__('llist.explore_subtitle')) ?></p>
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
                        <?= e(__('llist.all_lands')) ?>
                    <?php endif; ?>
                </h2>
                <p><?= (int) $total ?> <?= $total > 1 ? e(__('plist.results_plural')) : e(__('plist.results_singular')) ?></p>
            </div>
            <?php if ($activeFilters !== []): ?>
                <a href="<?= url('/lands') ?>" class="plist-reset"><?= e(__('plist.clear_filters')) ?></a>
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
                <?php if (($filters['type'] ?? '') !== ''): ?>
                    <a href="<?= e($buildUrl(['type' => ''])) ?>" class="plist-filter-tag">
                        <?= e($landTypes[$filters['type']] ?? $filters['type']) ?> <i class="bi bi-x"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($lands)): ?>
            <div class="plist-empty">
                <div class="plist-empty-icon"><i class="bi bi-map"></i></div>
                <h3><?= e(__('llist.no_results')) ?></h3>
                <p><?= e(__('llist.no_results_hint')) ?></p>
                <a href="<?= url('/lands') ?>" class="plist-btn-outline"><?= e(__('llist.view_all')) ?></a>
            </div>
        <?php else: ?>
            <div class="plist-grid">
                <?php foreach ($lands as $item): ?>
                    <?php $cardCompact = true; include base_path('views/partials/land-card.php'); ?>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav class="plist-pagination" aria-label="<?= e(__('plist.pagination')) ?>">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a class="plist-page-btn<?= $i === $page ? ' active' : '' ?>"
                           href="<?= url('/lands?' . http_build_query(array_merge($filters, ['page' => $i]))) ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
