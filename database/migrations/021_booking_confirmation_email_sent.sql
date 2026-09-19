ALTER TABLE `bookings`
    ADD COLUMN `confirmation_email_sent_at` DATETIME DEFAULT NULL AFTER `confirmation_pin`,
    ADD KEY `bookings_confirmation_email_index` (`status`, `confirmation_email_sent_at`);

UPDATE `bookings`
SET `confirmation_email_sent_at` = `created_at`
WHERE `confirmation_email_sent_at` IS NULL;
