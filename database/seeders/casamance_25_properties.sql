-- 25 logements Casamance — 4 par ville (+ 1 à Diouloulou)
-- Exécuter après demo_data.sql et casamance_update.sql
USE `ariaqqrw_casa_immo`;

-- ============================================================
-- Nouveaux logements (id 6–25)
-- ============================================================

INSERT INTO `properties`
    (`id`, `owner_id`, `title`, `description`, `type`, `address`, `city`, `district`, `country`, `latitude`, `longitude`, `area_sqm`, `bedrooms`, `bathrooms`, `capacity`, `rules`, `status`, `currency`)
VALUES
-- Ziguinchor (2 de plus → 4 total avec id 1 et 3)
(6, 4, 'Appartement lumineux Nagor',
 'Bel appartement meublé dans le lotissement Nagor, calme et sécurisé. Proche écoles et marchés.',
 'appartement', 'Lotissement Nagor', 'Ziguinchor', 'Nagor', 'Sénégal', 12.59200000, -16.25800000, 72.00, 2, 1, 4,
 'Non fumeur. Animaux sur demande.', 'approved', 'XOF'),
(7, 2, 'Résidence Santhiaba',
 'Appartement spacieux au cœur de Santhiaba, quartier vivant de Ziguinchor. Idéal familles.',
 'appartement', 'Rue de Santhiaba', 'Ziguinchor', 'Santhiaba', 'Sénégal', 12.58100000, -16.26800000, 95.00, 3, 2, 6,
 'Check-in 15h. Check-out 11h.', 'approved', 'XOF'),

-- Cap Skirring (3 de plus → 4 total avec id 2)
(8, 2, 'Villa vue océan Kabrousse',
 'Villa avec terrasse panoramique à Kabrousse, à quelques minutes de la plage.',
 'villa', 'Route de Kabrousse', 'Cap Skirring', 'Kabrousse', 'Sénégal', 12.38000000, -16.76000000, 200.00, 3, 2, 6,
 'Fêtes interdites. Caution exigée.', 'approved', 'XOF'),
(9, 4, 'Appartement bord de mer',
 'Appartement rénové face à l''océan, parfait pour un séjour balnéaire.',
 'appartement', 'Avenue de la Plage', 'Cap Skirring', 'Bord de mer', 'Sénégal', 12.39800000, -16.74500000, 65.00, 2, 1, 4,
 'Non fumeur.', 'approved', 'XOF'),
(10, 2, 'Bungalow tropical',
 'Bungalow indépendant dans un jardin tropical, accès piscine partagée.',
 'maison', 'Boucott-Diamaguène', 'Cap Skirring', 'Boucott-Diamaguène', 'Sénégal', 12.40000000, -16.75200000, 110.00, 2, 1, 4,
 'Respect du voisinage.', 'approved', 'XOF'),

-- Oussouye (3 de plus → 4 total avec id 5)
(11, 4, 'Maison traditionnelle Diembéreng',
 'Maison diola authentique avec cour intérieure, immersion culturelle garantie.',
 'maison', 'Diembéreng', 'Oussouye', 'Diembéreng', 'Sénégal', 12.47500000, -16.54500000, 140.00, 3, 2, 5,
 'Visite de la forêt sacrée à proximité.', 'approved', 'XOF'),
(12, 2, 'Studio Oussouye centre',
 'Studio meublé proche du marché et de la forêt du fromager sacré.',
 'studio', 'Route principale', 'Oussouye', 'Centre', 'Sénégal', 12.48400000, -16.54900000, 32.00, 1, 1, 2,
 'Calme après 22h.', 'approved', 'XOF'),
(13, 4, 'Villa rizières Mlomp',
 'Villa entourée de rizières, cadre verdoyant et paisible.',
 'villa', 'Mlomp', 'Oussouye', 'Mlomp', 'Sénégal', 12.47000000, -16.52000000, 180.00, 4, 2, 8,
 'Voiture recommandée.', 'approved', 'XOF'),

-- Bignona (3 de plus → 4 total avec id 4)
(14, 2, 'Appartement Escale',
 'Appartement neuf dans le quartier Escale, proche route nationale.',
 'appartement', 'Quartier Escale', 'Bignona', 'Escale', 'Sénégal', 12.81200000, -16.22800000, 78.00, 2, 1, 4,
 'Location mensuelle possible.', 'approved', 'XOF'),
(15, 4, 'Maison avec cour Bignona',
 'Maison familiale avec grande cour, idéale longue durée.',
 'maison', 'Zone résidentielle', 'Bignona', 'Zone résidentielle', 'Sénégal', 12.81600000, -16.23200000, 160.00, 3, 2, 6,
 'Animaux acceptés.', 'approved', 'XOF'),
