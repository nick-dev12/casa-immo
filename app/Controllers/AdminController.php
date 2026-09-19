<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Helpers\AuthHelper;
use App\Models\ConstructionProject;
use App\Models\Contract;
use App\Models\ContractImage;
use App\Models\Establishment;
use App\Models\Report;
use App\Models\User;
use App\Services\AdminAgencyService;
use App\Services\AdminDashboardService;
use App\Services\AdminReportService;
use App\Services\AdminUserService;
use App\Services\AdminModerationService;
use App\Services\ContractDocumentService;

final class AdminController extends Controller
{
    public function index(): string
    {
        $user = $this->requireAdmin();
        $dashboard = (new AdminDashboardService())->overview();

        return $this->adminView('admin/index', [
            'title' => __('admin.dashboard_title'),
            'user' => $user,
            'stats' => $dashboard['stats'],
            'modules' => $dashboard['modules'],
            'recentBookings' => $dashboard['recent_bookings'],
        ]);
    }

    public function agencies(): string
    {
        $this->requireAdmin();
        $service = new AdminAgencyService();

        return $this->adminView('admin/agencies', [
            'title' => __('admin.nav_agencies'),
            'pageTitle' => __('admin.nav_agencies'),
            'stats' => $service->stats(),
            'items' => $service->allForAdmin(),
            'error' => flash('admin_error'),
            'success' => flash('admin_success'),
        ]);
    }

    public function showAgency(string $id): string
    {
        $this->requireAdmin();
        $agencyId = (int) $id;
        $service = new AdminAgencyService();
        $agency = $service->findForAdmin($agencyId);

        if ($agency === null) {
            Session::flash('admin_error', __('admin.agency.error_not_found'));
            $this->redirect(url('/admin/agencies'));
        }

        return $this->adminView('admin/agencies/show', [
            'title' => (string) ($agency['name'] ?? __('admin.nav_agencies')),
            'pageTitle' => '',
            'agency' => $agency,
            'properties' => $service->propertiesForAgency($agencyId),
            'error' => flash('admin_error'),
            'success' => flash('admin_success'),
        ]);
    }

    public function activateAgency(string $id): never
    {
        $this->requireAdmin();
        $this->updateAgencyStatus((int) $id, 'active', __('admin.agency.activated'));
    }

    public function deactivateAgency(string $id): never
    {
        $this->requireAdmin();
        $this->updateAgencyStatus((int) $id, 'suspended', __('admin.agency.deactivated'));
    }

    public function deleteAgency(string $id): never
    {
        $this->requireAdmin();
        $agencyId = (int) $id;
        $service = new AdminAgencyService();

        if ($service->findForAdmin($agencyId) === null) {
            Session::flash('admin_error', __('admin.agency.error_not_found'));
            $this->redirect(url('/admin/agencies'));
        }

        if (!$service->deleteAgency($agencyId)) {
            Session::flash('admin_error', __('admin.agency.error_delete'));
            $this->redirect(url('/admin/agencies'));
        }

        Session::flash('admin_success', __('admin.agency.deleted'));
        $this->redirect(url('/admin/agencies'));
    }

    public function users(): string
    {
        $current = $this->requireAdmin();
        $service = new AdminUserService();

        return $this->adminView('admin/users', [
            'title' => __('admin.nav_users'),
            'pageTitle' => __('admin.nav_users'),
            'stats' => $service->stats(),
            'items' => $service->allForAdmin(),
            'currentUserId' => (int) $current['id'],
            'error' => flash('admin_error'),
            'success' => flash('admin_success'),
        ]);
    }

    public function showUser(string $id): string
    {
        $current = $this->requireAdmin();
        $userId = (int) $id;
        $service = new AdminUserService();
        $user = $service->findForAdmin($userId);

        if ($user === null) {
            Session::flash('admin_error', __('admin.user.error_not_found'));
            $this->redirect(url('/admin/users'));
        }

        return $this->adminView('admin/users/show', [
            'title' => trim((string) ($user['first_name'] ?? '') . ' ' . (string) ($user['last_name'] ?? '')),
            'pageTitle' => '',
            'user' => $user,
            'properties' => $service->propertiesForUser($userId),
            'lands' => $service->landsForUser($userId),
            'isProtected' => $service->hasAdminRole($user) || $userId === (int) $current['id'],
            'currentUserId' => (int) $current['id'],
            'error' => flash('admin_error'),
            'success' => flash('admin_success'),
        ]);
    }

