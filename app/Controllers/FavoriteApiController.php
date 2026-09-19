<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\AuthHelper;
use App\Models\Favorite;

final class FavoriteApiController extends Controller
{
    public function toggle(): never
    {
        if (!AuthHelper::check()) {
            $this->json([
                'success' => false,
                'message' => __('favorites.login_required'),
                'login_url' => url('/login?redirect=' . urlencode('/favorites')),
            ], 401);
        }

        $type = (string) $this->request->input('type', 'property');
        $id = (int) $this->request->input('id', 0);
        $userId = (int) AuthHelper::id();

        $favoriteModel = new Favorite();
        $favorited = $favoriteModel->toggle($userId, $type, $id);

        $this->json([
            'success' => true,
            'favorited' => $favorited,
        ]);
    }

    /**
     * @return never
     */
    public function ids(): never
    {
        if (!AuthHelper::check()) {
            $this->json(['success' => true, 'property_ids' => [], 'land_ids' => []]);
        }

        $favoriteModel = new Favorite();
        $this->json([
            'success' => true,
            'property_ids' => $favoriteModel->propertyIdsForUser((int) AuthHelper::id()),
            'land_ids' => $favoriteModel->landIdsForUser((int) AuthHelper::id()),
        ]);
    }
}
