ALTER TABLE `bookings`
    ADD COLUMN `confirmation_pin` CHAR(4) DEFAULT NULL AFTER `review_reminder_sent_at`;

UPDATE `bookings`
SET `confirmation_pin` = LPAD(MOD(`id` * 7919 + 1000, 10000), 4, '0')
WHERE `confirmation_pin` IS NULL;

ALTER TABLE `bookings`
    MODIFY COLUMN `confirmation_pin` CHAR(4) NOT NULL;
