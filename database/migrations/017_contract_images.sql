CREATE TABLE IF NOT EXISTS `contract_images` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `contract_id` BIGINT UNSIGNED NOT NULL,
    `path`        VARCHAR(255)    NOT NULL,
    `is_primary`  TINYINT(1)      NOT NULL DEFAULT 0,
    `sort_order`  INT UNSIGNED    NOT NULL DEFAULT 0,
    `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `contract_images_contract_id_index` (`contract_id`),
    CONSTRAINT `contract_images_contract_id_foreign` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
