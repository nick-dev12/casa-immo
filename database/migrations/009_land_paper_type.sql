ALTER TABLE `lands`
    ADD COLUMN `paper_type` ENUM(
        'titre_foncier',
        'deliberation',
        'bail_emphyteotique'
    ) DEFAULT NULL AFTER `land_type`,
    ADD KEY `lands_paper_type_index` (`paper_type`);
