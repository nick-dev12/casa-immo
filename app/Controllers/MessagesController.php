<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Helpers\AuthHelper;
use App\Models\Conversation;
use App\Models\Land;
use App\Models\Notification;
use App\Models\Property;
use App\Models\User;
use App\Services\HostAgencyContext;
use App\Services\MessageNotificationService;

final class MessagesController extends Controller
{
    public function index(): string
    {
        $user = $this->requireUser('/messages');
        $userId = (int) $user['id'];
        $conversations = (new Conversation())->forUser($userId);

        return $this->viewForAgency('host/messages/index', 'messages/index', [
            'title' => __('messages.title'),
            'isMessages' => true,
            'isAccountPage' => true,
            'user' => $user,
            'conversations' => $this->presentInbox($conversations, $userId),
            'error' => flash('messages_error'),
        ]);
    }

    public function show(string $id): string
    {
        $user = $this->requireUser('/messages/' . $id);
        $userId = (int) $user['id'];
        $conversationId = (int) $id;
        $model = new Conversation();
        $conversation = $model->findForParticipant($conversationId, $userId);

        if ($conversation === null) {
            Session::flash('messages_error', __('messages.error.not_found'));
            $this->redirect(url('/messages'));
        }

        $model->markRead($conversationId, $userId);
        (new Notification())->markReadByConversation($userId, $conversationId);
        $other = $model->otherParticipant($conversationId, $userId);
        $listing = $this->listingContext($conversation);

        return $this->viewForAgency('host/messages/show', 'messages/show', [
            'title' => $other !== null
                ? AuthHelper::fullName($other)
                : __('messages.title'),
            'isMessages' => true,
            'isMessageThread' => true,
            'isAccountPage' => true,
            'user' => $user,
            'conversation' => $conversation,
            'other' => $other,
            'listing' => $listing,
            'messages' => $this->presentMessages($model->messages($conversationId), $userId),
        ]);
    }

    public function start(): never
    {
        $propertyId = (int) $this->request->input('property_id', 0);
        $landId = (int) $this->request->input('land_id', 0);
        $withUserId = (int) $this->request->input('with_user', 0);
        $redirect = '/messages/start';
        if ($propertyId > 0) {
            $redirect .= '?property_id=' . $propertyId;
            if ($withUserId > 0) {
                $redirect .= '&with_user=' . $withUserId;
            }
        } elseif ($landId > 0) {
            $redirect .= '?land_id=' . $landId;
        }

        $user = $this->requireUser($redirect);
        $userId = (int) $user['id'];

        $otherId = 0;
        $subject = '';

        if ($propertyId > 0) {
            $property = (new Property())->findById($propertyId);
            if ($property === null) {
                Session::flash('messages_error', __('messages.error.listing'));
                $this->redirect(url('/messages'));
            }
            $ownerId = (int) ($property['owner_id'] ?? 0);
            $withUserId = (int) $this->request->input('with_user', 0);
            if ($withUserId > 0 && $userId === $ownerId) {
                $otherId = $withUserId;
            } else {
                $otherId = $ownerId;
            }
            $subject = (string) ($property['title'] ?? '');
        } elseif ($landId > 0) {
            $land = (new Land())->findById($landId);
            if ($land === null) {
                Session::flash('messages_error', __('messages.error.listing'));
                $this->redirect(url('/messages'));
            }
            $otherId = (int) ($land['seller_id'] ?? 0);
            $subject = (string) ($land['title'] ?? '');
        } else {
            Session::flash('messages_error', __('messages.error.listing'));
            $this->redirect(url('/messages'));
        }

        if ($otherId <= 0 || (new User())->findById($otherId) === null) {
            Session::flash('messages_error', __('messages.error.seller'));
            $this->redirect(url('/messages'));
        }

        if ($otherId === $userId) {
            Session::flash('messages_error', __('messages.error.self'));
            $this->redirect(url('/messages'));
        }

        $conversationId = (new Conversation())->findOrCreateForListing(
            $userId,
            $otherId,
            $propertyId > 0 ? $propertyId : null,
            $landId > 0 ? $landId : null,
            $subject
        );

        $this->redirect(url('/messages/' . $conversationId));
    }

