<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\LocaleHelper;

final class LocaleController extends Controller
{
    public function switch(string $locale): never
    {
        if (!in_array($locale, LocaleHelper::SUPPORTED, true)) {
            $this->redirect(url('/'));
        }

        LocaleHelper::set($locale);

        $redirect = $this->safeReferer();

        $this->redirect($redirect);
    }

    private function safeReferer(): string
    {
        $referer = (string) ($_SERVER['HTTP_REFERER'] ?? '');
        $appUrl = rtrim((string) config('app', 'url'), '/');

        if ($referer === '' || !str_starts_with($referer, $appUrl)) {
            return url('/');
        }

        return $referer;
    }
}
