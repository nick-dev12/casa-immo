<?php
/** @var array<string, mixed> $establishment */
/** @var array<string, mixed> $property */
/** @var list<string> $blockedDates */
/** @var list<string> $reservedDates */

$activeNav = 'properties';
$showBack = true;
include base_path('views/host/partials/header.php');

$propertyId = (int) $property['id'];
$calendarConfig = [
    'blocked' => array_values($blockedDates ?? []),
    'reserved' => array_values($reservedDates ?? []),
    'postUrl' => url('/host/properties/' . $propertyId . '/availability'),
    'csrf' => csrf_token(),
    'labels' => [
        'prev' => __('host.availability_prev_month'),
        'next' => __('host.availability_next_month'),
        'block' => __('host.availability_block_selected'),
        'legendAvailable' => __('host.availability_legend_available'),
        'legendBlocked' => __('host.availability_legend_blocked'),
        'legendReserved' => __('host.availability_legend_reserved'),
        'legendSelected' => __('host.availability_legend_selected'),
    ],
    'weekdays' => [
        __('host.availability.week.mon'),
        __('host.availability.week.tue'),
        __('host.availability.week.wed'),
        __('host.availability.week.thu'),
        __('host.availability.week.fri'),
        __('host.availability.week.sat'),
        __('host.availability.week.sun'),
    ],
    'months' => [
        __('host.availability.month.jan'),
        __('host.availability.month.feb'),
        __('host.availability.month.mar'),
        __('host.availability.month.apr'),
        __('host.availability.month.may'),
        __('host.availability.month.jun'),
        __('host.availability.month.jul'),
        __('host.availability.month.aug'),
        __('host.availability.month.sep'),
        __('host.availability.month.oct'),
        __('host.availability.month.nov'),
        __('host.availability.month.dec'),
    ],
];
?>
<?php
$heroLabel = __('host.field.city');
$heroValue = (string) ($property['city'] ?? '');
$heroBadge = null;
$heroActions = [
    ['href' => url('/host/properties/' . $propertyId . '/edit'), 'label' => __('host.edit'), 'icon' => 'pencil', 'ghost' => true],
    ['href' => url('/host/properties'), 'label' => __('host.listings_title'), 'icon' => 'houses-fill', 'ghost' => true],
];
include base_path('views/host/partials/admin-hero.php');
?>

<div class="host-panel host-dash-panel host-avail-panel">
    <?php if (!empty($success)): ?>
        <div class="host-flash host-flash-success" role="status"><?= e((string) $success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="host-flash host-flash-error" role="alert"><?= e((string) $error) ?></div>
    <?php endif; ?>

    <div class="host-avail-calendar"
         id="hostAvailabilityCalendar"
         data-config="<?= e(json_encode($calendarConfig, JSON_UNESCAPED_UNICODE)) ?>">
        <div class="host-avail-calendar-toolbar">
            <button type="button" class="host-avail-nav-btn" data-action="prev-month" aria-label="<?= e(__('host.availability_prev_month')) ?>">
                <i class="bi bi-chevron-left" aria-hidden="true"></i>
            </button>
            <h3 class="host-avail-month-label" data-month-label></h3>
            <button type="button" class="host-avail-nav-btn" data-action="next-month" aria-label="<?= e(__('host.availability_next_month')) ?>">
                <i class="bi bi-chevron-right" aria-hidden="true"></i>
            </button>
        </div>

        <div class="host-avail-weekdays" data-weekdays></div>
        <div class="host-avail-grid" data-grid role="grid" aria-label="<?= e(__('host.availability_title')) ?>"></div>

        <div class="host-avail-legend">
            <span class="host-avail-legend-item"><i class="host-avail-dot host-avail-dot-available"></i><?= e(__('host.availability_legend_available')) ?></span>
            <span class="host-avail-legend-item"><i class="host-avail-dot host-avail-dot-selected"></i><?= e(__('host.availability_legend_selected')) ?></span>
            <span class="host-avail-legend-item"><i class="host-avail-dot host-avail-dot-blocked"></i><?= e(__('host.availability_legend_blocked')) ?></span>
            <span class="host-avail-legend-item"><i class="host-avail-dot host-avail-dot-reserved"></i><?= e(__('host.availability_legend_reserved')) ?></span>
        </div>

        <form method="post" action="<?= url('/host/properties/' . $propertyId . '/availability') ?>" class="host-avail-block-form" id="hostAvailBlockForm">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="block_dates">
            <div data-dates-inputs></div>
            <button type="submit" class="host-btn host-btn-primary host-btn-block" disabled data-block-btn>
                <?= e(__('host.availability_block_selected')) ?>
            </button>
        </form>

        <form method="post" action="<?= url('/host/properties/' . $propertyId . '/availability') ?>" class="host-avail-unblock-form" id="hostAvailUnblockForm" hidden>
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="unblock">
            <input type="hidden" name="date" value="" data-unblock-date>
        </form>
    </div>
</div>

<script src="<?= asset('js/host-availability.js') ?>" defer></script>
<?php include base_path('views/host/partials/footer.php'); ?>