    public function send(string $id): never
    {
        $user = $this->requireUserJson();
        $userId = (int) $user['id'];
        $conversationId = (int) $id;
        $model = new Conversation();

        if ($model->findForParticipant($conversationId, $userId) === null) {
            $this->json(['success' => false, 'message' => __('messages.error.not_found')], 404);
        }

        $body = trim((string) $this->request->input('body', ''));
        if ($body === '') {
            $this->json(['success' => false, 'message' => __('messages.error.empty')], 422);
        }

        if (mb_strlen($body) > 2000) {
            $this->json(['success' => false, 'message' => __('messages.error.too_long')], 422);
        }

        $message = $model->send($conversationId, $userId, $body);
        (new MessageNotificationService())->notifyRecipient($conversationId, $userId, $message);

        $this->json([
            'success' => true,
            'message' => $this->presentMessage($message, $userId),
        ]);
    }

    public function unreadStatus(): never
    {
        $user = $this->requireUserJson();
        $userId = (int) $user['id'];
        $model = new Conversation();
        $latest = $model->latestIncomingMessage($userId);
        $latestPayload = null;

        if ($latest !== null) {
            $other = [
                'first_name' => $latest['first_name'] ?? '',
                'last_name' => $latest['last_name'] ?? '',
            ];
            $body = trim((string) ($latest['body'] ?? ''));
            if (mb_strlen($body) > 120) {
                $body = mb_substr($body, 0, 117) . '…';
            }

            $latestPayload = [
                'id' => (int) ($latest['id'] ?? 0),
                'conversation_id' => (int) ($latest['conversation_id'] ?? 0),
                'body' => $body,
                'sender_name' => AuthHelper::fullName($other) !== '' ? AuthHelper::fullName($other) : __('messages.unknown'),
                'time_label' => format_message_time((string) ($latest['created_at'] ?? '')),
                'url' => url('/messages/' . (int) ($latest['conversation_id'] ?? 0)),
            ];
        }

        $this->json([
            'success' => true,
            'count' => $model->unreadCountForUser($userId),
            'conversations' => $model->unreadConversationsSummary($userId),
            'latest_incoming' => $latestPayload,
        ]);
    }

    public function poll(string $id): never
    {
        $user = $this->requireUserJson();
        $userId = (int) $user['id'];
        $conversationId = (int) $id;
        $model = new Conversation();

        if ($model->findForParticipant($conversationId, $userId) === null) {
            $this->json(['success' => false, 'message' => __('messages.error.not_found')], 404);
        }

        $afterId = max(0, (int) $this->request->input('after', 0));
        $incoming = $model->messages($conversationId, $afterId);
        $model->markRead($conversationId, $userId);
        if ($incoming !== []) {
            (new Notification())->markReadByConversation($userId, $conversationId);
        }

        $this->json([
            'success' => true,
            'messages' => $this->presentMessages($incoming, $userId),
            'unread_count' => $model->unreadCountForUser($userId),
        ]);
    }

    /**
     * Affiche une vue dans le shell agence si l'utilisateur a un établissement.
     *
     * @param array<string, mixed> $data
     */
    private function viewForAgency(string $hostView, string $defaultView, array $data): string
    {
        $userId = (int) (($data['user']['id'] ?? 0));
        $agencyContext = $userId > 0 ? HostAgencyContext::forUser($userId) : null;

        if ($agencyContext === null) {
            return $this->view($defaultView, $data);
        }

        return $this->view($hostView, array_merge($data, $agencyContext, [
            'isHostPage' => true,
            'isAccountPage' => true,
        ]));
    }

    /**
     * @return array<string, mixed>
     */
    private function requireUser(string $redirectPath): array
    {
        if (!AuthHelper::check()) {
            $this->redirect(url('/login?redirect=' . rawurlencode($redirectPath)));
        }

        $user = AuthHelper::user();
        if ($user === null) {
            $this->redirect(url('/login?redirect=' . rawurlencode($redirectPath)));
        }

        return $user;
    }

