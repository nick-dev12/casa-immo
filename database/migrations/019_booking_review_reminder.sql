ALTER TABLE `bookings`
    ADD COLUMN `review_reminder_sent_at` DATETIME DEFAULT NULL AFTER `cancelled_at`,
    ADD KEY `bookings_review_reminder_index` (`status`, `check_out`, `review_reminder_sent_at`);
