-- Admin = super admin + agence immobilière (publication)
USE `ariaqqrw_casa_immo`;

INSERT IGNORE INTO `user_roles` (`user_id`, `role_id`)
SELECT u.id, r.id
FROM `users` u
CROSS JOIN `roles` r
WHERE u.email = 'admin@zig.com'
  AND u.deleted_at IS NULL
  AND r.slug IN ('agency', 'owner');

INSERT INTO `establishments` (`owner_id`, `name`, `description`, `city`, `district`, `address`, `phone`, `status`)
SELECT u.id,
       'Zig Immobilier',
       'Agence immobilière — gestion locative, vente de terrains, construction et hébergement meublé.',
       'Ziguinchor',
       'Centre-ville',
       'Ziguinchor',
       COALESCE(u.phone, '+221770000000'),
       'active'
FROM `users` u
WHERE u.email = 'admin@zig.com'
  AND u.deleted_at IS NULL
  AND NOT EXISTS (
      SELECT 1 FROM `establishments` e WHERE e.owner_id = u.id
  );
