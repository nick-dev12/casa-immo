-- Mise à jour des données pour la Casamance
USE `ariaqqrw_casa_immo`;

UPDATE `properties` SET
    `title` = 'Appartement moderne à Ziguinchor',
    `description` = 'Appartement meublé en centre-ville, proche du marché et des transports. Idéal pour séjours courts ou moyens en Casamance.',
    `address` = 'Avenue Lamine Guèye',
    `city` = 'Ziguinchor',
    `district` = 'Centre-ville',
    `latitude` = 12.58330000,
    `longitude` = -16.27190000
WHERE `id` = 1;

UPDATE `properties` SET
    `title` = 'Villa luxueuse à Cap Skirring',
    `description` = 'Villa spacieuse avec piscine privée, proche de la plage. Parfaite pour familles en vacances au bord de l\'océan.',
    `type` = 'villa',
    `address` = 'Route de la Plage',
    `city` = 'Cap Skirring',
    `district` = 'Bord de mer',
    `latitude` = 12.39500000,
    `longitude` = -16.74810000
WHERE `id` = 2;

UPDATE `properties` SET
    `title` = 'Studio cosy à Ziguinchor',
    `description` = 'Studio fonctionnel proche des commerces et restaurants locaux. Idéal voyageurs solo en Casamance.',
    `address` = 'Quartier Kandialang',
    `city` = 'Ziguinchor',
    `district` = 'Kandialang',
    `latitude` = 12.59000000,
    `longitude` = -16.28000000
WHERE `id` = 3;

UPDATE `properties` SET
    `title` = 'Maison familiale à Bignona',
    `description` = 'Grande maison avec cour intérieure, parfaite pour location mensuelle en Casamance.',
    `address` = 'Quartier Escale',
    `city` = 'Bignona',
    `district` = 'Escale',
    `latitude` = 12.81030000,
    `longitude` = -16.22640000
WHERE `id` = 4;

UPDATE `properties` SET
    `title` = 'Chambre privée à Oussouye',
    `description` = 'Chambre confortable dans résidence calme, accès cuisine partagée. Au cœur du pays Bassari.',
    `address` = 'Oussouye Centre',
    `city` = 'Oussouye',
    `district` = 'Centre',
    `latitude` = 12.48330000,
    `longitude` = -16.55000000
WHERE `id` = 5;

UPDATE `lands` SET
    `title` = 'Terrain 500 m² à Ziguinchor',
    `description` = 'Terrain viabilisé proche du centre de Ziguinchor. Idéal construction villa ou immeuble.',
    `city` = 'Ziguinchor',
    `district` = 'Nagor',
    `address` = 'Lotissement Nagor',
    `latitude` = 12.59000000,
    `longitude` = -16.26000000
WHERE `id` = 1;

UPDATE `lands` SET
    `title` = 'Parcelle agricole 2 ha — Sedhiou',
    `description` = 'Terrain fertile en Casamance, accès eau, parfait pour culture maraîchère ou élevage.',
    `city` = 'Sedhiou',
    `district` = 'Boutoupa',
    `address` = 'Route de Boutoupa',
    `latitude` = 12.70810000,
    `longitude` = -15.55690000
WHERE `id` = 2;

UPDATE `lands` SET
    `title` = 'Terrain commercial Ziguinchor',
    `description` = 'Emplacement stratégique pour commerce ou bureaux en centre-ville de Ziguinchor.',
    `city` = 'Ziguinchor',
    `district` = 'Centre-ville',
    `address` = 'Avenue Général de Gaulle',
    `latitude` = 12.58400000,
    `longitude` = -16.27300000,
    `land_type` = 'commercial'
WHERE `id` = 3;

UPDATE `lands` SET
    `title` = 'Terrain résidentiel Bignona',
    `description` = 'Parcelle clôturée avec accès route principale, zone en développement.',
    `city` = 'Bignona',
    `district` = 'Zone résidentielle',
    `address` = 'Route de Bignona',
    `latitude` = 12.81500000,
    `longitude` = -16.23000000,
    `land_type` = 'residentiel'
WHERE `id` = 4;

UPDATE `conversations` SET `subject` = 'Réservation appartement Ziguinchor' WHERE `id` = 1;
UPDATE `conversations` SET `subject` = 'Visite terrain Ziguinchor' WHERE `id` = 2;

UPDATE `notifications` SET
    `title` = 'Nouvelle réservation',
    `body` = 'Fatou Sow a réservé votre appartement à Ziguinchor.'
WHERE `id` = 2;

UPDATE `notifications` SET
    `body` = 'Fatou Sow souhaite visiter votre terrain à Ziguinchor.'
WHERE `id` = 5;

UPDATE `settings` SET `value` = 'Zig Imobilier — Casamance' WHERE `setting_key` = 'site_name';
