-- Super admin : mourtallafalloudiouf@gmail.com
USE `ariaqqrw_casa_immo`;

INSERT INTO `users` (`first_name`, `last_name`, `email`, `password`, `email_verified_at`, `status`)
SELECT 'Mourtalla', 'Fallou Diouf', 'mourtallafalloudiouf@gmail.com', '$2y$12$roq35IazXJ7YeVJZx8LRye0liW2t9AHTD51KuHS0RRKzxHzx2gcHC', NOW(), 'active'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `users` WHERE `email` = 'mourtallafalloudiouf@gmail.com' AND `deleted_at` IS NULL
);

UPDATE `users`
SET `password` = '$2y$12$roq35IazXJ7YeVJZx8LRye0liW2t9AHTD51KuHS0RRKzxHzx2gcHC',
    `status` = 'active',
    `email_verified_at` = COALESCE(`email_verified_at`, NOW())
WHERE `email` = 'mourtallafalloudiouf@gmail.com' AND `deleted_at` IS NULL;

INSERT IGNORE INTO `user_roles` (`user_id`, `role_id`)
SELECT u.id, r.id
FROM `users` u
CROSS JOIN `roles` r
WHERE u.email = 'mourtallafalloudiouf@gmail.com'
  AND u.deleted_at IS NULL
  AND r.slug = 'admin';
