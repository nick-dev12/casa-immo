-- Confirmer automatiquement les réservations en attente (plus de validation manuelle).
UPDATE `bookings`
SET `status` = 'confirmed',
    `updated_at` = NOW()
WHERE `status` = 'pending';
