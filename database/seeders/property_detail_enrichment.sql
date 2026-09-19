-- Enrichissement données fiche logement (équipements, photos)
USE `ariaqqrw_casa_immo`;

INSERT INTO `amenities` (`name`, `icon`, `category`) VALUES
('Petit déjeuner inclus', 'cup-hot', 'services'),
('Salle de bain privée', 'droplet-half', 'confort'),
('Toilettes privatives', 'door-closed', 'confort'),
('Linge de maison', 'layers', 'confort'),
('Vue mer', 'water', 'exterieur'),
('Salon meublé', 'sofa', 'cuisine')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT IGNORE INTO `property_amenities` (`property_id`, `amenity_id`)
SELECT 1, id FROM amenities WHERE name IN (
    'Petit déjeuner inclus', 'Salle de bain privée', 'Toilettes privatives', 'Linge de maison', 'Vue mer'
);

INSERT IGNORE INTO `property_amenities` (`property_id`, `amenity_id`)
SELECT 2, id FROM amenities WHERE name IN ('Petit déjeuner inclus', 'Piscine', 'Parking');

UPDATE `property_images` SET `path` = 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=1200&h=800&fit=crop' WHERE `property_id` = 1 AND `sort_order` = 0;
UPDATE `property_images` SET `path` = 'https://images.unsplash.com/photo-1560448204-e02f11c45751?w=800&h=600&fit=crop' WHERE `property_id` = 1 AND `sort_order` = 1;

INSERT IGNORE INTO `property_images` (`property_id`, `path`, `is_primary`, `sort_order`) VALUES
(1, 'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=800&h=600&fit=crop', 0, 2),
(1, 'https://images.unsplash.com/photo-1556912173-46c336c7fd55?w=800&h=600&fit=crop', 0, 3),
(1, 'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?w=800&h=600&fit=crop', 0, 4),
(1, 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800&h=600&fit=crop', 0, 5);

UPDATE `property_images` SET `path` = 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=1200&h=800&fit=crop' WHERE `property_id` = 2 AND `sort_order` = 0;
UPDATE `property_images` SET `path` = 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800&h=600&fit=crop' WHERE `property_id` = 2 AND `sort_order` = 1;

UPDATE `property_images` SET `path` = 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=1200&h=800&fit=crop' WHERE `property_id` = 3 AND `sort_order` = 0;
UPDATE `property_images` SET `path` = 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=1200&h=800&fit=crop' WHERE `property_id` = 4 AND `sort_order` = 0;
UPDATE `property_images` SET `path` = 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=1200&h=800&fit=crop' WHERE `property_id` = 5 AND `sort_order` = 0;
