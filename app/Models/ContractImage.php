<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class ContractImage extends Model
{
    public const MAX_PER_CONTRACT = 5;

    /**
     * @return list<array<string, mixed>>
     */
    public function forContract(int $contractId): array
    {
        $stmt = $this->db->prepare('
            SELECT id, path, is_primary, sort_order
            FROM contract_images
            WHERE contract_id = :contract_id
            ORDER BY is_primary DESC, sort_order ASC, id ASC
        ');
        $stmt->execute([':contract_id' => $contractId]);

        return $stmt->fetchAll() ?: [];
    }

    /**
     * @param list<string> $paths
     */
    public function addMany(int $contractId, array $paths): void
    {
        if ($paths === []) {
            return;
        }

        $stmt = $this->db->prepare('
            INSERT INTO contract_images (contract_id, path, is_primary, sort_order)
            VALUES (:contract_id, :path, :is_primary, :sort_order)
        ');

        foreach ($paths as $index => $path) {
            $stmt->execute([
                ':contract_id' => $contractId,
                ':path' => $path,
                ':is_primary' => $index === 0 ? 1 : 0,
                ':sort_order' => $index,
            ]);
        }
    }
}