(16, 2, 'Studio Diouloulou route',
 'Studio pratique sur l''axe Bignona–Diouloulou, commerces à proximité.',
 'studio', 'Route de Diouloulou', 'Bignona', 'Diouloulou', 'Sénégal', 12.80500000, -16.24000000, 38.00, 1, 1, 2,
 'Idéal solo ou couple.', 'approved', 'XOF'),

-- Sedhiou (4)
(17, 4, 'Maison fleuve Casamance',
 'Maison confortable avec vue sur le fleuve, terrasse ombragée.',
 'maison', 'Bords du fleuve', 'Sedhiou', 'Centre-ville', 'Sénégal', 12.70800000, -15.55700000, 150.00, 3, 2, 6,
 'Moustiquaires fournies.', 'approved', 'XOF'),
(18, 2, 'Appartement Sedhiou centre',
 'Appartement meublé en centre-ville, marché et gare à pied.',
 'appartement', 'Avenue du Fleuve', 'Sedhiou', 'Centre-ville', 'Sénégal', 12.71000000, -15.55500000, 70.00, 2, 1, 4,
 'Non fumeur.', 'approved', 'XOF'),
(19, 4, 'Case de villégiature Boutoupa',
 'Hébergement rustique-chic dans le village de Boutoupa, nature préservée.',
 'maison', 'Boutoupa', 'Sedhiou', 'Boutoupa', 'Sénégal', 12.72000000, -15.54000000, 90.00, 2, 1, 4,
 'Accès 4x4 conseillé en saison des pluies.', 'approved', 'XOF'),
(20, 2, 'Chambre d''hôtes Tanaff',
 'Chambre spacieuse avec petit-déjeuner possible, accueil chaleureux.',
 'chambre', 'Tanaff', 'Sedhiou', 'Tanaff', 'Sénégal', 12.69500000, -15.57000000, 22.00, 1, 1, 2,
 'Espace partagé.', 'approved', 'XOF'),

-- Kafountine (4)
(21, 2, 'Campement pêcheurs Abéné',
 'Hébergement authentique à Abéné, célèbre pour ses poissons braisés.',
 'maison', 'Abéné', 'Kafountine', 'Abéné', 'Sénégal', 12.93000000, -16.05000000, 85.00, 2, 1, 4,
 'Ambiance conviviale.', 'approved', 'XOF'),
(22, 4, 'Villa plage Kafountine',
 'Villa proche du port de pêche et des restaurants de bord de mer.',
 'villa', 'Front de mer', 'Kafountine', 'Centre', 'Sénégal', 12.92500000, -16.04500000, 175.00, 3, 2, 6,
 'Parfait amateurs de fruits de mer.', 'approved', 'XOF'),
(23, 2, 'Studio vue lagune',
 'Studio avec vue lagune, idéal week-end nature.',
 'studio', 'Route du port', 'Kafountine', 'Centre', 'Sénégal', 12.92800000, -16.04800000, 35.00, 1, 1, 2,
 'Calme et reposant.', 'approved', 'XOF'),
(24, 4, 'Maison familiale Kafountine',
 'Grande maison pour familles, proche plage et marché aux poissons.',
 'maison', 'Quartier résidentiel', 'Kafountine', 'Centre', 'Sénégal', 12.92200000, -16.04200000, 130.00, 3, 2, 7,
 'Barbecue disponible.', 'approved', 'XOF'),

-- Diouloulou (1 — 25e logement)
(25, 2, 'Maison rizières Diouloulou',
 'Maison entourée de rizières verdoyantes, cadre authentique casamançais.',
 'maison', 'Diouloulou village', 'Diouloulou', 'Centre', 'Sénégal', 12.65000000, -16.38000000, 120.00, 2, 1, 5,
 'Sérénité garantie.', 'approved', 'XOF');

-- ============================================================
-- Tarifs
-- ============================================================

INSERT INTO `property_prices` (`property_id`, `price_per_night`, `price_per_week`, `price_per_month`, `currency`) VALUES
(6,  25000.00, 150000.00, 550000.00, 'XOF'),
(7,  32000.00, 190000.00, 680000.00, 'XOF'),
(8,  85000.00, 500000.00, 1700000.00, 'XOF'),
(9,  45000.00, 270000.00, 950000.00, 'XOF'),
(10, 55000.00, 330000.00, 1100000.00, 'XOF'),
(11, 28000.00, 165000.00, 580000.00, 'XOF'),
(12, 12000.00,  72000.00, 260000.00, 'XOF'),
(13, 65000.00, 380000.00, 1300000.00, 'XOF'),
(14, 18000.00, 108000.00, 400000.00, 'XOF'),
(15, 22000.00, 130000.00, 470000.00, 'XOF'),
(16, 10000.00,  60000.00, 220000.00, 'XOF'),
(17, 20000.00, 120000.00, 430000.00, 'XOF'),
(18, 17000.00, 100000.00, 360000.00, 'XOF'),
(19, 15000.00,  90000.00, 320000.00, 'XOF'),
(20,  9000.00,  54000.00, 195000.00, 'XOF'),
(21, 24000.00, 140000.00, 500000.00, 'XOF'),
(22, 70000.00, 410000.00, 1400000.00, 'XOF'),
(23, 14000.00,  84000.00, 300000.00, 'XOF'),
(24, 26000.00, 155000.00, 560000.00, 'XOF'),
(25, 19000.00, 114000.00, 410000.00, 'XOF');

