<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Model;
use App\Models\HostLand;
use App\Models\HostProperty;
use App\Models\ListingImage;
use App\Models\ModerationAction;

final class AdminModerationService extends Model
{
    /** @var list<string> */
    public const MOTIVES = [
        'misleading_photos',
        'incorrect_price',
        'false_information',
        'illegal_content',
        'duplicate_spam',
        'location_mismatch',
        'missing_documents',
        'abusive_behavior',
        'policy_violation',
        'other',
    ];

    /** @var list<string> */
    public const ACTIONS = ['warning', 'suspend', 'reject', 'restore'];

    /**
     * @return array<string, string>
     */
    public function motiveLabels(): array
    {
        $labels = [];
        foreach (self::MOTIVES as $slug) {
            $labels[$slug] = __('admin.moderation.motive.' . $slug);
        }

        return $labels;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function propertyDetail(int $propertyId): ?array
    {
        $stmt = $this->db->prepare('
            SELECT
                p.*,
                pp.price_per_night,
                pp.price_per_month,
                pp.currency AS price_currency,
                u.id AS owner_user_id,
                u.first_name,
                u.last_name,
                u.email,
                u.phone,
                u.status AS owner_status,
                u.created_at AS owner_since,
                e.id AS establishment_id,
                e.name AS establishment_name,
                e.phone AS establishment_phone,
                e.address AS establishment_address,
                e.city AS establishment_city,
                e.district AS establishment_district
            FROM properties p
            INNER JOIN users u ON u.id = p.owner_id AND u.deleted_at IS NULL
            LEFT JOIN establishments e ON e.id = p.establishment_id
            LEFT JOIN property_prices pp ON pp.property_id = p.id
            WHERE p.id = :id
              AND p.deleted_at IS NULL
            LIMIT 1
        ');
        $stmt->execute([':id' => $propertyId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function landDetail(int $landId): ?array
    {
        $stmt = $this->db->prepare('
            SELECT
                l.*,
                u.id AS owner_user_id,
                u.first_name,
                u.last_name,
                u.email,
                u.phone,
                u.status AS owner_status,
                u.created_at AS owner_since
            FROM lands l
            INNER JOIN users u ON u.id = l.seller_id AND u.deleted_at IS NULL
            WHERE l.id = :id
              AND l.deleted_at IS NULL
            LIMIT 1
        ');
        $stmt->execute([':id' => $landId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function imagesFor(string $type, int $id): array
    {
        $imageModel = new ListingImage();

        return $type === 'land'
            ? $imageModel->forLand($id)
            : $imageModel->forProperty($id);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function historyFor(string $type, int $id): array
    {
        return (new ModerationAction())->forTarget($type, $id);
    }

    /**
     * @param list<string> $motives
     */
    public function applyAction(
        int $adminId,
        string $type,
        int $targetId,
        string $action,
        array $motives,
        string $notes = ''
    ): bool {
        if (!in_array($type, ['property', 'land'], true)) {
            return false;
        }

        if (!in_array($action, self::ACTIONS, true)) {
            return false;
        }

        $validMotives = array_values(array_intersect($motives, self::MOTIVES));
        if ($action !== 'restore' && $validMotives === []) {
            return false;
        }

        if ($type === 'property' && $this->propertyDetail($targetId) === null) {
            return false;
        }

        if ($type === 'land' && $this->landDetail($targetId) === null) {
            return false;
        }

        if ($action === 'warning') {
            (new ModerationAction())->create($adminId, $type, $targetId, $action, $validMotives, $notes);

            return true;
        }

        $newStatus = match ($action) {
            'suspend' => 'suspended',
            'reject' => 'rejected',
            'restore' => 'approved',
            default => null,
        };

        if ($newStatus === null) {
            return false;
        }

        if ($type === 'property') {
            (new HostProperty())->updateStatus($targetId, $newStatus);
        } else {
            (new HostLand())->updateStatus($targetId, $newStatus);
        }

        (new ModerationAction())->create($adminId, $type, $targetId, $action, $validMotives, $notes);

        return true;
    }
}
