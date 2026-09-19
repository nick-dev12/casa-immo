<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected Request $request;

    public function __construct()
    {
        $this->request = new Request();
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function view(string $view, array $data = [], ?string $layout = 'layouts/main'): string
    {
        return View::render($view, $data, $layout);
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function json(array $data, int $status = 200): never
    {
        Response::json($data, $status);
    }

    protected function redirect(string $url): never
    {
        Response::redirect($url);
    }

    protected function pdf(string $binary, string $filename, bool $download = false): never
    {
        Response::pdf($binary, $filename, $download);
    }
}
