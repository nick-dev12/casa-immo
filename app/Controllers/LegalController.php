<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

final class LegalController extends Controller
{
    public function terms(): string
    {
        return $this->view('legal/terms', [
            'title' => __('legal.terms_title'),
            'isAccountPage' => true,
            'isHelpPage' => true,
            'appName' => config('app', 'name'),
        ]);
    }

    public function privacy(): string
    {
        return $this->view('legal/privacy', [
            'title' => __('legal.privacy_title'),
            'isAccountPage' => true,
            'isHelpPage' => true,
            'appName' => config('app', 'name'),
        ]);
    }
}
