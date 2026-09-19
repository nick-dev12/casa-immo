-- Journal des actions de modération (super admin)
CREATE TABLE IF NOT EXISTS `moderation_actions` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `admin_id`    BIGINT UNSIGNED NOT NULL,
    `target_type` ENUM('property', 'land') NOT NULL,
    `target_id`   BIGINT UNSIGNED NOT NULL,
    `action`      ENUM('warning', 'suspend', 'reject', 'restore') NOT NULL,
    `motives`     JSON            NOT NULL,
    `notes`       TEXT            DEFAULT NULL,
    `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `moderation_actions_target_index` (`target_type`, `target_id`),
    KEY `moderation_actions_admin_id_index` (`admin_id`),
    CONSTRAINT `moderation_actions_admin_id_foreign`
        FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
