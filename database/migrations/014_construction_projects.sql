-- Projets de construction (chantiers, devis, livraisons)
USE `ariaqqrw_casa_immo`;

CREATE TABLE IF NOT EXISTS `construction_projects` (
    `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `owner_id`         BIGINT UNSIGNED NOT NULL,
    `establishment_id` BIGINT UNSIGNED DEFAULT NULL,
    `property_id`      BIGINT UNSIGNED DEFAULT NULL,
    `title`            VARCHAR(255)    NOT NULL,
    `description`      TEXT            DEFAULT NULL,
    `project_type`     ENUM('maison_fini', 'maison_finition', 'villa', 'immeuble', 'autre') NOT NULL DEFAULT 'maison_finition',
    `status`           ENUM('devis', 'planning', 'chantier', 'livraison', 'termine', 'annule') NOT NULL DEFAULT 'devis',
    `city`             VARCHAR(100)    NOT NULL,
    `district`         VARCHAR(100)    DEFAULT NULL,
    `address`          VARCHAR(255)    DEFAULT NULL,
    `quote_amount`     DECIMAL(15, 2)  DEFAULT NULL,
    `currency`         CHAR(3)         NOT NULL DEFAULT 'XOF',
    `start_date`       DATE            DEFAULT NULL,
    `delivery_date`    DATE            DEFAULT NULL,
    `progress`         TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `client_name`      VARCHAR(150)    DEFAULT NULL,
    `client_phone`     VARCHAR(30)     DEFAULT NULL,
    `notes`            TEXT            DEFAULT NULL,
    `created_at`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `construction_projects_owner_id_index` (`owner_id`),
    KEY `construction_projects_status_index` (`status`),
    KEY `construction_projects_property_id_index` (`property_id`),
    CONSTRAINT `construction_projects_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `construction_projects_establishment_id_foreign` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`id`) ON DELETE SET NULL,
    CONSTRAINT `construction_projects_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `construction_milestones` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id`   BIGINT UNSIGNED NOT NULL,
    `title`        VARCHAR(255)    NOT NULL,
    `due_date`     DATE            DEFAULT NULL,
    `completed_at` DATE            DEFAULT NULL,
    `status`       ENUM('pending', 'in_progress', 'done') NOT NULL DEFAULT 'pending',
    `sort_order`   INT UNSIGNED    NOT NULL DEFAULT 0,
    `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `construction_milestones_project_id_index` (`project_id`),
    CONSTRAINT `construction_milestones_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `construction_projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
