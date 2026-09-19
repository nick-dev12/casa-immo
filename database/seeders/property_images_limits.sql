-- Galeries logements : minimum 4 photos, maximum 10 par annonce
USE `ariaqqrw_casa_immo`;

-- Villa Cap Skirring (id 2) : compléter à 4 photos
INSERT IGNORE INTO `property_images` (`property_id`, `path`, `is_primary`, `sort_order`) VALUES
(2, 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&h=600&fit=crop', 0, 2),
(2, 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800&h=600&fit=crop', 0, 3);

-- Studio Ziguinchor (id 3)
INSERT IGNORE INTO `property_images` (`property_id`, `path`, `is_primary`, `sort_order`) VALUES
(3, 'https://images.unsplash.com/photo-1560448204-e02f11c45751?w=800&h=600&fit=crop', 0, 1),
(3, 'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=800&h=600&fit=crop', 0, 2),
(3, 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800&h=600&fit=crop', 0, 3);

-- Maison Bignona (id 4)
INSERT IGNORE INTO `property_images` (`property_id`, `path`, `is_primary`, `sort_order`) VALUES
(4, 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&h=600&fit=crop', 0, 1),
(4, 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800&h=600&fit=crop', 0, 2),
(4, 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800&h=600&fit=crop', 0, 3);

-- Chambre Oussouye (id 5)
INSERT IGNORE INTO `property_images` (`property_id`, `path`, `is_primary`, `sort_order`) VALUES
(5, 'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?w=800&h=600&fit=crop', 0, 1),
(5, 'https://images.unsplash.com/photo-1560448204-e02f11c45751?w=800&h=600&fit=crop', 0, 2),
(5, 'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=800&h=600&fit=crop', 0, 3);

-- Supprimer les photos au-delà de 10 par logement
DELETE pi FROM `property_images` pi
INNER JOIN (
    SELECT id FROM (
        SELECT id,
               ROW_NUMBER() OVER (PARTITION BY property_id ORDER BY is_primary DESC, sort_order ASC) AS rn
        FROM property_images
    ) ranked
    WHERE rn > 10
) excess ON pi.id = excess.id;