    /**
     * @return array<string, mixed>
     */
    private function requireUserJson(): array
    {
        if (!AuthHelper::check()) {
            $this->json([
                'success' => false,
                'message' => __('messages.login_required'),
                'login_url' => url('/login?redirect=/messages'),
            ], 401);
        }

        $user = AuthHelper::user();
        if ($user === null) {
            $this->json([
                'success' => false,
                'message' => __('messages.login_required'),
                'login_url' => url('/login?redirect=/messages'),
            ], 401);
        }

        return $user;
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    private function presentInbox(array $rows, int $userId): array
    {
        $items = [];
        foreach ($rows as $row) {
            $other = [
                'id' => $row['other_id'] ?? null,
                'first_name' => $row['other_first_name'] ?? '',
                'last_name' => $row['other_last_name'] ?? '',
                'avatar' => $row['other_avatar'] ?? null,
            ];
            $listingTitle = trim((string) ($row['property_title'] ?? $row['land_title'] ?? $row['subject'] ?? ''));
            $isLand = !empty($row['land_id']) && empty($row['property_title']);
            $image = $isLand
                ? land_image($row['land_image'] ?? null, (string) ($row['land_type'] ?? 'residentiel'))
                : property_image($row['property_image'] ?? null, (string) ($row['property_type'] ?? 'autre'));
            $listingUrl = '';
            if (!empty($row['property_id'])) {
                $listingUrl = url('/properties/' . (int) $row['property_id']);
            } elseif (!empty($row['land_id'])) {
                $listingUrl = url('/lands/' . (int) $row['land_id']);
            }

            $lastBody = trim((string) ($row['last_body'] ?? ''));
            $lastSenderId = (int) ($row['last_sender_id'] ?? 0);
            if ($lastBody !== '' && $lastSenderId === $userId) {
                $lastBody = __('messages.you_prefix', ['body' => $lastBody]);
            }

            $items[] = [
                'id' => (int) $row['id'],
                'other_name' => AuthHelper::fullName($other) !== '' ? AuthHelper::fullName($other) : __('messages.unknown'),
                'other_initials' => user_initials($other),
                'other_avatar' => user_avatar_url($other),
                'listing_title' => $listingTitle,
                'listing_url' => $listingUrl,
                'listing_image' => $listingTitle !== '' ? $image : '',
                'last_body' => $lastBody,
                'last_at' => format_message_time((string) ($row['last_at'] ?? $row['updated_at'] ?? '')),
                'unread_count' => (int) ($row['unread_count'] ?? 0),
            ];
        }

        return $items;
    }

    /**
     * @param array<string, mixed> $conversation
     * @return array<string, mixed>|null
     */
    private function listingContext(array $conversation): ?array
    {
        $propertyId = (int) ($conversation['property_id'] ?? 0);
        $landId = (int) ($conversation['land_id'] ?? 0);

        if ($propertyId > 0) {
            $property = (new Property())->findById($propertyId);
            if ($property === null) {
                return null;
            }

            return [
                'kind' => 'property',
                'title' => (string) ($property['title'] ?? ''),
                'city' => (string) ($property['city'] ?? ''),
                'url' => url('/properties/' . $propertyId . '?city=' . urlencode((string) ($property['city'] ?? ''))),
                'image' => property_image($property['primary_image'] ?? null, (string) ($property['type'] ?? 'autre')),
            ];
        }

        if ($landId > 0) {
            $land = (new Land())->findById($landId);
            if ($land === null) {
                return null;
            }

            return [
                'kind' => 'land',
                'title' => (string) ($land['title'] ?? ''),
                'city' => (string) ($land['city'] ?? ''),
                'url' => url('/lands/' . $landId . '?city=' . urlencode((string) ($land['city'] ?? ''))),
                'image' => land_image($land['primary_image'] ?? null, (string) ($land['land_type'] ?? 'residentiel')),
            ];
        }

        $subject = trim((string) ($conversation['subject'] ?? ''));
        if ($subject === '') {
            return null;
        }

        return [
            'kind' => 'other',
            'title' => $subject,
            'city' => '',
            'url' => '',
            'image' => '',
        ];
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    private function presentMessages(array $rows, int $userId): array
    {
        $items = [];
        foreach ($rows as $row) {
            $items[] = $this->presentMessage($row, $userId);
        }

        return $items;
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function presentMessage(array $row, int $userId): array
    {
        return [
            'id' => (int) ($row['id'] ?? 0),
            'sender_id' => (int) ($row['sender_id'] ?? 0),
            'body' => (string) ($row['body'] ?? ''),
            'mine' => (int) ($row['sender_id'] ?? 0) === $userId,
            'created_at' => (string) ($row['created_at'] ?? ''),
            'time_label' => format_message_time((string) ($row['created_at'] ?? ''), true),
        ];
    }
}
