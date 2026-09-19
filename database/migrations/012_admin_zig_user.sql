-- Compte super admin : admin@zig.com / Passer123
USE `ariaqqrw_casa_immo`;

INSERT INTO `users` (`first_name`, `last_name`, `email`, `password`, `email_verified_at`, `status`)
SELECT 'Super', 'Admin', 'admin@zig.com', '$2y$12$rkXQysg6JNjGWRAoOgiLzuPymMdAEuFonuJ1gyvPeOhojcvlcNCvi', NOW(), 'active'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `users` WHERE `email` = 'admin@zig.com' AND `deleted_at` IS NULL
);

UPDATE `users`
SET `password` = '$2y$12$rkXQysg6JNjGWRAoOgiLzuPymMdAEuFonuJ1gyvPeOhojcvlcNCvi',
    `status` = 'active',
    `email_verified_at` = COALESCE(`email_verified_at`, NOW())
WHERE `email` = 'admin@zig.com' AND `deleted_at` IS NULL;

INSERT IGNORE INTO `user_roles` (`user_id`, `role_id`)
SELECT u.id, r.id
FROM `users` u
CROSS JOIN `roles` r
WHERE u.email = 'admin@zig.com'
  AND u.deleted_at IS NULL
  AND r.slug = 'admin';
