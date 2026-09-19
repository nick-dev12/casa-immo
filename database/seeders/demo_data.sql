-- ============================================================
-- Zig Imobilier — Données de démonstration
-- Mot de passe pour tous les comptes : Admin@123
-- ============================================================

USE `ariaqqrw_casa_immo`;

SET @pwd = '$2y$12$VkfqtRMsEBdiQ61e9YoGceFdq9c2SSEZ08U9/tWmsNz.9tTqxa.KO';

-- ------------------------------------------------------------
-- Rôles
-- ------------------------------------------------------------

INSERT INTO `roles` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Administrateur', 'admin', 'Accès complet à la plateforme'),
(2, 'Client',         'client', 'Recherche, réserve et paie'),
(3, 'Propriétaire',   'owner',  'Publie et gère des logements'),
(4, 'Vendeur terrain','land_seller', 'Publie et vend des terrains');

-- ------------------------------------------------------------
-- Utilisateurs
-- ------------------------------------------------------------

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `phone`, `password`, `email_verified_at`, `status`) VALUES
(1, 'Admin',     'Zig',       'admin@zig-imobilier.sn',    '+221770000001', @pwd, NOW(), 'active'),
(2, 'Amadou',    'Diallo',    'amadou.diallo@email.sn',    '+221770000002', @pwd, NOW(), 'active'),
(3, 'Fatou',     'Sow',       'fatou.sow@email.sn',        '+221770000003', @pwd, NOW(), 'active'),
(4, 'Moussa',    'Ndiaye',    'moussa.ndiaye@email.sn',    '+221770000004', @pwd, NOW(), 'active'),
(5, 'Aïssatou',  'Ba',        'aissatou.ba@email.sn',      '+221770000005', @pwd, NOW(), 'active'),
(6, 'Ibrahima',  'Fall',      'ibrahima.fall@email.sn',    '+221770000006', @pwd, NOW(), 'active'),
(7, 'Mariama',   'Diop',      'mariama.diop@email.sn',     '+221770000007', @pwd, NOW(), 'active');

INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES
(1, 1),
(2, 2), (2, 3),
(3, 2),
(4, 3),
(5, 2), (5, 4),
(6, 4),
(7, 2);

-- ------------------------------------------------------------
-- Paramètres plateforme
-- ------------------------------------------------------------

INSERT INTO `settings` (`setting_key`, `value`, `type`, `description`) VALUES
('commission_rate',       '10',    'float',   'Taux de commission plateforme (%)'),
('currency_default',      'XOF',   'string',  'Devise par défaut'),
('booking_min_nights',    '1',     'integer', 'Nombre minimum de nuits'),
('site_name',             'Zig Imobilier', 'string', 'Nom du site'),
('email_verification',    'true',  'boolean', 'Vérification email obligatoire');

-- ------------------------------------------------------------
-- Équipements
-- ------------------------------------------------------------

INSERT INTO `amenities` (`id`, `name`, `icon`, `category`) VALUES
(1,  'Wi-Fi',            'wifi',           'confort'),
(2,  'Climatisation',    'snow',           'confort'),
(3,  'Parking',          'car',            'exterieur'),
(4,  'Piscine',          'pool',           'exterieur'),
(5,  'Cuisine équipée',  'utensils',       'cuisine'),
(11, 'Salon meublé',     'sofa',           'cuisine'),
(6,  'Lave-linge',       'washer',         'confort'),
(7,  'Télévision',       'tv',             'confort'),
(8,  'Sécurité 24h/24',  'shield',         'securite'),
(9,  'Balcon',           'door-open',      'exterieur'),
(10, 'Eau chaude',       'droplet',        'confort');

-- ------------------------------------------------------------
-- Logements
-- ------------------------------------------------------------

INSERT INTO `properties` (`id`, `owner_id`, `title`, `description`, `type`, `address`, `city`, `district`, `country`, `latitude`, `longitude`, `area_sqm`, `bedrooms`, `bathrooms`, `capacity`, `rules`, `status`, `currency`) VALUES
(1, 2, 'Appartement moderne à Almadies',
 'Magnifique appartement meublé avec vue mer, idéal pour séjours courts ou moyens. Proche des restaurants et plages.',
 'appartement', 'Route des Almadies', 'Dakar', 'Almadies', 'Sénégal', 14.71670000, -17.51670000, 85.00, 2, 1, 4,
 'Non fumeur. Pas d''animaux. Check-in 14h, check-out 11h.', 'approved', 'XOF'),

