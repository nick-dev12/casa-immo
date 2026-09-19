-- Équipement : Salon meublé (catégorie Cuisine & Repas)
USE `ariaqqrw_casa_immo`;

INSERT INTO `amenities` (`name`, `icon`, `category`) VALUES
('Salon meublé', 'sofa', 'cuisine')
ON DUPLICATE KEY UPDATE icon = VALUES(icon), category = VALUES(category);
