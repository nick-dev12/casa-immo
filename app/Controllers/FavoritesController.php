<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\AuthHelper;
use App\Models\Favorite;

final class FavoritesController extends Controller
{
    public function index(): string
    {
        if (!AuthHelper::check()) {
            $this->redirect(url('/login?redirect=/favorites'));
        }

        $user = AuthHelper::user();
        if ($user === null) {
            $this->redirect(url('/login?redirect=/favorites'));
        }
        $favoriteModel = new Favorite();
        $userId = (int) $user['id'];

        return $this->view('favorites/index', [
            'title' => __('favorites.title'),
            'isFavorites' => true,
            'isAccountPage' => true,
            'user' => $user,
            'items' => $favoriteModel->forUser($userId),
            'favoritePropertyIds' => $favoriteModel->propertyIdsForUser($userId),
        ]);
    }
}
