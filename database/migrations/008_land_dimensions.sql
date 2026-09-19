ALTER TABLE `lands`
    ADD COLUMN `width_m` DECIMAL(10, 2) DEFAULT NULL AFTER `area_unit`,
    ADD COLUMN `length_m` DECIMAL(10, 2) DEFAULT NULL AFTER `width_m`;
