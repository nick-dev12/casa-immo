<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public static function json(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        exit;
    }

    public static function redirect(string $url, int $status = 302): never
    {
        http_response_code($status);
        header('Location: ' . $url);
        exit;
    }

    public static function pdf(string $binary, string $filename, bool $download = false): never
    {
        http_response_code(200);
        header('Content-Type: application/pdf');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename) ?: 'facture.pdf';
        $disposition = $download ? 'attachment' : 'inline';
        header('Content-Disposition: ' . $disposition . '; filename="' . $safeName . '"');
        header('Content-Length: ' . (string) strlen($binary));
        echo $binary;
        exit;
    }

    public static function abort(int $status, string $message = ''): never
    {
        http_response_code($status);

        if ($message !== '') {
            echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        }

        exit;
    }
}
