<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Model;

final class AdminDashboardService extends Model
{
    /**
     * @return array{
     *   stats: array<string, int|float>,
     *   modules: list<array<string, mixed>>,
     *   recent_bookings: list<array<string, mixed>>
     * }
     */
    public function overview(): array
    {
        return [
            'stats' => $this->stats(),
            'modules' => $this->businessModules(),
            'recent_bookings' => $this->recentBookings(5),
        ];
    }

    /**
     * @return array<string, int|float>
     */
    public function stats(): array
    {
        $establishments = $this->scalarRow('
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = \'active\' THEN 1 ELSE 0 END) AS active
            FROM establishments
        ');

        $users = $this->scalarRow('
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN u.status = \'active\' THEN 1 ELSE 0 END) AS active
            FROM users u
            WHERE u.deleted_at IS NULL
        ');

        $clients = $this->scalar('
            SELECT COUNT(DISTINCT ur.user_id)
            FROM user_roles ur
            INNER JOIN roles r ON r.id = ur.role_id
            INNER JOIN users u ON u.id = ur.user_id AND u.deleted_at IS NULL
            WHERE r.slug = \'client\'
        ');

        $properties = $this->scalarRow('
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = \'approved\' THEN 1 ELSE 0 END) AS published,
                SUM(CASE WHEN status = \'pending\' THEN 1 ELSE 0 END) AS pending
            FROM properties
            WHERE deleted_at IS NULL
        ');

        $lands = $this->scalarRow('
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = \'approved\' THEN 1 ELSE 0 END) AS published,
                SUM(CASE WHEN land_type = \'residentiel\' THEN 1 ELSE 0 END) AS residential,
                SUM(CASE WHEN land_type = \'agricole\' THEN 1 ELSE 0 END) AS agricultural,
                SUM(CASE WHEN land_type = \'commercial\' THEN 1 ELSE 0 END) AS commercial
            FROM lands
            WHERE deleted_at IS NULL
        ');

        $bookings = $this->scalarRow('
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status IN (\'confirmed\', \'completed\') AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN 1 ELSE 0 END) AS month_count,
                COALESCE(SUM(CASE WHEN status IN (\'confirmed\', \'completed\') AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN total_amount ELSE 0 END), 0) AS month_revenue
            FROM bookings
        ');

        $contracts = $this->scalarRow('
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = \'active\' THEN 1 ELSE 0 END) AS active
            FROM contracts
        ');

        $overduePayments = 0;
        try {
            $overduePayments = $this->scalar('
                SELECT COUNT(*)
                FROM contract_payments
                WHERE status IN (\'pending\', \'overdue\')
                  AND due_date < CURDATE()
            ');
        } catch (\Throwable) {
            $overduePayments = 0;
        }

        $reportsPending = 0;
        try {
            $reportsPending = $this->scalar('SELECT COUNT(*) FROM reports WHERE status = \'pending\'');
        } catch (\Throwable) {
            $reportsPending = 0;
        }

        $visitsPending = 0;
        try {
            $visitsPending = $this->scalar('SELECT COUNT(*) FROM land_visits WHERE status = \'pending\'');
        } catch (\Throwable) {
            $visitsPending = 0;
        }

        $constructionActive = 0;
        try {
            $constructionActive = $this->scalar('
                SELECT COUNT(*) FROM construction_projects
                WHERE status IN (\'planning\', \'chantier\', \'livraison\')
            ');
        } catch (\Throwable) {
            $constructionActive = 0;
        }

        return [
            'establishments_total' => (int) ($establishments['total'] ?? 0),
            'establishments_active' => (int) ($establishments['active'] ?? 0),
            'users_total' => (int) ($users['total'] ?? 0),
            'users_active' => (int) ($users['active'] ?? 0),
            'clients_total' => $clients,
            'properties_total' => (int) ($properties['total'] ?? 0),
            'properties_published' => (int) ($properties['published'] ?? 0),
            'properties_pending' => (int) ($properties['pending'] ?? 0),
            'lands_total' => (int) ($lands['total'] ?? 0),
            'lands_published' => (int) ($lands['published'] ?? 0),
            'lands_residential' => (int) ($lands['residential'] ?? 0),
            'lands_agricultural' => (int) ($lands['agricultural'] ?? 0),
            'lands_commercial' => (int) ($lands['commercial'] ?? 0),
            'bookings_total' => (int) ($bookings['total'] ?? 0),
            'bookings_month' => (int) ($bookings['month_count'] ?? 0),
            'bookings_revenue_month' => (float) ($bookings['month_revenue'] ?? 0),
            'contracts_active' => (int) ($contracts['active'] ?? 0),
            'contracts_total' => (int) ($contracts['total'] ?? 0),
            'payments_overdue' => $overduePayments,
            'reports_pending' => $reportsPending,
            'visits_pending' => $visitsPending,
            'construction_active' => $constructionActive,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function businessModules(): array
    {
        $stats = $this->stats();

        return [
            [
                'key' => 'users',
                'href' => url('/admin/users'),
                'icon' => 'people-fill',
                'tone' => 'navy',
                'title' => __('admin.nav_users'),
                'description' => __('admin.module.users_desc'),
                'metric' => (int) ($stats['clients_total'] ?? 0),
                'metric_label' => __('admin.module.users_metric'),
            ],
            [
                'key' => 'houses',
                'href' => url('/host/properties/new'),
                'icon' => 'houses-fill',
                'tone' => 'blue',
                'title' => __('admin.module.houses'),
                'description' => __('admin.module.houses_publish_desc'),
                'metric' => null,
                'metric_label' => __('admin.module.publish_action'),
            ],
            [
                'key' => 'lands',
                'href' => url('/host/properties/new'),
                'icon' => 'map',
                'tone' => 'green',
                'title' => __('admin.module.lands'),
                'description' => __('admin.module.lands_publish_desc'),
                'metric' => null,
                'metric_label' => __('admin.module.publish_action'),
            ],
            [
                'key' => 'rentals',
                'href' => url('/admin/rentals'),
                'icon' => 'file-earmark-text',
                'tone' => 'blue',
                'title' => __('admin.module.rentals'),
                'description' => __('admin.module.rentals_desc'),
                'metric' => (int) ($stats['contracts_active'] ?? 0),
                'metric_label' => __('admin.module.rentals_metric'),
            ],
            [
                'key' => 'furnished',
                'href' => url('/host'),
                'icon' => 'house-heart',
                'tone' => 'purple',
                'title' => __('admin.module.furnished'),
                'description' => __('admin.module.furnished_publish_desc'),
                'metric' => (int) ($stats['bookings_month'] ?? 0),
                'metric_label' => __('admin.module.furnished_metric'),
            ],
            [
                'key' => 'construction',
                'href' => url('/admin/construction'),
                'icon' => 'bricks',
                'tone' => 'orange',
                'title' => __('admin.module.construction'),
                'description' => __('admin.module.construction_desc'),
                'metric' => (int) ($stats['construction_active'] ?? 0),
                'metric_label' => __('admin.module.construction_metric'),
            ],
            [
                'key' => 'reports',
                'href' => url('/admin/reports'),
                'icon' => 'flag-fill',
                'tone' => 'orange',
                'title' => __('admin.nav_reports'),
                'description' => __('admin.module.reports_desc'),
                'metric' => (int) ($stats['reports_pending'] ?? 0),
                'metric_label' => __('admin.module.reports_metric'),
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function recentBookings(int $limit = 5): array
    {
        $limit = max(1, min($limit, 20));
        $stmt = $this->db->query('
            SELECT
                b.id,
                b.check_in,
                b.check_out,
                b.total_amount,
                b.currency,
                b.status,
                p.title,
                p.city,
                u.first_name,
                u.last_name
            FROM bookings b
            INNER JOIN properties p ON p.id = b.property_id
            INNER JOIN users u ON u.id = b.user_id
            ORDER BY b.created_at DESC
            LIMIT ' . $limit . '
        ');

        return $stmt->fetchAll() ?: [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function establishments(int $limit = 50): array
    {
        $limit = max(1, min($limit, 100));
        $stmt = $this->db->query('
            SELECT e.*, u.first_name, u.last_name, u.email
            FROM establishments e
            INNER JOIN users u ON u.id = e.owner_id
            ORDER BY e.created_at DESC
            LIMIT ' . $limit . '
        ');

        return $stmt->fetchAll() ?: [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function users(int $limit = 50): array
    {
        $limit = max(1, min($limit, 100));
        $stmt = $this->db->query('
            SELECT u.id, u.first_name, u.last_name, u.email, u.phone, u.status, u.created_at,
                   GROUP_CONCAT(r.name ORDER BY r.name SEPARATOR \', \') AS roles
            FROM users u
            LEFT JOIN user_roles ur ON ur.user_id = u.id
            LEFT JOIN roles r ON r.id = ur.role_id
            WHERE u.deleted_at IS NULL
            GROUP BY u.id
            ORDER BY u.created_at DESC
            LIMIT ' . $limit . '
        ');

        return $stmt->fetchAll() ?: [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function admins(int $limit = 50): array
    {
        $limit = max(1, min($limit, 100));
        $stmt = $this->db->query('
            SELECT u.id, u.first_name, u.last_name, u.email, u.phone, u.status, u.created_at, u.last_login_at
            FROM users u
            INNER JOIN user_roles ur ON ur.user_id = u.id
            INNER JOIN roles r ON r.id = ur.role_id AND r.slug = \'admin\'
            WHERE u.deleted_at IS NULL
            ORDER BY u.created_at DESC
            LIMIT ' . $limit . '
        ');

        return $stmt->fetchAll() ?: [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function properties(int $limit = 50): array
    {
        $limit = max(1, min($limit, 100));
        $stmt = $this->db->query('
            SELECT p.id, p.title, p.type, p.city, p.status, p.created_at,
                   u.first_name, u.last_name, e.name AS establishment_name,
                   pp.price_per_night, pp.price_per_month, pp.currency AS price_currency,
                   (
                       SELECT pi.path
                       FROM property_images pi
                       WHERE pi.property_id = p.id
                       ORDER BY pi.is_primary DESC, pi.sort_order ASC
                       LIMIT 1
                   ) AS primary_image,
                   (
                       SELECT COUNT(*)
                       FROM property_images pi2
                       WHERE pi2.property_id = p.id
                   ) AS image_count
            FROM properties p
            INNER JOIN users u ON u.id = p.owner_id
            LEFT JOIN establishments e ON e.id = p.establishment_id
            LEFT JOIN property_prices pp ON pp.property_id = p.id
            WHERE p.deleted_at IS NULL
            ORDER BY p.created_at DESC
            LIMIT ' . $limit . '
        ');

        return $stmt->fetchAll() ?: [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function lands(int $limit = 50): array
    {
        $limit = max(1, min($limit, 100));
        $stmt = $this->db->query('
            SELECT l.id, l.title, l.land_type, l.city, l.price, l.currency, l.status, l.created_at,
                   l.area, l.area_unit,
                   u.first_name, u.last_name,
                   (
                       SELECT li.path
                       FROM land_images li
                       WHERE li.land_id = l.id
                       ORDER BY li.is_primary DESC, li.sort_order ASC
                       LIMIT 1
                   ) AS primary_image,
                   (
                       SELECT COUNT(*)
                       FROM land_images li2
                       WHERE li2.land_id = l.id
                   ) AS image_count
            FROM lands l
            INNER JOIN users u ON u.id = l.seller_id
            WHERE l.deleted_at IS NULL
            ORDER BY l.created_at DESC
            LIMIT ' . $limit . '
        ');

        return $stmt->fetchAll() ?: [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function contracts(int $limit = 50): array
    {
        $limit = max(1, min($limit, 100));
        try {
            $stmt = $this->db->query('
                SELECT c.id, c.start_date, c.end_date, c.monthly_amount, c.status,
                       p.title AS property_title,
                       owner.first_name AS owner_first_name, owner.last_name AS owner_last_name,
                       tenant.first_name AS tenant_first_name, tenant.last_name AS tenant_last_name
                FROM contracts c
                INNER JOIN properties p ON p.id = c.property_id
                INNER JOIN users owner ON owner.id = c.owner_id
                INNER JOIN users tenant ON tenant.id = c.tenant_id
                ORDER BY c.created_at DESC
                LIMIT ' . $limit . '
            ');

            return $stmt->fetchAll() ?: [];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function bookings(int $limit = 50): array
    {
        $limit = max(1, min($limit, 100));
        $stmt = $this->db->query('
            SELECT b.id, b.property_id, b.check_in, b.check_out, b.subtotal, b.commission_rate,
                   b.commission_amount, b.total_amount, b.currency, b.status,
                   p.title, p.city, p.type,
                   u.first_name, u.last_name,
                   (
                       SELECT pi.path
                       FROM property_images pi
                       WHERE pi.property_id = p.id
                       ORDER BY pi.is_primary DESC, pi.sort_order ASC
                       LIMIT 1
                   ) AS primary_image,
                   (
                       SELECT COUNT(*)
                       FROM property_images pi2
                       WHERE pi2.property_id = p.id
                   ) AS image_count
            FROM bookings b
            INNER JOIN properties p ON p.id = b.property_id
            INNER JOIN users u ON u.id = b.user_id
            ORDER BY b.created_at DESC
            LIMIT ' . $limit . '
        ');

        return $stmt->fetchAll() ?: [];
    }

    /**
     * @return array<string, int|float>
     */
    public function bookingStats(): array
    {
        $row = $this->scalarRow('
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = \'pending\' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = \'confirmed\' THEN 1 ELSE 0 END) AS confirmed,
                SUM(CASE WHEN status IN (\'confirmed\', \'completed\') AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN 1 ELSE 0 END) AS month_count,
                COALESCE(SUM(CASE WHEN status IN (\'confirmed\', \'completed\') AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN total_amount ELSE 0 END), 0) AS month_revenue,
                COALESCE(SUM(CASE WHEN status IN (\'confirmed\', \'completed\') AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN commission_amount ELSE 0 END), 0) AS month_commission,
                COALESCE(SUM(CASE WHEN status IN (\'confirmed\', \'completed\') AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN (subtotal - commission_amount) ELSE 0 END), 0) AS month_owner_net,
                COALESCE(SUM(CASE WHEN status IN (\'confirmed\', \'completed\') THEN commission_amount ELSE 0 END), 0) AS total_commission
            FROM bookings
        ');

        return [
            'total' => (int) ($row['total'] ?? 0),
            'pending' => (int) ($row['pending'] ?? 0),
            'confirmed' => (int) ($row['confirmed'] ?? 0),
            'month_count' => (int) ($row['month_count'] ?? 0),
            'month_revenue' => (float) ($row['month_revenue'] ?? 0),
            'month_commission' => (float) ($row['month_commission'] ?? 0),
            'month_owner_net' => (float) ($row['month_owner_net'] ?? 0),
            'total_commission' => (float) ($row['total_commission'] ?? 0),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function reports(int $limit = 50): array
    {
        $limit = max(1, min($limit, 100));
        try {
            $stmt = $this->db->query('
                SELECT r.*, u.first_name, u.last_name
                FROM reports r
                INNER JOIN users u ON u.id = r.reporter_id
                ORDER BY r.created_at DESC
                LIMIT ' . $limit . '
            ');

            return $stmt->fetchAll() ?: [];
        } catch (\Throwable) {
            return [];
        }
    }

  /**
     * @return array<string, int|float>
     */
    private function scalarRow(string $sql): array
    {
        $stmt = $this->db->query($sql);
        $row = $stmt->fetch();

        return is_array($row) ? $row : [];
    }

    private function scalar(string $sql): int
    {
        $stmt = $this->db->query($sql);

        return (int) $stmt->fetchColumn();
    }
}
