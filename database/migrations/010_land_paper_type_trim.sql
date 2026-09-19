UPDATE `lands`
SET `paper_type` = NULL
WHERE `paper_type` IN (
    'attestation_domaniale',
    'certificat_propriete',
    'lettre_attribution',
    'permis_occuper',
    'sans_papiers',
    'autre'
);

ALTER TABLE `lands`
    MODIFY COLUMN `paper_type` ENUM(
        'titre_foncier',
        'deliberation',
        'bail_emphyteotique'
    ) DEFAULT NULL;
