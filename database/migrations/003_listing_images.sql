-- Tables photos annonces (création si absentes)
CREATE TABLE IF NOT EXISTS `property_images` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `property_id` bigint(20) unsigned NOT NULL,
    `path` varchar(255) NOT NULL,
    `is_primary` tinyint(1) NOT NULL DEFAULT 0,
    `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
    `created_at` datetime NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `property_images_property_id_index` (`property_id`),
    CONSTRAINT `property_images_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `land_images` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `land_id` bigint(20) unsigned NOT NULL,
    `path` varchar(255) NOT NULL,
    `is_primary` tinyint(1) NOT NULL DEFAULT 0,
    `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
    `created_at` datetime NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `land_images_land_id_index` (`land_id`),
    CONSTRAINT `land_images_land_id_foreign` FOREIGN KEY (`land_id`) REFERENCES `lands` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
