<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Helpers\AuthHelper;
use App\Models\Booking;
use App\Models\Notification;
use App\Models\Property;
use App\Services\BookingNotificationService;
use App\Services\BookingInvoiceService;
use App\Services\BookingInvoicePdfService;
use App\Services\BookingReviewService;
use App\Models\Review;

final class ReservationsController extends Controller
{
    public function index(): string
    {
        if (!AuthHelper::check()) {
            $this->redirect(url('/login?redirect=/reservations'));
        }

        $user = AuthHelper::user();
        if ($user === null) {
            $this->redirect(url('/login?redirect=/reservations'));
        }

        $userId = (int) $user['id'];
        $bookingModel = new Booking();
        $bookings = $bookingModel->forUser($userId);
        $isHost = user_is_property_host($userId);
        $hostBookings = $isHost ? $bookingModel->forOwner($userId) : [];
        $unreadBookingIds = (new Notification())->unreadBookingIds($userId);
        $mode = trim((string) $this->request->input('mode', ''));

        if (!in_array($mode, ['guest', 'received'], true)) {
            $mode = ($bookings === [] && $hostBookings !== []) ? 'received' : 'guest';
        }

        if (!$isHost) {
            $mode = 'guest';
            $hostBookings = [];
        }

        return $this->view('reservations/index', [
            'title' => __('reservations.title'),
            'isReservations' => true,
            'isAccountPage' => true,
            'user' => $user,
            'bookings' => $bookings,
            'hostBookings' => $hostBookings,
            'isHost' => $isHost,
            'mode' => $mode,
            'unreadBookingIds' => $unreadBookingIds,
            'unreadReservationsCount' => unread_reservations_count(),
        ]);
    }

    public function show(string $id): string
    {
        if (!AuthHelper::check()) {
            $this->redirect(url('/login?redirect=/reservations/' . $id));
        }

        $user = AuthHelper::user();
        if ($user === null) {
            $this->redirect(url('/login?redirect=/reservations/' . $id));
        }

        $userId = (int) $user['id'];
        $bookingId = (int) $id;
        $bookingModel = new Booking();
        $isHostView = false;
        $booking = $bookingModel->findForUser($bookingId, $userId);

        if ($booking === null) {
            $booking = $bookingModel->findForOwner($bookingId, $userId);
            $isHostView = $booking !== null;
        }

        if ($booking === null) {
            $this->redirect(url('/reservations'));
        }

        (new Notification())->markReadByBooking($userId, $bookingId);

        $propertyImages = (new Property())->getImages((int) $booking['property_id']);

        $reviewService = new BookingReviewService();
        $canReview = !$isHostView && $reviewService->guestCanReview($booking, $userId);
        $hasReview = !$isHostView && (new Review())->existsForBooking($bookingId);

        return $this->view($isHostView ? 'reservations/show-host' : 'reservations/show', [
            'title' => translated((string) ($booking['title'] ?? __('reservations.title'))),
            'isReservations' => true,
            'isAccountPage' => true,
            'isReservationDetail' => true,
            'isReservationHostDetail' => $isHostView,
            'user' => $user,
            'booking' => $booking,
            'propertyImages' => $propertyImages,
            'isHostView' => $isHostView,
            'canReview' => $canReview,
            'hasReview' => $hasReview,
        ]);
    }

    public function review(string $id): string
    {
        $bookingId = (int) $id;
        $detailPath = '/reservations/' . $bookingId . '/review';

        if (!AuthHelper::check()) {
            $this->redirect(url('/login?redirect=' . rawurlencode($detailPath)));
        }

        $user = AuthHelper::user();
        if ($user === null) {
            $this->redirect(url('/login?redirect=' . rawurlencode($detailPath)));
        }

        $userId = (int) $user['id'];
        $booking = (new Booking())->findForUser($bookingId, $userId);
        if ($booking === null) {
            $this->redirect(url('/reservations'));
        }

        $reviewService = new BookingReviewService();
        if ((new Review())->existsForBooking($bookingId)) {
            Session::flash('reservation_success', __('reviews.success.already'));
            $this->redirect(url('/reservations/' . $bookingId));
        }

        if (!$reviewService->guestCanReview($booking, $userId)) {
            Session::flash('reservation_error', __('reviews.error.not_allowed'));
            $this->redirect(url('/reservations/' . $bookingId));
        }

        $propertyImages = (new Property())->getImages((int) $booking['property_id']);

        return $this->view('reservations/review', [
            'title' => __('reviews.form.title'),
            'isReservations' => true,
            'isAccountPage' => true,
            'user' => $user,
            'booking' => $booking,
            'propertyImages' => $propertyImages,
        ]);
    }

