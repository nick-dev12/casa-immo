-- ============================================================
-- Zig Imobilier — Schéma complet MySQL/MariaDB
-- Base : ariaqqrw_casa_immo
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `ariaqqrw_casa_immo`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `ariaqqrw_casa_immo`;

-- ------------------------------------------------------------
-- Utilisateurs & rôles
-- ------------------------------------------------------------

DROP TABLE IF EXISTS `password_reset_tokens`;
DROP TABLE IF EXISTS `user_roles`;
DROP TABLE IF EXISTS `roles`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `first_name`      VARCHAR(100)    NOT NULL,
    `last_name`       VARCHAR(100)    NOT NULL,
    `email`           VARCHAR(191)    NOT NULL,
    `phone`           VARCHAR(30)     DEFAULT NULL,
    `password`        VARCHAR(255)    NOT NULL,
    `avatar`          VARCHAR(255)    DEFAULT NULL,
    `email_verified_at` DATETIME      DEFAULT NULL,
    `status`          ENUM('active','inactive','banned') NOT NULL DEFAULT 'active',
    `last_login_at`   DATETIME        DEFAULT NULL,
    `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`      DATETIME        DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_email_unique` (`email`),
    KEY `users_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `roles` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(50)  NOT NULL,
    `slug`        VARCHAR(50)  NOT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `roles_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_roles` (
    `user_id` BIGINT UNSIGNED NOT NULL,
    `role_id` INT UNSIGNED    NOT NULL,
    PRIMARY KEY (`user_id`, `role_id`),
    KEY `user_roles_role_id_foreign` (`role_id`),
    CONSTRAINT `user_roles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `user_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_reset_tokens` (
    `email`      VARCHAR(191) NOT NULL,
    `token`      VARCHAR(255) NOT NULL,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Logements
-- ------------------------------------------------------------

DROP TABLE IF EXISTS `property_amenities`;
DROP TABLE IF EXISTS `amenities`;
DROP TABLE IF EXISTS `property_availability`;
DROP TABLE IF EXISTS `property_prices`;
DROP TABLE IF EXISTS `property_images`;
DROP TABLE IF EXISTS `properties`;
DROP TABLE IF EXISTS `establishments`;

CREATE TABLE `establishments` (
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

CREATE TABLE `properties` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `owner_id`    BIGINT UNSIGNED NOT NULL,
    `establishment_id` BIGINT UNSIGNED DEFAULT NULL,
    `title`       VARCHAR(255)    NOT NULL,
    `description` TEXT            NOT NULL,
    `type`        ENUM('appartement','studio','villa','maison','chambre','residence','autre') NOT NULL DEFAULT 'appartement',
    `address`     VARCHAR(255)    NOT NULL,
    `city`        VARCHAR(100)    NOT NULL,
    `district`    VARCHAR(100)    DEFAULT NULL,
    `country`     VARCHAR(100)    NOT NULL DEFAULT 'Sénégal',
    `latitude`    DECIMAL(10, 8)  DEFAULT NULL,
    `longitude`   DECIMAL(11, 8)  DEFAULT NULL,
    `area_sqm`    DECIMAL(10, 2)  DEFAULT NULL,
    `bedrooms`    TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `bathrooms`   TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `capacity`    TINYINT UNSIGNED NOT NULL DEFAULT 2,
    `rules`       TEXT            DEFAULT NULL,
    `video_path`  VARCHAR(255)    DEFAULT NULL,
    `status`      ENUM('draft','pending','approved','rejected','suspended') NOT NULL DEFAULT 'draft',
    `currency`    CHAR(3)         NOT NULL DEFAULT 'XOF',
    `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`  DATETIME        DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `properties_owner_id_foreign` (`owner_id`),
    KEY `properties_establishment_id_index` (`establishment_id`),
    KEY `properties_city_index` (`city`),
    KEY `properties_type_index` (`type`),
    KEY `properties_status_index` (`status`),
    KEY `properties_location_index` (`latitude`, `longitude`),
    CONSTRAINT `properties_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `properties_establishment_id_foreign` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `property_prices` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `property_id`     BIGINT UNSIGNED NOT NULL,
    `price_per_night` DECIMAL(12, 2)  DEFAULT NULL,
    `price_per_week`  DECIMAL(12, 2)  DEFAULT NULL,
    `price_per_month` DECIMAL(12, 2)  DEFAULT NULL,
    `currency`        CHAR(3)         NOT NULL DEFAULT 'XOF',
    `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `property_prices_property_id_unique` (`property_id`),
    CONSTRAINT `property_prices_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `property_images` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `property_id` BIGINT UNSIGNED NOT NULL,
    `path`        VARCHAR(255)    NOT NULL,
    `is_primary`  TINYINT(1)      NOT NULL DEFAULT 0,
    `sort_order`  INT UNSIGNED    NOT NULL DEFAULT 0,
    `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `property_images_property_id_index` (`property_id`),
    CONSTRAINT `property_images_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `amenities` (
    `id`       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`     VARCHAR(100) NOT NULL,
    `icon`     VARCHAR(50)  DEFAULT NULL,
    `category` VARCHAR(50)  DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `amenities_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `property_amenities` (
    `property_id` BIGINT UNSIGNED NOT NULL,
    `amenity_id`  INT UNSIGNED    NOT NULL,
    PRIMARY KEY (`property_id`, `amenity_id`),
    KEY `property_amenities_amenity_id_foreign` (`amenity_id`),
    CONSTRAINT `property_amenities_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
    CONSTRAINT `property_amenities_amenity_id_foreign` FOREIGN KEY (`amenity_id`) REFERENCES `amenities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `property_availability` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `property_id` BIGINT UNSIGNED NOT NULL,
    `date`        DATE            NOT NULL,
    `status`      ENUM('available','blocked','reserved','maintenance') NOT NULL DEFAULT 'available',
    `booking_id`  BIGINT UNSIGNED DEFAULT NULL,
    `note`        VARCHAR(255)    DEFAULT NULL,
    `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `property_availability_unique` (`property_id`, `date`),
    KEY `property_availability_date_index` (`date`),
    KEY `property_availability_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Réservations & paiements
-- ------------------------------------------------------------

DROP TABLE IF EXISTS `commissions`;
DROP TABLE IF EXISTS `payment_transactions`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `booking_guests`;
DROP TABLE IF EXISTS `bookings`;

CREATE TABLE `bookings` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `property_id`       BIGINT UNSIGNED NOT NULL,
    `user_id`           BIGINT UNSIGNED NOT NULL,
    `check_in`          DATE            NOT NULL,
    `check_out`         DATE            NOT NULL,
    `guests`            TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `nights`            INT UNSIGNED    NOT NULL,
    `rental_type`       ENUM('nightly','weekly','monthly','long_term') NOT NULL DEFAULT 'nightly',
    `price_per_unit`    DECIMAL(12, 2)  NOT NULL,
    `subtotal`          DECIMAL(12, 2)  NOT NULL,
    `commission_rate`   DECIMAL(5, 2)   NOT NULL DEFAULT 10.00,
    `commission_amount` DECIMAL(12, 2)  NOT NULL DEFAULT 0.00,
    `total_amount`      DECIMAL(12, 2)  NOT NULL,
    `currency`          CHAR(3)         NOT NULL DEFAULT 'XOF',
    `status`            ENUM('pending','confirmed','cancelled','completed','rejected') NOT NULL DEFAULT 'pending',
    `notes`             TEXT            DEFAULT NULL,
    `cancelled_at`      DATETIME        DEFAULT NULL,
    `review_reminder_sent_at` DATETIME  DEFAULT NULL,
    `confirmation_pin`  CHAR(4)         NOT NULL,
    `confirmation_email_sent_at` DATETIME DEFAULT NULL,
    `created_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `bookings_property_id_foreign` (`property_id`),
    KEY `bookings_user_id_foreign` (`user_id`),
    KEY `bookings_dates_index` (`check_in`, `check_out`),
    KEY `bookings_status_index` (`status`),
    CONSTRAINT `bookings_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
    CONSTRAINT `bookings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `property_availability`
    ADD CONSTRAINT `property_availability_booking_id_foreign`
    FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL;

CREATE TABLE `booking_guests` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `booking_id` BIGINT UNSIGNED NOT NULL,
    `first_name` VARCHAR(100)    NOT NULL,
    `last_name`  VARCHAR(100)    NOT NULL,
    `email`      VARCHAR(191)    DEFAULT NULL,
    `phone`      VARCHAR(30)     DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `booking_guests_booking_id_foreign` (`booking_id`),
    CONSTRAINT `booking_guests_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payments` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `booking_id` BIGINT UNSIGNED DEFAULT NULL,
    `user_id`    BIGINT UNSIGNED NOT NULL,
    `amount`     DECIMAL(12, 2)  NOT NULL,
    `currency`   CHAR(3)         NOT NULL DEFAULT 'XOF',
    `gateway`    ENUM('wave','orange_money','card','cash','other') NOT NULL DEFAULT 'wave',
    `status`     ENUM('pending','processing','completed','failed','refunded') NOT NULL DEFAULT 'pending',
    `reference`  VARCHAR(100)    DEFAULT NULL,
    `paid_at`    DATETIME        DEFAULT NULL,
    `created_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `payments_booking_id_foreign` (`booking_id`),
    KEY `payments_user_id_foreign` (`user_id`),
    KEY `payments_status_index` (`status`),
    CONSTRAINT `payments_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL,
    CONSTRAINT `payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payment_transactions` (
    `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `payment_id`       BIGINT UNSIGNED NOT NULL,
    `transaction_ref`  VARCHAR(191)    NOT NULL,
    `gateway_response` JSON            DEFAULT NULL,
    `status`           ENUM('pending','success','failed') NOT NULL DEFAULT 'pending',
    `amount`           DECIMAL(12, 2)  NOT NULL,
    `created_at`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `payment_transactions_payment_id_foreign` (`payment_id`),
    CONSTRAINT `payment_transactions_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `commissions` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `booking_id`      BIGINT UNSIGNED NOT NULL,
    `total_amount`    DECIMAL(12, 2)  NOT NULL,
    `percentage`      DECIMAL(5, 2)   NOT NULL,
    `platform_amount` DECIMAL(12, 2)  NOT NULL,
    `owner_amount`    DECIMAL(12, 2)  NOT NULL,
    `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `commissions_booking_id_unique` (`booking_id`),
    CONSTRAINT `commissions_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Favoris & avis
-- ------------------------------------------------------------

DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `favorites`;

CREATE TABLE `favorites` (
    `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`          BIGINT UNSIGNED NOT NULL,
    `favoritable_type` ENUM('property','land') NOT NULL,
    `favoritable_id`   BIGINT UNSIGNED NOT NULL,
    `created_at`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `favorites_unique` (`user_id`, `favoritable_type`, `favoritable_id`),
    KEY `favorites_user_id_index` (`user_id`),
    CONSTRAINT `favorites_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `reviews` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`     BIGINT UNSIGNED NOT NULL,
    `property_id` BIGINT UNSIGNED NOT NULL,
    `booking_id`  BIGINT UNSIGNED NOT NULL,
    `rating`      TINYINT UNSIGNED NOT NULL,
    `comment`     TEXT            DEFAULT NULL,
    `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `reviews_booking_id_unique` (`booking_id`),
    KEY `reviews_property_id_index` (`property_id`),
    KEY `reviews_user_id_index` (`user_id`),
    CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `reviews_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
    CONSTRAINT `reviews_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
    CONSTRAINT `reviews_rating_check` CHECK (`rating` BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Messagerie & notifications
-- ------------------------------------------------------------

DROP TABLE IF EXISTS `messages`;
DROP TABLE IF EXISTS `conversation_participants`;
DROP TABLE IF EXISTS `conversations`;
DROP TABLE IF EXISTS `notifications`;

CREATE TABLE `conversations` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `subject`     VARCHAR(255)    DEFAULT NULL,
    `property_id` BIGINT UNSIGNED DEFAULT NULL,
    `land_id`     BIGINT UNSIGNED DEFAULT NULL,
    `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `conversations_property_id_index` (`property_id`),
    KEY `conversations_land_id_index` (`land_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `conversation_participants` (
    `conversation_id` BIGINT UNSIGNED NOT NULL,
    `user_id`         BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (`conversation_id`, `user_id`),
    KEY `conversation_participants_user_id_foreign` (`user_id`),
    CONSTRAINT `conversation_participants_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
    CONSTRAINT `conversation_participants_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `messages` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `conversation_id` BIGINT UNSIGNED NOT NULL,
    `sender_id`       BIGINT UNSIGNED NOT NULL,
    `body`            TEXT            NOT NULL,
    `is_read`         TINYINT(1)      NOT NULL DEFAULT 0,
    `read_at`         DATETIME        DEFAULT NULL,
    `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `messages_conversation_id_index` (`conversation_id`),
    KEY `messages_sender_id_index` (`sender_id`),
    CONSTRAINT `messages_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
    CONSTRAINT `messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notifications` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    BIGINT UNSIGNED NOT NULL,
    `type`       VARCHAR(50)     NOT NULL,
    `title`      VARCHAR(255)    NOT NULL,
    `body`       TEXT            DEFAULT NULL,
    `data`       JSON            DEFAULT NULL,
    `is_read`    TINYINT(1)      NOT NULL DEFAULT 0,
    `read_at`    DATETIME        DEFAULT NULL,
    `created_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `notifications_user_id_index` (`user_id`),
    KEY `notifications_is_read_index` (`is_read`),
    CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Terrains
-- ------------------------------------------------------------

DROP TABLE IF EXISTS `land_visits`;
DROP TABLE IF EXISTS `land_documents`;
DROP TABLE IF EXISTS `land_images`;
DROP TABLE IF EXISTS `lands`;

CREATE TABLE `lands` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `seller_id`   BIGINT UNSIGNED NOT NULL,
    `title`       VARCHAR(255)    NOT NULL,
    `description` TEXT            NOT NULL,
    `area`        DECIMAL(12, 2)  NOT NULL,
    `area_unit`   ENUM('m2','ha','are') NOT NULL DEFAULT 'm2',
    `width_m`     DECIMAL(10, 2)  DEFAULT NULL,
    `length_m`    DECIMAL(10, 2)  DEFAULT NULL,
    `price`       DECIMAL(15, 2)  NOT NULL,
    `currency`    CHAR(3)         NOT NULL DEFAULT 'XOF',
    `city`        VARCHAR(100)    NOT NULL,
    `district`    VARCHAR(100)    DEFAULT NULL,
    `address`     VARCHAR(255)    DEFAULT NULL,
    `latitude`    DECIMAL(10, 8)  DEFAULT NULL,
    `longitude`   DECIMAL(11, 8)  DEFAULT NULL,
    `land_type`   ENUM('residentiel','commercial','agricole','industriel','autre') NOT NULL DEFAULT 'residentiel',
    `paper_type`  ENUM('titre_foncier','deliberation','bail_emphyteotique') DEFAULT NULL,
    `status`      ENUM('draft','pending','approved','rejected','suspended','sold') NOT NULL DEFAULT 'draft',
    `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`  DATETIME        DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `lands_seller_id_foreign` (`seller_id`),
    KEY `lands_city_index` (`city`),
    KEY `lands_land_type_index` (`land_type`),
    KEY `lands_paper_type_index` (`paper_type`),
    KEY `lands_status_index` (`status`),
    CONSTRAINT `lands_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `land_images` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `land_id`    BIGINT UNSIGNED NOT NULL,
    `path`       VARCHAR(255)    NOT NULL,
    `is_primary` TINYINT(1)      NOT NULL DEFAULT 0,
    `sort_order` INT UNSIGNED    NOT NULL DEFAULT 0,
    `created_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `land_images_land_id_index` (`land_id`),
    CONSTRAINT `land_images_land_id_foreign` FOREIGN KEY (`land_id`) REFERENCES `lands` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `land_documents` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `land_id`    BIGINT UNSIGNED NOT NULL,
    `name`       VARCHAR(255)    NOT NULL,
    `path`       VARCHAR(255)    NOT NULL,
    `doc_type`   VARCHAR(50)     DEFAULT NULL,
    `created_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `land_documents_land_id_index` (`land_id`),
    CONSTRAINT `land_documents_land_id_foreign` FOREIGN KEY (`land_id`) REFERENCES `lands` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `land_visits` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `land_id`         BIGINT UNSIGNED NOT NULL,
    `user_id`         BIGINT UNSIGNED NOT NULL,
    `requested_date`  DATE            NOT NULL,
    `requested_time`  TIME            DEFAULT NULL,
    `message`         TEXT            DEFAULT NULL,
    `status`          ENUM('pending','accepted','rejected','rescheduled','completed','cancelled') NOT NULL DEFAULT 'pending',
    `proposed_date`   DATE            DEFAULT NULL,
    `proposed_time`   TIME            DEFAULT NULL,
    `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `land_visits_land_id_foreign` (`land_id`),
    KEY `land_visits_user_id_foreign` (`user_id`),
    CONSTRAINT `land_visits_land_id_foreign` FOREIGN KEY (`land_id`) REFERENCES `lands` (`id`) ON DELETE CASCADE,
    CONSTRAINT `land_visits_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Contrats longue durée
-- ------------------------------------------------------------

DROP TABLE IF EXISTS `contract_payments`;
DROP TABLE IF EXISTS `contracts`;

CREATE TABLE `contracts` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `property_id`     BIGINT UNSIGNED NOT NULL,
    `owner_id`        BIGINT UNSIGNED NOT NULL,
    `tenant_id`       BIGINT UNSIGNED NOT NULL,
    `booking_id`      BIGINT UNSIGNED DEFAULT NULL,
    `start_date`      DATE            NOT NULL,
    `end_date`        DATE            NOT NULL,
    `monthly_amount`  DECIMAL(12, 2)  NOT NULL,
    `deposit_amount`  DECIMAL(12, 2)  NOT NULL DEFAULT 0.00,
    `deposit_months`  TINYINT UNSIGNED NOT NULL DEFAULT 2,
    `terms`           TEXT            DEFAULT NULL,
    `contract_type`   ENUM('habitation', 'commercial', 'saisonnier', 'autre') NOT NULL DEFAULT 'habitation',
    `rental_duration` ENUM('court', 'moyen', 'long') NOT NULL DEFAULT 'long',
    `status`          ENUM('draft','active','terminated','expired') NOT NULL DEFAULT 'draft',
    `pdf_path`        VARCHAR(255)    DEFAULT NULL,
    `tenant_photo_path` VARCHAR(255)  DEFAULT NULL,
    `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `contracts_property_id_foreign` (`property_id`),
    KEY `contracts_owner_id_foreign` (`owner_id`),
    KEY `contracts_tenant_id_foreign` (`tenant_id`),
    CONSTRAINT `contracts_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
    CONSTRAINT `contracts_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `contracts_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `contracts_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `contract_images` (
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

CREATE TABLE `contract_payments` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `contract_id` BIGINT UNSIGNED NOT NULL,
    `due_date`    DATE            NOT NULL,
    `amount`      DECIMAL(12, 2)  NOT NULL,
    `status`      ENUM('pending','paid','overdue','cancelled') NOT NULL DEFAULT 'pending',
    `paid_at`     DATETIME        DEFAULT NULL,
    `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `contract_payments_contract_id_foreign` (`contract_id`),
    KEY `contract_payments_due_date_index` (`due_date`),
    CONSTRAINT `contract_payments_contract_id_foreign` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Administration
-- ------------------------------------------------------------

DROP TABLE IF EXISTS `moderation_actions`;
DROP TABLE IF EXISTS `reports`;
DROP TABLE IF EXISTS `settings`;

CREATE TABLE `reports` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `reporter_id`     BIGINT UNSIGNED NOT NULL,
    `reportable_type` ENUM('property','land','user','review','message') NOT NULL,
    `reportable_id`   BIGINT UNSIGNED NOT NULL,
    `reason`          VARCHAR(100)    NOT NULL,
    `description`     TEXT            DEFAULT NULL,
    `status`          ENUM('pending','reviewed','resolved','dismissed') NOT NULL DEFAULT 'pending',
    `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `reports_reporter_id_foreign` (`reporter_id`),
    KEY `reports_status_index` (`status`),
    CONSTRAINT `reports_reporter_id_foreign` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `moderation_actions` (
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

CREATE TABLE `settings` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL,
    `value`       TEXT         NOT NULL,
    `type`        ENUM('string','integer','float','boolean','json') NOT NULL DEFAULT 'string',
    `description` VARCHAR(255) DEFAULT NULL,
    `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `settings_key_unique` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
