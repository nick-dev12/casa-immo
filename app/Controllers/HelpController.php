<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\AuthHelper;

final class HelpController extends Controller
{
    public function index(): string
    {
        if (AuthHelper::check()) {
            $this->redirect(url('/profile/help'));
        }

        return $this->view('help/index', [
            'title' => __('profile.contact_support'),
            'isAccountPage' => true,
            'isHelpPage' => true,
            'appName' => config('app', 'name'),
        ]);
    }
}
