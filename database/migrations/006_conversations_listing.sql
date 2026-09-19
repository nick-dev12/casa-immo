-- Lie les conversations à un logement ou un terrain (messagerie client ↔ vendeur)

ALTER TABLE `conversations`
    ADD COLUMN `property_id` BIGINT UNSIGNED DEFAULT NULL AFTER `subject`,
    ADD COLUMN `land_id` BIGINT UNSIGNED DEFAULT NULL AFTER `property_id`;

ALTER TABLE `conversations`
    ADD KEY `conversations_property_id_index` (`property_id`),
    ADD KEY `conversations_land_id_index` (`land_id`);

ALTER TABLE `conversations`
    ADD CONSTRAINT `conversations_property_id_foreign`
        FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL,
    ADD CONSTRAINT `conversations_land_id_foreign`
        FOREIGN KEY (`land_id`) REFERENCES `lands` (`id`) ON DELETE SET NULL;

UPDATE `conversations` c
INNER JOIN `properties` p ON p.id = 1
SET c.`property_id` = 1
WHERE c.`id` = 1 AND c.`property_id` IS NULL;

UPDATE `conversations` c
INNER JOIN `lands` l ON l.id = 1
SET c.`land_id` = 1
WHERE c.`id` = 2 AND c.`land_id` IS NULL;
