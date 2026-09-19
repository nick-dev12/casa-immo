-- Champs complémentaires pour la gestion locative
ALTER TABLE `contracts`
    ADD COLUMN `contract_type` ENUM('habitation', 'commercial', 'saisonnier', 'autre') NOT NULL DEFAULT 'habitation' AFTER `terms`,
    ADD COLUMN `tenant_photo_path` VARCHAR(255) DEFAULT NULL AFTER `pdf_path`;