(2, 2, 'Villa luxueuse à Saly',
 'Villa spacieuse avec piscine privée, jardin tropical et accès direct à la plage. Parfaite pour familles.',
 'villa', 'Saly Portudal', 'Mbour', 'Saly', 'Sénégal', 14.43670000, -17.02170000, 250.00, 4, 3, 8,
 'Fêtes interdites. Caution requise.', 'approved', 'XOF'),

(3, 4, 'Studio cosy Plateau',
 'Studio fonctionnel au cœur du Plateau, proche des administrations et commerces. Idéal voyageurs solo.',
 'studio', 'Avenue Roume', 'Dakar', 'Plateau', 'Sénégal', 14.69280000, -17.44670000, 35.00, 1, 1, 2,
 'Silence après 22h.', 'approved', 'XOF'),

(4, 4, 'Maison familiale à Thiès',
 'Grande maison avec cour intérieure, parfaite pour location mensuelle ou longue durée.',
 'maison', 'Quartier Escale', 'Thiès', 'Escale', 'Sénégal', 14.79130000, -16.92560000, 180.00, 3, 2, 6,
 'Animaux acceptés avec accord.', 'approved', 'XOF'),

(5, 2, 'Chambre privée Mermoz',
 'Chambre confortable dans résidence sécurisée, accès cuisine partagée.',
 'chambre', 'Mermoz Pyrotechnie', 'Dakar', 'Mermoz', 'Sénégal', 14.70780000, -17.47970000, 18.00, 1, 1, 1,
 'Espace commun à partager.', 'approved', 'XOF');

INSERT INTO `property_prices` (`property_id`, `price_per_night`, `price_per_week`, `price_per_month`, `currency`) VALUES
(1, 30000.00, 180000.00, 650000.00, 'XOF'),
(2, 75000.00, 450000.00, 1500000.00, 'XOF'),
(3, 15000.00,  90000.00, 320000.00, 'XOF'),
(4, 20000.00, 120000.00, 450000.00, 'XOF'),
(5,  8000.00,  48000.00, 180000.00, 'XOF');

INSERT INTO `property_amenities` (`property_id`, `amenity_id`) VALUES
(1, 1), (1, 2), (1, 3), (1, 5), (1, 7), (1, 9),
(2, 1), (2, 2), (2, 3), (2, 4), (2, 5), (2, 6), (2, 7), (2, 8),
(3, 1), (3, 2), (3, 5), (3, 7),
(4, 1), (4, 3), (4, 5), (4, 6), (4, 10),
(5, 1), (5, 2), (5, 8);

INSERT INTO `property_images` (`property_id`, `path`, `is_primary`, `sort_order`) VALUES
(1, 'uploads/properties/1/main.jpg', 1, 0),
(1, 'uploads/properties/1/salon.jpg', 0, 1),
(2, 'uploads/properties/2/main.jpg', 1, 0),
(2, 'uploads/properties/2/piscine.jpg', 0, 1),
(3, 'uploads/properties/3/main.jpg', 1, 0),
(4, 'uploads/properties/4/main.jpg', 1, 0),
(5, 'uploads/properties/5/main.jpg', 1, 0);

-- ------------------------------------------------------------
-- Disponibilités (exemple septembre 2026)
-- ------------------------------------------------------------

INSERT INTO `property_availability` (`property_id`, `date`, `status`, `note`) VALUES
(1, '2026-09-10', 'blocked', 'Travaux peinture'),
(1, '2026-09-11', 'blocked', 'Travaux peinture'),
(1, '2026-09-17', 'blocked', 'Maintenance clim'),
(1, '2026-09-18', 'blocked', 'Maintenance clim'),
(1, '2026-09-19', 'blocked', 'Maintenance clim');

-- ------------------------------------------------------------
-- Réservations
-- ------------------------------------------------------------

INSERT INTO `bookings` (`id`, `property_id`, `user_id`, `check_in`, `check_out`, `guests`, `nights`, `rental_type`, `price_per_unit`, `subtotal`, `commission_rate`, `commission_amount`, `total_amount`, `currency`, `status`) VALUES
(1, 1, 3, '2026-09-14', '2026-09-17', 2, 3, 'nightly', 30000.00, 90000.00, 10.00, 9000.00, 90000.00, 'XOF', 'confirmed'),
(2, 2, 7, '2026-10-01', '2026-10-08', 4, 7, 'weekly', 75000.00, 525000.00, 10.00, 52500.00, 525000.00, 'XOF', 'pending');

