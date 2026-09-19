<?php
/** @var array<string, mixed> $property */
/** @var float $priceNight */
/** @var string $currency */
/** @var list<string> $unavailableDates */

$propertyId = (int) $property['id'];
$capacity = max(1, (int) ($property['capacity'] ?? 1));
$oldCheckIn = (string) old('check_in', '');
$oldCheckOut = (string) old('check_out', '');
$oldGuests = (int) old('guests', 1);
$bookingError = flash('booking_error');
$unavailableDates = array_values($unavailableDates ?? []);

$calendarConfig = [
    'unavailable' => $unavailableDates,
    'checkIn' => $oldCheckIn,
    'checkOut' => $oldCheckOut,
    'labels' => [
        'reserved' => __('booking.calendar.reserved'),
        'available' => __('booking.calendar.available'),
        'checkIn' => __('booking.calendar.check_in'),
        'checkOut' => __('booking.calendar.check_out'),
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
<form class="pd-booking-form booking-form"
      action="<?= url('/properties/' . $propertyId . '/book') ?>"
      method="post">
    <?= csrf_field() ?>

    <?php if ($bookingError): ?>
        <p class="pd-booking-error" role="alert"><?= e((string) $bookingError) ?></p>
    <?php endif; ?>

    <div class="pd-booking-calendar"
         data-config="<?= e(json_encode($calendarConfig, JSON_UNESCAPED_UNICODE)) ?>">
        <div class="pd-booking-calendar-toolbar">
            <button type="button" class="pd-booking-cal-nav" data-action="prev-month" aria-label="Mois précédent">
                <i class="bi bi-chevron-left" aria-hidden="true"></i>
            </button>
            <h4 class="pd-booking-cal-month" data-month-label></h4>
            <button type="button" class="pd-booking-cal-nav" data-action="next-month" aria-label="Mois suivant">
                <i class="bi bi-chevron-right" aria-hidden="true"></i>
            </button>
        </div>
        <div class="pd-booking-cal-weekdays" data-weekdays></div>
        <div class="pd-booking-cal-grid" data-grid role="grid" aria-label="Calendrier de réservation"></div>
        <div class="pd-booking-cal-legend">
            <span class="pd-booking-cal-legend-item">
                <i class="pd-booking-cal-dot pd-booking-cal-dot-available"></i><?= e(__('booking.calendar.available')) ?>
            </span>
            <span class="pd-booking-cal-legend-item">
                <i class="pd-booking-cal-dot pd-booking-cal-dot-reserved"></i><?= e(__('booking.calendar.reserved')) ?>
            </span>
        </div>
    </div>

    <div class="pd-dates pd-dates-hidden">
        <label>
            <span><?= e(__('booking.calendar.check_in')) ?></span>
            <input type="date"
                   name="check_in"
                   value="<?= e($oldCheckIn) ?>"
                   data-check-in
                   required>
        </label>
        <label>
            <span><?= e(__('booking.calendar.check_out')) ?></span>
            <input type="date"
                   name="check_out"
                   value="<?= e($oldCheckOut) ?>"
                   data-check-out
                   required>
        </label>
    </div>
    <label class="pd-guests">
        <span>Voyageurs</span>
        <select name="guests">
            <?php for ($g = 1; $g <= $capacity; $g++): ?>
                <option value="<?= $g ?>"<?= $oldGuests === $g ? ' selected' : '' ?>>
                    <?= $g ?> adulte<?= $g > 1 ? 's' : '' ?>
                </option>
            <?php endfor; ?>
        </select>
    </label>
    <button type="submit" class="pd-btn-book pd-btn-book-full">Réserver</button>
    <p class="pd-booking-note">Vous ne serez pas débité maintenant</p>
</form>
