-- Coordonnées GPS des établissements / agences

USE `ariaqqrw_casa_immo`;

ALTER TABLE `establishments`
    ADD COLUMN IF NOT EXISTS `latitude` DECIMAL(10, 8) DEFAULT NULL AFTER `address`,
    ADD COLUMN IF NOT EXISTS `longitude` DECIMAL(11, 8) DEFAULT NULL AFTER `latitude`;