INSERT INTO `booking_guests` (`booking_id`, `first_name`, `last_name`, `email`, `phone`) VALUES
(1, 'Fatou', 'Sow', 'fatou.sow@email.sn', '+221770000003'),
(2, 'Mariama', 'Diop', 'mariama.diop@email.sn', '+221770000007');

UPDATE `property_availability` SET `status` = 'reserved', `booking_id` = 1 WHERE `property_id` = 1 AND `date` IN ('2026-09-14', '2026-09-15', '2026-09-16');

INSERT INTO `property_availability` (`property_id`, `date`, `status`, `booking_id`) VALUES
(1, '2026-09-14', 'reserved', 1),
(1, '2026-09-15', 'reserved', 1),
(1, '2026-09-16', 'reserved', 1)
ON DUPLICATE KEY UPDATE `status` = 'reserved', `booking_id` = 1;

INSERT INTO `commissions` (`booking_id`, `total_amount`, `percentage`, `platform_amount`, `owner_amount`) VALUES
(1, 90000.00, 10.00, 9000.00, 81000.00);

INSERT INTO `payments` (`booking_id`, `user_id`, `amount`, `currency`, `gateway`, `status`, `reference`, `paid_at`) VALUES
(1, 3, 90000.00, 'XOF', 'wave', 'completed', 'WAVE-20260901-001', '2026-09-01 10:30:00');

INSERT INTO `payment_transactions` (`payment_id`, `transaction_ref`, `gateway_response`, `status`, `amount`) VALUES
(1, 'TXN-WAVE-001', '{"status":"success","provider":"wave"}', 'success', 90000.00);

-- ------------------------------------------------------------
-- Avis
-- ------------------------------------------------------------

INSERT INTO `reviews` (`user_id`, `property_id`, `booking_id`, `rating`, `comment`) VALUES
(3, 1, 1, 5, 'Excellent séjour ! Appartement très propre et bien situé.');

-- ------------------------------------------------------------
-- Favoris
-- ------------------------------------------------------------

INSERT INTO `favorites` (`user_id`, `favoritable_type`, `favoritable_id`) VALUES
(3, 'property', 2),
(3, 'property', 4),
(7, 'property', 1),
(5, 'land', 1);

-- ------------------------------------------------------------
-- Terrains
-- ------------------------------------------------------------

INSERT INTO `lands` (`id`, `seller_id`, `title`, `description`, `area`, `area_unit`, `price`, `currency`, `city`, `district`, `address`, `latitude`, `longitude`, `land_type`, `status`) VALUES
(1, 5, 'Terrain 500 m² à Diamniadio',
 'Terrain viabilisé proche du nouveau centre urbain de Diamniadio. Idéal construction villa ou immeuble R+2.',
 500.00, 'm2', 5000000.00, 'XOF', 'Diamniadio', 'Zone résidentielle', 'Lotissement Diamniadio Nord', 14.72000000, -17.18500000, 'residentiel', 'approved'),

(2, 6, 'Parcelle agricole 2 hectares - Thiès',
 'Terrain fertile avec accès eau, parfait pour culture maraîchère ou élevage.',
 2.00, 'ha', 15000000.00, 'XOF', 'Thiès', 'Notto', 'Route de Notto', 14.85000000, -16.95000000, 'agricole', 'approved'),

(3, 5, 'Terrain commercial Plateau',
 'Emplacement premium pour commerce ou bureaux, titre foncier disponible.',
 300.00, 'm2', 85000000.00, 'XOF', 'Dakar', 'Plateau', 'Avenue Léopold Sédar Senghor', 14.67000000, -17.43000000, 'commercial', 'approved'),

(4, 6, 'Terrain industriel Rufisque',
 'Zone industrielle avec accès route principale, clôturé.',
 5000.00, 'm2', 120000000.00, 'XOF', 'Rufisque', 'Zone industrielle', 'Route de Rufisque', 14.71670000, -17.26670000, 'industriel', 'approved');

