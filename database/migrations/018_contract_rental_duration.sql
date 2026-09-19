-- Durée de location et nombre de mois de caution
ALTER TABLE `contracts`
    ADD COLUMN `rental_duration` ENUM('court', 'moyen', 'long') NOT NULL DEFAULT 'long' AFTER `contract_type`,
    ADD COLUMN `deposit_months` TINYINT UNSIGNED NOT NULL DEFAULT 2 AFTER `deposit_amount`;