-- ============================================================
-- Équipements
-- ============================================================

INSERT INTO `property_amenities` (`property_id`, `amenity_id`) VALUES
(6, 1), (6, 2), (6, 3), (6, 5), (6, 7),
(7, 1), (7, 2), (7, 3), (7, 5), (7, 6), (7, 9),
(8, 1), (8, 2), (8, 3), (8, 4), (8, 5), (8, 7), (8, 8),
(9, 1), (9, 2), (9, 5), (9, 7), (9, 9),
(10, 1), (10, 2), (10, 4), (10, 5), (10, 9),
(11, 1), (11, 3), (11, 5), (11, 10),
(12, 1), (12, 2), (12, 5), (12, 7),
(13, 1), (13, 2), (13, 3), (13, 4), (13, 5), (13, 8),
(14, 1), (14, 2), (14, 3), (14, 5),
(15, 1), (15, 3), (15, 5), (15, 6), (15, 10),
(16, 1), (16, 2), (16, 5),
(17, 1), (17, 3), (17, 5), (17, 9),
(18, 1), (18, 2), (18, 5), (18, 7),
(19, 1), (19, 5), (19, 10),
(20, 1), (20, 2), (20, 8),
(21, 1), (21, 5), (21, 7),
(22, 1), (22, 2), (22, 3), (22, 4), (22, 5), (22, 7),
(23, 1), (23, 2), (23, 5),
(24, 1), (24, 3), (24, 5), (24, 6),
(25, 1), (25, 3), (25, 5), (25, 9);

-- ============================================================
-- Photos (4 par logement — Unsplash)
-- ============================================================

