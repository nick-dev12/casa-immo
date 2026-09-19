-- Rôle agence immobilière
INSERT INTO `roles` (`name`, `slug`, `description`)
SELECT 'Agence immobilière', 'agency', 'Gère des annonces pour une agence immobilière'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `roles` WHERE `slug` = 'agency');
