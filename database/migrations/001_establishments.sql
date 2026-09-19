-- Établissements hôtes (exécuter une fois sur ariaqqrw_casa_immo)

USE `ariaqqrw_casa_immo`;

CREATE TABLE IF NOT EXISTS `establishments` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `owner_id`    BIGINT UNSIGNED NOT NULL,
    `name`        VARCHAR(255)    NOT NULL,
    `description` TEXT            DEFAULT NULL,
    `city`        VARCHAR(100)    NOT NULL,
    `district`    VARCHAR(100)    DEFAULT NULL,
    `address`     VARCHAR(255)    DEFAULT NULL,
    `phone`       VARCHAR(30)     DEFAULT NULL,
    `status`      ENUM('draft','active','suspended') NOT NULL DEFAULT 'active',
    `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `establishments_owner_id_unique` (`owner_id`),
    KEY `establishments_city_index` (`city`),
    CONSTRAINT `establishments_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `properties`
    ADD COLUMN IF NOT EXISTS `establishment_id` BIGINT UNSIGNED DEFAULT NULL AFTER `owner_id`,
    ADD KEY IF NOT EXISTS `properties_establishment_id_index` (`establishment_id`);