INSERT INTO `land_images` (`land_id`, `path`, `is_primary`, `sort_order`) VALUES
(1, 'uploads/lands/1/main.jpg', 1, 0),
(1, 'uploads/lands/1/vue.jpg', 0, 1),
(2, 'uploads/lands/2/main.jpg', 1, 0),
(3, 'uploads/lands/3/main.jpg', 1, 0),
(4, 'uploads/lands/4/main.jpg', 1, 0);

INSERT INTO `land_documents` (`land_id`, `name`, `path`, `doc_type`) VALUES
(1, 'Titre foncier', 'uploads/lands/1/titre-foncier.pdf', 'titre_foncier'),
(1, 'Plan cadastral', 'uploads/lands/1/plan.pdf', 'plan'),
(3, 'Titre foncier', 'uploads/lands/3/titre-foncier.pdf', 'titre_foncier');

INSERT INTO `land_visits` (`land_id`, `user_id`, `requested_date`, `requested_time`, `message`, `status`) VALUES
(1, 3, '2026-09-20', '10:00:00', 'Je souhaite visiter ce terrain pour un projet de construction.', 'pending'),
(3, 7, '2026-09-25', '14:30:00', 'Intéressée pour un local commercial.', 'accepted');

-- ------------------------------------------------------------
-- Contrats longue durée
-- ------------------------------------------------------------

INSERT INTO `contracts` (`property_id`, `owner_id`, `tenant_id`, `start_date`, `end_date`, `monthly_amount`, `deposit_amount`, `terms`, `status`) VALUES
(4, 4, 3, '2026-01-01', '2026-12-31', 450000.00, 900000.00, 'Contrat location annuelle renouvelable. Paiement avant le 5 de chaque mois.', 'active');

INSERT INTO `contract_payments` (`contract_id`, `due_date`, `amount`, `status`, `paid_at`) VALUES
(1, '2026-01-05', 450000.00, 'paid', '2026-01-03 09:00:00'),
(1, '2026-02-05', 450000.00, 'paid', '2026-02-01 11:00:00'),
(1, '2026-03-05', 450000.00, 'paid', '2026-03-02 10:00:00'),
(1, '2026-04-05', 450000.00, 'pending', NULL);

-- ------------------------------------------------------------
-- Messagerie
-- ------------------------------------------------------------

INSERT INTO `conversations` (`id`, `subject`, `property_id`, `land_id`) VALUES
(1, 'Réservation appartement Almadies', 1, NULL),
(2, 'Visite terrain Diamniadio', NULL, 1);

INSERT INTO `conversation_participants` (`conversation_id`, `user_id`) VALUES
(1, 2), (1, 3),
(2, 5), (2, 3);

INSERT INTO `messages` (`conversation_id`, `sender_id`, `body`, `is_read`, `read_at`) VALUES
(1, 3, 'Bonjour, est-il possible d''arriver à 16h le 14 septembre ?', 1, NOW()),
(1, 2, 'Bonjour Fatou, oui pas de problème. Je vous envoie les instructions d''accès.', 1, NOW()),
(2, 3, 'Bonjour, je souhaiterais visiter le terrain samedi prochain.', 0, NULL);

-- ------------------------------------------------------------
-- Notifications
-- ------------------------------------------------------------

INSERT INTO `notifications` (`user_id`, `type`, `title`, `body`, `data`, `is_read`) VALUES
(2, 'booking', 'Nouvelle réservation', 'Fatou Sow a réservé votre appartement à Almadies.', '{"booking_id":1,"property_id":1}', 1),
(3, 'payment', 'Paiement confirmé', 'Votre paiement de 90 000 FCFA a été confirmé.', '{"payment_id":1,"booking_id":1}', 1),
(5, 'land_visit', 'Demande de visite', 'Fatou Sow souhaite visiter votre terrain à Diamniadio.', '{"land_visit_id":1,"land_id":1}', 0),
(1, 'listing', 'Annonce en attente', '1 nouvelle annonce attend validation.', '{"count":0}', 0);

-- ------------------------------------------------------------
-- Signalements
-- ------------------------------------------------------------

INSERT INTO `reports` (`reporter_id`, `reportable_type`, `reportable_id`, `reason`, `description`, `status`) VALUES
(7, 'property', 5, 'description_inexacte', 'Les photos ne correspondent pas à la chambre actuelle.', 'pending');