INSERT IGNORE INTO `property_images` (`property_id`, `path`, `is_primary`, `sort_order`) VALUES
-- 6
(6, 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800&h=600&fit=crop', 1, 0),
(6, 'https://images.unsplash.com/photo-1560448204-e02f11c45751?w=800&h=600&fit=crop', 0, 1),
(6, 'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=800&h=600&fit=crop', 0, 2),
(6, 'https://images.unsplash.com/photo-1556912173-46c336c7fd55?w=800&h=600&fit=crop', 0, 3),
-- 7
(7, 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&h=600&fit=crop', 1, 0),
(7, 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800&h=600&fit=crop', 0, 1),
(7, 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=800&h=600&fit=crop', 0, 2),
(7, 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800&h=600&fit=crop', 0, 3),
-- 8
(8, 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800&h=600&fit=crop', 1, 0),
(8, 'https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=800&h=600&fit=crop', 0, 1),
(8, 'https://images.unsplash.com/photo-1600047509807-ba8f99d2cd7a?w=800&h=600&fit=crop', 0, 2),
(8, 'https://images.unsplash.com/photo-1600210492494-03fe3c5fbf0a?w=800&h=600&fit=crop', 0, 3),
-- 9
(9, 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800&h=600&fit=crop', 1, 0),
(9, 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800&h=600&fit=crop', 0, 1),
(9, 'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?w=800&h=600&fit=crop', 0, 2),
(9, 'https://images.unsplash.com/photo-1560448204-e02f11c45751?w=800&h=600&fit=crop', 0, 3),
-- 10
(10, 'https://images.unsplash.com/photo-1600047509358-9dc75507de08?w=800&h=600&fit=crop', 1, 0),
(10, 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&h=600&fit=crop', 0, 1),
(10, 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800&h=600&fit=crop', 0, 2),
(10, 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800&h=600&fit=crop', 0, 3),
-- 11
(11, 'https://images.unsplash.com/photo-1600566753086-00f18fb576b9?w=800&h=600&fit=crop', 1, 0),
(11, 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&h=600&fit=crop', 0, 1),
(11, 'https://images.unsplash.com/photo-1556912173-46c336c7fd55?w=800&h=600&fit=crop', 0, 2),
(11, 'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=800&h=600&fit=crop', 0, 3),
-- 12
(12, 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800&h=600&fit=crop', 1, 0),
(12, 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800&h=600&fit=crop', 0, 1),
(12, 'https://images.unsplash.com/photo-1560448204-e02f11c45751?w=800&h=600&fit=crop', 0, 2),
(12, 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800&h=600&fit=crop', 0, 3),
-- 13
(13, 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800&h=600&fit=crop', 1, 0),
(13, 'https://images.unsplash.com/photo-1600047509807-ba8f99d2cd7a?w=800&h=600&fit=crop', 0, 1),
(13, 'https://images.unsplash.com/photo-1600210492494-03fe3c5fbf0a?w=800&h=600&fit=crop', 0, 2),
(13, 'https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=800&h=600&fit=crop', 0, 3),
-- 14
(14, 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800&h=600&fit=crop', 1, 0),
(14, 'https://images.unsplash.com/photo-1560448204-e02f11c45751?w=800&h=600&fit=crop', 0, 1),
(14, 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800&h=600&fit=crop', 0, 2),
(14, 'https://images.unsplash.com/photo-1556912173-46c336c7fd55?w=800&h=600&fit=crop', 0, 3),
-- 15
(15, 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=800&h=600&fit=crop', 1, 0),
(15, 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&h=600&fit=crop', 0, 1),
(15, 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800&h=600&fit=crop', 0, 2),
(15, 'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=800&h=600&fit=crop', 0, 3),
-- 16
(16, 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800&h=600&fit=crop', 1, 0),
(16, 'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?w=800&h=600&fit=crop', 0, 1),
(16, 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800&h=600&fit=crop', 0, 2),
(16, 'https://images.unsplash.com/photo-1560448204-e02f11c45751?w=800&h=600&fit=crop', 0, 3),
-- 17
(17, 'https://images.unsplash.com/photo-1600566753086-00f18fb576b9?w=800&h=600&fit=crop', 1, 0),
(17, 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&h=600&fit=crop', 0, 1),
(17, 'https://images.unsplash.com/photo-1556912173-46c336c7fd55?w=800&h=600&fit=crop', 0, 2),
(17, 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800&h=600&fit=crop', 0, 3),
-- 18
(18, 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800&h=600&fit=crop', 1, 0),
(18, 'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=800&h=600&fit=crop', 0, 1),
(18, 'https://images.unsplash.com/photo-1560448204-e02f11c45751?w=800&h=600&fit=crop', 0, 2),
(18, 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800&h=600&fit=crop', 0, 3),
-- 19
(19, 'https://images.unsplash.com/photo-1600047509358-9dc75507de08?w=800&h=600&fit=crop', 1, 0),
(19, 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=800&h=600&fit=crop', 0, 1),
(19, 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800&h=600&fit=crop', 0, 2),
(19, 'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?w=800&h=600&fit=crop', 0, 3),
-- 20
(20, 'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?w=800&h=600&fit=crop', 1, 0),
(20, 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800&h=600&fit=crop', 0, 1),
(20, 'https://images.unsplash.com/photo-1560448204-e02f11c45751?w=800&h=600&fit=crop', 0, 2),
(20, 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800&h=600&fit=crop', 0, 3),
-- 21
(21, 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&h=600&fit=crop', 1, 0),
(21, 'https://images.unsplash.com/photo-1600566753086-00f18fb576b9?w=800&h=600&fit=crop', 0, 1),
(21, 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800&h=600&fit=crop', 0, 2),
(21, 'https://images.unsplash.com/photo-1600210492494-03fe3c5fbf0a?w=800&h=600&fit=crop', 0, 3),
-- 22
(22, 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800&h=600&fit=crop', 1, 0),
(22, 'https://images.unsplash.com/photo-1600047509807-ba8f99d2cd7a?w=800&h=600&fit=crop', 0, 1),
(22, 'https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=800&h=600&fit=crop', 0, 2),
(22, 'https://images.unsplash.com/photo-1600047509358-9dc75507de08?w=800&h=600&fit=crop', 0, 3),
-- 23
(23, 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800&h=600&fit=crop', 1, 0),
(23, 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800&h=600&fit=crop', 0, 1),
(23, 'https://images.unsplash.com/photo-1560448204-e02f11c45751?w=800&h=600&fit=crop', 0, 2),
(23, 'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=800&h=600&fit=crop', 0, 3),
-- 24
(24, 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=800&h=600&fit=crop', 1, 0),
(24, 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&h=600&fit=crop', 0, 1),
(24, 'https://images.unsplash.com/photo-1556912173-46c336c7fd55?w=800&h=600&fit=crop', 0, 2),
(24, 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800&h=600&fit=crop', 0, 3),
-- 25
(25, 'https://images.unsplash.com/photo-1600566753086-00f18fb576b9?w=800&h=600&fit=crop', 1, 0),
(25, 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&h=600&fit=crop', 0, 1),
(25, 'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=800&h=600&fit=crop', 0, 2),
(25, 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800&h=600&fit=crop', 0, 3);