    public function storeReview(string $id): never
    {
        $bookingId = (int) $id;
        $detailPath = '/reservations/' . $bookingId . '/review';

        if (!AuthHelper::check()) {
            $this->redirect(url('/login?redirect=' . rawurlencode($detailPath)));
        }

        $user = AuthHelper::user();
        if ($user === null) {
            $this->redirect(url('/login?redirect=' . rawurlencode($detailPath)));
        }

        $userId = (int) $user['id'];
        $rating = (int) $this->request->input('rating', '0');
        $comment = trim((string) $this->request->input('comment', ''));

        $result = (new BookingReviewService())->submit($bookingId, $userId, $rating, $comment);
        if (!$result['ok']) {
            Session::flash('reservation_error', (string) ($result['error'] ?? __('reviews.error.failed')));
            $this->redirect(url($detailPath));
        }

        Session::flash('reservation_success', __('reviews.success.submitted'));
        $this->redirect(url('/reservations/' . $bookingId));
    }

    public function invoice(string $id): never
    {
        if (!AuthHelper::check()) {
            $this->redirect(url('/login?redirect=/reservations/' . $id . '/invoice'));
        }

        $user = AuthHelper::user();
        if ($user === null) {
            $this->redirect(url('/login?redirect=/reservations/' . $id . '/invoice'));
        }

        $bookingId = (int) $id;
        $booking = (new Booking())->findForUser($bookingId, (int) $user['id']);

        if ($booking === null) {
            $this->redirect(url('/reservations'));
        }

        $invoiceService = new BookingInvoiceService();
        if (!$invoiceService->isAvailable($booking)) {
            $this->redirect(url('/reservations/' . $bookingId));
        }

        $invoice = $invoiceService->build($booking);
        $pdfService = new BookingInvoicePdfService();
        $binary = $pdfService->generate($invoice);
        $filename = $pdfService->filename($booking);
        $download = in_array($this->request->input('download', ''), ['1', 'true'], true);

        $this->pdf($binary, $filename, $download);
    }

    public function cancel(string $id): never
    {
        $bookingId = (int) $id;
        $detailPath = '/reservations/' . $bookingId;

        if (!AuthHelper::check()) {
            $this->redirect(url('/login?redirect=' . rawurlencode($detailPath)));
        }

        $user = AuthHelper::user();
        if ($user === null) {
            $this->redirect(url('/login?redirect=' . rawurlencode($detailPath)));
        }

        $bookingModel = new Booking();
        $booking = $bookingModel->findForUser($bookingId, (int) $user['id']);

        if ($booking === null) {
            Session::flash('reservation_error', __('reservations.cancel.error.not_found'));
            $this->redirect(url('/reservations'));
        }

        if (!$bookingModel->canCancel($booking)) {
            Session::flash('reservation_error', __('reservations.cancel.error.not_allowed'));
            $this->redirect(url($detailPath));
        }

        if (!$bookingModel->cancelForUser($bookingId, (int) $user['id'])) {
            Session::flash('reservation_error', __('reservations.cancel.error.failed'));
            $this->redirect(url($detailPath));
        }

        (new BookingNotificationService())->notifyCancelled($bookingId);

        Session::flash('reservation_success', __('reservations.cancel.success'));
        $this->redirect(url('/reservations'));
    }

    public function unreadStatus(): never
    {
        if (!AuthHelper::check()) {
            $this->json([
                'success' => false,
                'message' => __('messages.login_required'),
            ], 401);
        }

        $user = AuthHelper::user();
        if ($user === null) {
            $this->json([
                'success' => false,
                'message' => __('messages.login_required'),
            ], 401);
        }

        $userId = (int) $user['id'];
        $notificationModel = new Notification();
        $count = $notificationModel->unreadCountByType($userId, 'booking');
        $latest = $notificationModel->latestUnreadByType($userId, 'booking');
        $latestPayload = null;

        if ($latest !== null) {
            $payload = json_decode((string) ($latest['data'] ?? ''), true);
            $bookingId = is_array($payload) ? (int) ($payload['booking_id'] ?? 0) : 0;
            $role = is_array($payload) ? (string) ($payload['role'] ?? 'guest') : 'guest';
            $url = $role === 'host'
                ? ($bookingId > 0
                    ? url('/reservations/' . $bookingId)
                    : url('/reservations?mode=received'))
                : ($bookingId > 0 ? url('/reservations/' . $bookingId) : url('/reservations'));

            $latestPayload = [
                'id' => (int) ($latest['id'] ?? 0),
                'booking_id' => $bookingId,
                'title' => (string) ($latest['title'] ?? ''),
                'body' => (string) ($latest['body'] ?? ''),
                'sender_name' => (string) ($latest['title'] ?? ''),
                'url' => $url,
                'event' => is_array($payload) ? (string) ($payload['event'] ?? '') : '',
            ];
        }

        $this->json([
            'success' => true,
            'count' => $count,
            'booking_ids' => $notificationModel->unreadBookingIds($userId),
            'latest_incoming' => $latestPayload,
        ]);
    }
}
