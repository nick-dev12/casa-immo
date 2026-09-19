<?php
/** @var array<string, mixed> $destination */
/** @var array<int, array<string, mixed>> $properties */
/** @var array<string, string> $filters */
/** @var array<string, string> $propertyTypes */
/** @var int $total */
/** @var int $page */
/** @var int $totalPages */
/** @var array<int, array<string, mixed>> $lands */

$heroImage = asset($destination['hero_image']);
$city = (string) $destination['city'];
?>

<section class="dest-hero">
    <div class="dest-hero-bg">
        <img src="<?= e($heroImage) ?>" alt="<?= e($city) ?>" fetchpriority="high">
        <div class="dest-hero-overlay"></div>
    </div>

    <div class="dest-hero-inner container app-container">
        <header class="dest-topbar">
            <a href="<?= url('/') ?>" class="dest-back" aria-label="<?= e(__('dest.back_home')) ?>">
                <i class="bi bi-arrow-left"></i>
            </a>
            <?php
            $gtranslateClass = 'gt-lang--header';
            include base_path('views/partials/gtranslate.php');
            ?>
        </header>

        <div class="dest-hero-content">
            <p class="dest-region"><i class="bi bi-geo-alt"></i> <?= e($destination['region']) ?></p>
            <h1><?= e($city) ?></h1>
            <p class="dest-tagline"><?= e($destination['tagline']) ?></p>
        </div>

        <form method="get" action="<?= url('/properties') ?>" class="dest-search" id="destSearchForm">
            <input type="hidden" name="city" value="<?= e($city) ?>">

            <div class="dest-search-fields">
                <div class="dest-search-field dest-search-field-grow">
                    <i class="bi bi-search"></i>
                    <input type="search"
                           name="q"
                           placeholder="<?= e(__('dest.search_placeholder')) ?>"
                           value="<?= e($filters['q']) ?>"
                           aria-label="<?= e(__('dest.search')) ?>">
                </div>
                <div class="dest-search-field">
                    <i class="bi bi-house"></i>
                    <select name="type" aria-label="<?= e(__('plist.property_type')) ?>">
                        <option value=""><?= e(__('dest.all_types')) ?></option>
                        <?php foreach ($propertyTypes as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= $filters['type'] === $key ? 'selected' : '' ?>>
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="dest-search-btn">
                    <i class="bi bi-search d-md-none"></i>
                    <span class="d-none d-md-inline"><?= e(__('dest.search')) ?></span>
                </button>
            </div>

            <?php if (!empty($destination['filters'])): ?>
            <div class="dest-type-chips" role="group" aria-label="<?= e(__('dest.filter_by_type')) ?>">
                <?php foreach ($destination['filters'] as $chip): ?>
                    <a href="<?= url('/properties?' . http_build_query(array_merge($filters, ['type' => $chip['type'], 'page' => 1]))) ?>"
                       class="dest-chip<?= ($filters['type'] ?? '') === $chip['type'] ? ' active' : '' ?>">
                        <?= e($chip['label']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </form>
    </div>
</section>

<section class="dest-stats">
    <div class="container app-container">
        <div class="dest-stats-grid">
            <?php foreach ($destination['stats'] as $stat): ?>
                <div class="dest-stat">
                    <span class="dest-stat-icon"><i class="bi <?= e($stat['icon']) ?>"></i></span>
                    <div>
                        <strong><?= e($stat['value']) ?></strong>
                        <span><?= e($stat['label']) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="dest-about">
    <div class="container app-container">
        <div class="dest-about-bar">
            <p class="dest-about-lead"><?= e($destination['booking_lead'] ?? $destination['tagline']) ?></p>
            <?php if (!empty($destination['quick_facts'])): ?>
            <div class="dest-quick-facts">
                <?php foreach ($destination['quick_facts'] as $fact): ?>
                    <span class="dest-quick-fact">
                        <i class="bi <?= e($fact['icon']) ?>"></i>
                        <?= e($fact['label']) ?>
                    </span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <a href="#logements" class="dest-about-cta">
                <?= e($total > 1
                    ? __('dest.view_properties_plural', ['count' => (string) $total])
                    : __('dest.view_properties_singular', ['count' => (string) $total])) ?>
                <i class="bi bi-arrow-down"></i>
            </a>
        </div>
    </div>
</section>

<section class="dest-listings" id="logements">
    <div class="container app-container">
        <header class="dest-section-head">
            <div>
                <h2><?= e(__('dest.properties_in', ['city' => $city])) ?></h2>
                <p>
                    <?= (int) $total ?>
                    <?= $total > 1 ? e(__('dest.listings_plural')) : e(__('dest.listings_singular')) ?>
                </p>
            </div>
        </header>

        <?php if (empty($properties)): ?>
            <div class="dest-empty">
                <div class="dest-empty-icon"><i class="bi bi-house-heart"></i></div>
                <h3><?= e(__('dest.no_properties')) ?></h3>
                <p><?= e(__('dest.no_properties_hint')) ?></p>
                <a href="<?= url('/') ?>" class="dest-btn-outline"><?= e(__('dest.discover')) ?></a>
            </div>
        <?php else: ?>
            <div class="dest-properties-grid">
                <?php foreach ($properties as $item): ?>
                    <?php $cardCompact = true; $cardShowCity = false; include base_path('views/partials/property-card.php'); ?>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav class="dest-pagination" aria-label="<?= e(__('plist.pagination')) ?>">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a class="dest-page-btn<?= $i === $page ? ' active' : '' ?>"
                           href="<?= url('/properties?' . http_build_query(array_merge($filters, ['page' => $i]))) ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php if (!empty($lands)): ?>
<section class="dest-lands">
    <div class="container app-container">
        <header class="dest-section-head">
            <div>
                <h2><?= e(__('dest.lands_in', ['city' => $city])) ?></h2>
                <p><?= e(translated($destination['lands_lead'] ?? __('dest.lands_lead'))) ?></p>
            </div>
            <a href="<?= url('/lands?' . http_build_query(['city' => $city])) ?>" class="dest-link"><?= e(__('dest.view_all')) ?></a>
        </header>
        <div class="dest-lands-scroll">
            <?php foreach ($lands as $item): ?>
                <?php include base_path('views/partials/land-card.php'); ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="dest-cta">
    <div class="container app-container">
        <div class="dest-cta-inner">
            <h2><?= e(__('dest.ready', ['city' => $city])) ?></h2>
            <p><?= e(translated($destination['cta_text'] ?? __('dest.cta_default'))) ?></p>
            <div class="dest-cta-actions">
                <a href="#logements" class="dest-btn-primary"><?= e(__('dest.view_properties_btn')) ?></a>
                <a href="<?= url('/') ?>" class="dest-btn-ghost"><?= e(__('dest.other_destinations')) ?></a>
            </div>
        </div>
    </div>
</section>