    public function activateUser(string $id): never
    {
        $this->requireAdmin();
        $this->updatePlatformUserStatus((int) $id, 'active', __('admin.user.activated'));
    }

    public function deactivateUser(string $id): never
    {
        $this->requireAdmin();
        $this->updatePlatformUserStatus((int) $id, 'inactive', __('admin.user.deactivated'));
    }

    public function deleteUser(string $id): never
    {
        $this->requireAdmin();
        $current = $this->requireAdmin();
        $userId = (int) $id;
        $service = new AdminUserService();
        $user = $service->findForAdmin($userId);

        if ($user === null) {
            Session::flash('admin_error', __('admin.user.error_not_found'));
            $this->redirect(url('/admin/users'));
        }

        if ($userId === (int) $current['id']) {
            Session::flash('admin_error', __('admin.user.error_self'));
            $this->redirect(url('/admin/users/' . $userId));
        }

        if ($service->hasAdminRole($user)) {
            Session::flash('admin_error', __('admin.user.error_admin_protected'));
            $this->redirect(url('/admin/users/' . $userId));
        }

        if (!(new User())->softDelete($userId)) {
            Session::flash('admin_error', __('admin.user.error_delete'));
            $this->redirect(url('/admin/users/' . $userId));
        }

        Session::flash('admin_success', __('admin.user.deleted'));
        $this->redirect(url('/admin/users'));
    }

    public function properties(): string
    {
        $this->requireAdmin();

        return $this->adminView('admin/properties', [
            'title' => __('admin.nav_properties'),
            'pageTitle' => __('admin.nav_properties'),
            'items' => (new AdminDashboardService())->properties(),
        ]);
    }

    public function lands(): string
    {
        $this->requireAdmin();

        return $this->adminView('admin/lands', [
            'title' => __('admin.nav_lands'),
            'pageTitle' => __('admin.nav_lands'),
            'items' => (new AdminDashboardService())->lands(),
        ]);
    }

    public function rentals(): string
    {
        $this->requireAdmin();
        $model = new Contract();

        return $this->adminView('admin/rentals/index', [
            'title' => __('admin.nav_rentals'),
            'pageTitle' => __('admin.nav_rentals'),
            'items' => $model->allForAdmin(100),
            'stats' => $model->adminStats(),
            'success' => flash('admin_success'),
            'error' => flash('admin_error'),
        ]);
    }

    public function showRental(string $id): string
    {
        $this->requireAdmin();
        $contractId = (int) $id;
        $model = new Contract();
        $contract = $model->findForAdmin($contractId);

        if ($contract === null) {
            Session::flash('admin_error', __('admin.rental.error_not_found'));
            $this->redirect(url('/admin/rentals'));
        }

        return $this->adminView('admin/rentals/show', [
            'title' => __('admin.rental.dossier.kicker'),
            'pageTitle' => '',
            'contract' => $contract,
            'propertyPhotos' => (new ContractImage())->forContract($contractId),
            'cardStatus' => rental_card_status($contract),
        ]);
    }

    public function activateRental(string $id): never
    {
        $this->requireAdmin();
        $this->updateRentalStatus((int) $id, 'active', __('admin.rental.activated'));
    }

    public function deactivateRental(string $id): never
    {
        $this->requireAdmin();
        $this->updateRentalStatus((int) $id, 'terminated', __('admin.rental.deactivated'));
    }

    public function deleteRental(string $id): never
    {
        $this->requireAdmin();
        $contractId = (int) $id;
        $model = new Contract();

        if ($model->findForAdmin($contractId) === null) {
            Session::flash('admin_error', __('admin.rental.error_not_found'));
            $this->redirect(url('/admin/rentals'));
        }

        if (!$model->deleteContract($contractId)) {
            Session::flash('admin_error', __('admin.rental.error_delete'));
            $this->redirect(url('/admin/rentals'));
        }

        Session::flash('admin_success', __('admin.rental.deleted'));
        $this->redirect(url('/admin/rentals'));
    }

    public function createRental(): string
    {
        $this->requireAdmin();
        $model = new Contract();

        return $this->adminView('admin/rentals/form', [
            'title' => __('admin.rental_new'),
            'pageTitle' => __('admin.rental_new'),
            'properties' => $model->linkableProperties(),
            'error' => flash('admin_error'),
        ]);
    }

    public function storeRental(): never
    {
        $this->requireAdmin();

        $tenantLastName = trim((string) $this->request->input('tenant_last_name', ''));
        $tenantFirstName = trim((string) $this->request->input('tenant_first_name', ''));
        $tenantEmail = trim((string) $this->request->input('tenant_email', ''));
        $tenantPhone = trim((string) $this->request->input('tenant_phone', ''));
        $propertyId = (int) $this->request->input('property_id', 0);
        $startDate = trim((string) $this->request->input('start_date', ''));
        $endDate = trim((string) $this->request->input('end_date', ''));
        $contractType = (string) $this->request->input('contract_type', 'habitation');
        $maritalStatus = trim((string) $this->request->input('marital_status', ''));
        $monthlyRaw = trim((string) $this->request->input('monthly_amount', ''));
        $depositRaw = trim((string) $this->request->input('deposit_amount', ''));
        $rentalDuration = (string) $this->request->input('rental_duration', 'long');
        $depositMonths = (int) $this->request->input('deposit_months', 2);
        $notes = trim((string) $this->request->input('notes', ''));

        $pdfFile = $_FILES['contract_pdf'] ?? null;

        if ($tenantLastName === '' || $tenantFirstName === '') {
            Session::flash('admin_error', __('admin.rental.error_required'));
            $this->redirect(url('/admin/rentals/new'));
        }

        if ($tenantEmail !== '' && !filter_var($tenantEmail, FILTER_VALIDATE_EMAIL)) {
            Session::flash('admin_error', __('admin.rental.error_email'));
            $this->redirect(url('/admin/rentals/new'));
        }

        if ($propertyId <= 0 || $startDate === '' || $endDate === '') {
            Session::flash('admin_error', __('admin.rental.error_required'));
            $this->redirect(url('/admin/rentals/new'));
        }

        if ($startDate > $endDate) {
            Session::flash('admin_error', __('admin.rental.error_dates'));
            $this->redirect(url('/admin/rentals/new'));
        }

        if ($monthlyRaw === '' || (float) $monthlyRaw <= 0) {
            Session::flash('admin_error', __('admin.rental.error_rent'));
            $this->redirect(url('/admin/rentals/new'));
        }

        if ($pdfFile === null || ($pdfFile['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            Session::flash('admin_error', __('admin.rental.error_contract_required'));
            $this->redirect(url('/admin/rentals/new'));
        }

        if (!in_array($contractType, Contract::TYPES, true)) {
            $contractType = 'habitation';
        }

        if (!in_array($rentalDuration, Contract::DURATIONS, true)) {
            $rentalDuration = 'long';
        }

        if (!in_array($depositMonths, Contract::DEPOSIT_MONTHS, true)) {
            $depositMonths = 2;
        }

        $contractModel = new Contract();
        $property = $contractModel->findProperty($propertyId);
        if ($property === null) {
            Session::flash('admin_error', __('admin.rental.error_property'));
            $this->redirect(url('/admin/rentals/new'));
        }

        try {
            $tenantId = (new User())->findOrCreateTenant(
                $tenantFirstName,
                $tenantLastName,
                $tenantEmail !== '' ? $tenantEmail : null,
                $tenantPhone !== '' ? $tenantPhone : null
            );

            $termsParts = [];
            if ($maritalStatus !== '') {
                $termsParts[] = __('admin.rental.marital_status') . ' : ' . $maritalStatus;
            }
            if ($notes !== '') {
                $termsParts[] = $notes;
            }

            $depositAmount = $depositRaw !== '' ? (float) $depositRaw : 0.0;
            if ($depositAmount <= 0) {
                $depositAmount = (float) $monthlyRaw * $depositMonths;
            }

            $contractId = $contractModel->create([
                'property_id' => $propertyId,
                'owner_id' => (int) $property['owner_id'],
                'tenant_id' => $tenantId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'monthly_amount' => (float) $monthlyRaw,
                'deposit_amount' => $depositAmount,
                'deposit_months' => $depositMonths,
                'terms' => $termsParts !== [] ? implode("\n", $termsParts) : null,
                'contract_type' => $contractType,
                'rental_duration' => $rentalDuration,
                'status' => 'draft',
                'pdf_path' => '',
                'tenant_photo_path' => null,
            ]);

            $docService = new ContractDocumentService();
            $pdfPath = $docService->storePdf(is_array($pdfFile) ? $pdfFile : null, $contractId);
            $propertyPhotoPaths = $docService->storePropertyPhotos($_FILES['property_photos'] ?? null, $contractId);
            if ($propertyPhotoPaths !== []) {
                (new ContractImage())->addMany($contractId, $propertyPhotoPaths);
            }
            $contractModel->finalize($contractId, $pdfPath, null);
        } catch (\Throwable) {
            Session::flash('admin_error', __('admin.rental.error_save'));
            $this->redirect(url('/admin/rentals/new'));
        }

        Session::flash('admin_success', __('admin.rental.created'));
        $this->redirect(url('/admin/rentals'));
    }

    public function bookings(): string
    {
        $this->requireAdmin();
        $service = new AdminDashboardService();

        return $this->adminView('admin/bookings', [
            'title' => __('admin.nav_bookings'),
            'pageTitle' => __('admin.nav_bookings'),
            'stats' => $service->bookingStats(),
            'items' => $service->bookings(),
        ]);
    }

    public function construction(): string
    {
        $user = $this->requireAdmin();
        $userId = (int) $user['id'];
        $model = new ConstructionProject();

        return $this->adminView('admin/construction/index', [
            'title' => __('admin.nav_construction'),
            'pageTitle' => __('admin.nav_construction'),
            'stats' => $model->statsForOwner($userId),
            'items' => $model->forOwner($userId),
            'success' => flash('admin_success'),
            'error' => flash('admin_error'),
        ]);
    }

    public function createConstruction(): string
    {
        $user = $this->requireAdmin();
        $userId = (int) $user['id'];

        return $this->adminView('admin/construction/form', [
            'title' => __('admin.construction_new'),
            'pageTitle' => __('admin.construction_new'),
            'project' => null,
            'milestones' => [],
            'properties' => (new ConstructionProject())->linkableProperties($userId),
            'isEdit' => false,
        ]);
    }

    public function storeConstruction(): never
    {
        $user = $this->requireAdmin();
        $userId = (int) $user['id'];
        $establishment = (new Establishment())->findByOwnerId($userId);
        $data = $this->constructionInput();

        if ($data === null) {
            $this->redirect(url('/admin/construction/new'));
        }

        $model = new ConstructionProject();
        $projectId = $model->create($userId, $establishment !== null ? (int) $establishment['id'] : null, $data);
        $model->syncMilestones($projectId, $this->milestoneInput());

        Session::flash('admin_success', __('admin.construction_created'));
        $this->redirect(url('/admin/construction'));
    }

    public function editConstruction(string $id): string
    {
        $user = $this->requireAdmin();
        $userId = (int) $user['id'];
        $projectId = (int) $id;
        $model = new ConstructionProject();
        $project = $model->findForOwner($projectId, $userId);

        if ($project === null) {
            Session::flash('admin_error', __('admin.construction_not_found'));
            $this->redirect(url('/admin/construction'));
        }

        return $this->adminView('admin/construction/form', [
            'title' => __('admin.construction_edit'),
            'pageTitle' => __('admin.construction_edit'),
            'project' => $project,
            'milestones' => $model->milestones($projectId),
            'properties' => $model->linkableProperties($userId),
            'isEdit' => true,
        ]);
    }

    public function updateConstruction(string $id): never
    {
        $user = $this->requireAdmin();
        $userId = (int) $user['id'];
        $projectId = (int) $id;
        $model = new ConstructionProject();

        if ($model->findForOwner($projectId, $userId) === null) {
            Session::flash('admin_error', __('admin.construction_not_found'));
            $this->redirect(url('/admin/construction'));
        }

        $data = $this->constructionInput();
        if ($data === null) {
            $this->redirect(url('/admin/construction/' . $projectId . '/edit'));
        }

        $model->update($projectId, $userId, $data);
        $model->syncMilestones($projectId, $this->milestoneInput());

        Session::flash('admin_success', __('admin.construction_updated'));
        $this->redirect(url('/admin/construction'));
    }

    public function deleteConstruction(string $id): never
    {
        $user = $this->requireAdmin();
        $userId = (int) $user['id'];
        $projectId = (int) $id;
        $model = new ConstructionProject();

        if (!$model->delete($projectId, $userId)) {
            Session::flash('admin_error', __('admin.construction_not_found'));
        } else {
            Session::flash('admin_success', __('admin.construction_deleted'));
        }

        $this->redirect(url('/admin/construction'));
    }

    public function reports(): string
    {
        $this->requireAdmin();
        $service = new AdminReportService();

        return $this->adminView('admin/reports', [
            'title' => __('admin.nav_reports'),
            'pageTitle' => __('admin.nav_reports'),
            'stats' => $service->stats(),
            'items' => $service->allForAdmin(),
            'success' => flash('admin_success'),
            'error' => flash('admin_error'),
        ]);
    }

    public function showReport(string $id): string
    {
        $this->requireAdmin();
        $reportId = (int) $id;
        $service = new AdminReportService();
        $report = $service->findForAdmin($reportId);

        if ($report === null) {
            Session::flash('admin_error', __('admin.report_not_found'));
            $this->redirect(url('/admin/reports'));
        }

        return $this->adminView('admin/reports/show', [
            'title' => __('admin.report.dossier.kicker'),
            'pageTitle' => '',
            'report' => $report,
            'listingUrl' => $service->listingAdminUrl($report),
            'success' => flash('admin_success'),
            'error' => flash('admin_error'),
        ]);
    }

    public function updateReport(string $id): never
    {
        $this->requireAdmin();
        $reportId = (int) $id;
        $status = (string) $this->request->input('status', '');

        $model = new Report();
        if ($model->findById($reportId) === null) {
            Session::flash('admin_error', __('admin.report_not_found'));
            $this->redirect(url('/admin/reports'));
        }

        if (!$model->updateStatus($reportId, $status)) {
            Session::flash('admin_error', __('admin.report_invalid_status'));
            $this->redirect(url('/admin/reports'));
        }

        Session::flash('admin_success', __('admin.report_updated'));
        $redirect = trim((string) $this->request->input('redirect_to', ''));
        if ($redirect !== '' && str_starts_with($redirect, '/admin/reports')) {
            $this->redirect(url($redirect));
        }

        $this->redirect(url('/admin/reports/' . $reportId));
    }

    public function showProperty(string $id): string
    {
        $this->requireAdmin();
        $propertyId = (int) $id;
        $service = new AdminModerationService();
        $listing = $service->propertyDetail($propertyId);

        if ($listing === null) {
            Session::flash('admin_error', __('admin.moderation.not_found'));
            $this->redirect(url('/admin/properties'));
        }

        return $this->listingDetailView('property', $listing, $service);
    }

    public function showLand(string $id): string
    {
        $this->requireAdmin();
        $landId = (int) $id;
        $service = new AdminModerationService();
        $listing = $service->landDetail($landId);

        if ($listing === null) {
            Session::flash('admin_error', __('admin.moderation.not_found'));
            $this->redirect(url('/admin/lands'));
        }

        return $this->listingDetailView('land', $listing, $service);
    }

    public function moderateListing(): never
    {
        $admin = $this->requireAdmin();
        $adminId = (int) $admin['id'];
        $type = (string) $this->request->input('target_type', '');
        $targetId = (int) $this->request->input('target_id', 0);
        $action = (string) $this->request->input('action', '');
        $motives = $this->request->input('motives', []);
        $notes = trim((string) $this->request->input('notes', ''));

        if (!is_array($motives)) {
            $motives = [];
        }

        $motives = array_values(array_filter(array_map('strval', $motives)));

        $service = new AdminModerationService();
        if (!$service->applyAction($adminId, $type, $targetId, $action, $motives, $notes)) {
            Session::flash('admin_error', __('admin.moderation.error'));
        } else {
            Session::flash('admin_success', __('admin.moderation.success.' . $action));
        }

        $redirect = $type === 'land'
            ? url('/admin/lands/' . $targetId)
            : url('/admin/properties/' . $targetId);
        $this->redirect($redirect);
    }

    public function settings(): string
    {
        $user = $this->requireAdmin();

        return $this->adminView('admin/settings', [
            'title' => __('admin.nav_admins'),
            'pageTitle' => __('admin.nav_admins'),
            'items' => (new AdminDashboardService())->admins(),
            'currentUserId' => (int) $user['id'],
            'success' => flash('admin_success'),
            'error' => flash('admin_error'),
        ]);
    }

    public function createAdmin(): string
    {
        $this->requireAdmin();

        return $this->adminView('admin/settings/form', [
            'title' => __('admin.admins_create_title'),
            'pageTitle' => __('admin.admins_create_title'),
            'error' => flash('admin_error'),
        ]);
    }

    public function storeAdmin(): never
    {
        $this->requireAdmin();

        $firstName = trim((string) $this->request->input('first_name', ''));
        $lastName = trim((string) $this->request->input('last_name', ''));
        $email = trim((string) $this->request->input('email', ''));
        $phone = trim((string) $this->request->input('phone', ''));
        $password = (string) $this->request->input('password', '');
        $passwordConfirm = (string) $this->request->input('password_confirm', '');

        if ($firstName === '' || $lastName === '' || $email === '') {
            Session::flash('admin_error', __('admin.admins_error_required'));
            $this->redirect(url('/admin/settings/new'));
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('admin_error', __('admin.admins_error_email'));
            $this->redirect(url('/admin/settings/new'));
        }

        if (strlen($password) < 8) {
            Session::flash('admin_error', __('admin.admins_error_password'));
            $this->redirect(url('/admin/settings/new'));
        }

        if ($password !== $passwordConfirm) {
            Session::flash('admin_error', __('admin.admins_error_password_match'));
            $this->redirect(url('/admin/settings/new'));
        }

        $user = (new User())->createAdmin(
            $firstName,
            $lastName,
            $email,
            $password,
            $phone !== '' ? $phone : null
        );

        if ($user === null) {
            Session::flash('admin_error', __('admin.admins_error_exists'));
            $this->redirect(url('/admin/settings/new'));
        }

        Session::flash('admin_success', __('admin.admins_created'));
        $this->redirect(url('/admin/settings'));
    }

    public function deactivateAdmin(string $id): never
    {
        $current = $this->requireAdmin();
        $this->updateAdminAccountStatus((int) $id, (int) $current['id'], 'inactive');
    }

    public function activateAdmin(string $id): never
    {
        $current = $this->requireAdmin();
        $this->updateAdminAccountStatus((int) $id, (int) $current['id'], 'active');
    }

    public function deleteAdmin(string $id): never
    {
        $current = $this->requireAdmin();
        $targetId = (int) $id;
        $currentId = (int) $current['id'];

        if ($targetId === $currentId) {
            Session::flash('admin_error', __('admin.admins_error_self'));
            $this->redirect(url('/admin/settings'));
        }

        $userModel = new User();
        $admin = $userModel->findAdminById($targetId);
        if ($admin === null) {
            Session::flash('admin_error', __('admin.admins_error_not_found'));
            $this->redirect(url('/admin/settings'));
        }

        if ((string) ($admin['status'] ?? '') === 'active' && $userModel->countActiveAdmins() <= 1) {
            Session::flash('admin_error', __('admin.admins_error_last'));
            $this->redirect(url('/admin/settings'));
        }

        $userModel->removeRole($targetId, 'admin');
        $userModel->softDelete($targetId);

        Session::flash('admin_success', __('admin.admins_deleted'));
        $this->redirect(url('/admin/settings'));
    }

    /**
     * @param array<string, mixed> $data
     */
    private function adminView(string $view, array $data): string
    {
        $userId = AuthHelper::id();
        $navBadges = ['pending' => 0, 'reports' => 0, 'overdue' => 0];

        if ($userId !== null) {
            try {
                $stats = (new AdminDashboardService())->stats();
                $navBadges = [
                    'pending' => (int) ($stats['properties_pending'] ?? 0),
                    'reports' => (int) ($stats['reports_pending'] ?? 0),
                    'overdue' => (int) ($stats['payments_overdue'] ?? 0),
                ];
            } catch (\Throwable) {
                // Badges optionnels.
            }
        }

        return $this->view($view, array_merge($data, [
            'isAdminPage' => true,
            'navBadges' => $data['navBadges'] ?? $navBadges,
            'adminEstablishment' => (new \App\Models\Establishment())->findByOwnerId((int) AuthHelper::id()),
        ]));
    }

    private function updateAdminAccountStatus(int $targetId, int $currentId, string $status): never
    {
        if ($targetId === $currentId && $status !== 'active') {
            Session::flash('admin_error', __('admin.admins_error_self'));
            $this->redirect(url('/admin/settings'));
        }

        $userModel = new User();
        $admin = $userModel->findAdminById($targetId);
        if ($admin === null) {
            Session::flash('admin_error', __('admin.admins_error_not_found'));
            $this->redirect(url('/admin/settings'));
        }

        if ($status === 'inactive'
            && (string) ($admin['status'] ?? '') === 'active'
            && $userModel->countActiveAdmins() <= 1) {
            Session::flash('admin_error', __('admin.admins_error_last'));
            $this->redirect(url('/admin/settings'));
        }

        $userModel->setStatus($targetId, $status);

        Session::flash(
            'admin_success',
            $status === 'active' ? __('admin.admins_activated') : __('admin.admins_deactivated')
        );
        $this->redirect(url('/admin/settings'));
    }

    private function updatePlatformUserStatus(int $userId, string $status, string $successMessage): never
    {
        $current = $this->requireAdmin();
        $service = new AdminUserService();
        $user = $service->findForAdmin($userId);

        if ($user === null) {
            Session::flash('admin_error', __('admin.user.error_not_found'));
            $this->redirect(url('/admin/users'));
        }

        if ($userId === (int) $current['id']) {
            Session::flash('admin_error', __('admin.user.error_self'));
            $this->redirect(url('/admin/users/' . $userId));
        }

        if ($service->hasAdminRole($user)) {
            Session::flash('admin_error', __('admin.user.error_admin_protected'));
            $this->redirect(url('/admin/users/' . $userId));
        }

        if (!(new User())->setStatus($userId, $status)) {
            Session::flash('admin_error', __('admin.user.error_status'));
            $this->redirect(url('/admin/users'));
        }

        Session::flash('admin_success', $successMessage);
        $redirect = trim((string) $this->request->input('redirect_to', ''));
        if ($redirect !== '' && str_starts_with($redirect, '/admin/users')) {
            $this->redirect(url($redirect));
        }

        $this->redirect(url('/admin/users/' . $userId));
    }

    private function updateAgencyStatus(int $agencyId, string $status, string $successMessage): never
    {
        $service = new AdminAgencyService();
        if ($service->findForAdmin($agencyId) === null) {
            Session::flash('admin_error', __('admin.agency.error_not_found'));
            $this->redirect(url('/admin/agencies'));
        }

        if (!$service->updateStatus($agencyId, $status)) {
            Session::flash('admin_error', __('admin.agency.error_status'));
            $this->redirect(url('/admin/agencies'));
        }

        Session::flash('admin_success', $successMessage);
        $redirect = trim((string) $this->request->input('redirect_to', ''));
        if ($redirect !== '' && str_starts_with($redirect, '/admin/agencies')) {
            $this->redirect(url($redirect));
        }

        $this->redirect(url('/admin/agencies/' . $agencyId));
    }

    private function updateRentalStatus(int $contractId, string $status, string $successMessage): never
    {
        $model = new Contract();
        if ($model->findForAdmin($contractId) === null) {
            Session::flash('admin_error', __('admin.rental.error_not_found'));
            $this->redirect(url('/admin/rentals'));
        }

        if (!$model->updateStatus($contractId, $status)) {
            Session::flash('admin_error', __('admin.rental.error_status'));
            $this->redirect(url('/admin/rentals'));
        }

        Session::flash('admin_success', $successMessage);
        $this->redirect(url('/admin/rentals'));
    }

    /**
     * @return array<string, mixed>
     */
    private function requireAdmin(): array
    {
        if (!AuthHelper::check()) {
            Session::flash('auth_error', __('auth.login_required'));
            $this->redirect(url('/login?redirect=/admin'));
        }

        $user = AuthHelper::user();
        if ($user === null) {
            $this->redirect(url('/login?redirect=/admin'));
        }

        $userId = (int) $user['id'];
        if (!(new User())->hasRole($userId, 'admin')) {
            http_response_code(403);
            exit(__('admin.error.forbidden'));
        }

        return $user;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function constructionInput(): ?array
    {
        $title = trim((string) $this->request->input('title', ''));
        $city = trim((string) $this->request->input('city', ''));

        if ($title === '' || $city === '') {
            Session::flash('admin_error', __('admin.construction_error_required'));
            return null;
        }

        $type = (string) $this->request->input('project_type', 'maison_finition');
        if (!in_array($type, ConstructionProject::TYPES, true)) {
            $type = 'maison_finition';
        }

        $status = (string) $this->request->input('status', 'devis');
        if (!in_array($status, ConstructionProject::STATUSES, true)) {
            $status = 'devis';
        }

        $propertyId = (int) $this->request->input('property_id', 0);
        $quoteRaw = trim((string) $this->request->input('quote_amount', ''));
        $progress = max(0, min(100, (int) $this->request->input('progress', 0)));
        $startDate = trim((string) $this->request->input('start_date', ''));
        $deliveryDate = trim((string) $this->request->input('delivery_date', ''));

        return [
            'property_id' => $propertyId > 0 ? $propertyId : null,
            'title' => $title,
            'description' => trim((string) $this->request->input('description', '')),
            'project_type' => $type,
            'status' => $status,
            'city' => $city,
            'district' => trim((string) $this->request->input('district', '')),
            'address' => trim((string) $this->request->input('address', '')),
            'quote_amount' => $quoteRaw !== '' ? (float) $quoteRaw : null,
            'currency' => 'XOF',
            'start_date' => $startDate !== '' ? $startDate : null,
            'delivery_date' => $deliveryDate !== '' ? $deliveryDate : null,
            'progress' => $progress,
            'client_name' => trim((string) $this->request->input('client_name', '')),
            'client_phone' => trim((string) $this->request->input('client_phone', '')),
            'notes' => trim((string) $this->request->input('notes', '')),
        ];
    }

    /**
     * @return list<array{title: string, due_date: ?string, status: string}>
     */
    private function milestoneInput(): array
    {
        $titles = $this->request->input('milestone_title', []);
        $dates = $this->request->input('milestone_due_date', []);
        $statuses = $this->request->input('milestone_status', []);

        if (!is_array($titles)) {
            return [];
        }

        $milestones = [];
        foreach ($titles as $index => $title) {
            $title = trim((string) $title);
            if ($title === '') {
                continue;
            }

            $status = is_array($statuses) ? (string) ($statuses[$index] ?? 'pending') : 'pending';
            if (!in_array($status, ['pending', 'in_progress', 'done'], true)) {
                $status = 'pending';
            }

            $dueDate = is_array($dates) ? trim((string) ($dates[$index] ?? '')) : '';

            $milestones[] = [
                'title' => $title,
                'due_date' => $dueDate !== '' ? $dueDate : null,
                'status' => $status,
            ];
        }

        return $milestones;
    }

    /**
     * @param array<string, mixed> $listing
     */
    private function listingDetailView(string $type, array $listing, AdminModerationService $service): string
    {
        $listingId = (int) ($listing['id'] ?? 0);
        $backUrl = $type === 'land' ? url('/admin/lands') : url('/admin/properties');
        $publicUrl = $type === 'land'
            ? url('/lands/' . $listingId)
            : url('/properties/' . $listingId);

        return $this->adminView('admin/listings/show', [
            'title' => (string) ($listing['title'] ?? __('admin.moderation.detail_title')),
            'pageTitle' => (string) ($listing['title'] ?? __('admin.moderation.detail_title')),
            'listingType' => $type,
            'listing' => $listing,
            'images' => $service->imagesFor($type, $listingId),
            'motives' => $service->motiveLabels(),
            'history' => $service->historyFor($type, $listingId),
            'backUrl' => $backUrl,
            'publicUrl' => $publicUrl,
            'success' => flash('admin_success'),
            'error' => flash('admin_error'),
        ]);
    }
}
