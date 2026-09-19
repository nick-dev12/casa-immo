<?php

declare(strict_types=1);

namespace App\Services;

final class TranslationService
{
    private static ?self $instance = null;

    private string $driver;

    private string $cachePath;

    private int $timeout;

    /** @var array<string, mixed> */
    private array $config;

    private function __construct()
    {
        /** @var array<string, mixed> $config */
        $config = config('translation');
        $this->config = $config;
        $this->driver = (string) ($config['driver'] ?? 'libretranslate');
        $this->cachePath = (string) ($config['cache_path'] ?? base_path('storage/cache/translations'));
        $this->timeout = (int) ($config['timeout'] ?? 8);

        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function translate(string $text, string $from, string $to): string
    {
        $text = trim($text);
        $from = strtolower($from);
        $to = strtolower($to);

        if ($text === '' || $from === $to) {
            return $text;
        }

        $cacheKey = hash('sha256', $from . '|' . $to . '|' . $text);
        $cached = $this->readCache($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        $result = $this->driver === 'mymemory'
            ? $this->viaMyMemory($text, $from, $to)
            : $this->viaLibreTranslate($text, $from, $to);

        if ($result === null) {
            $result = $this->driver === 'mymemory'
                ? $this->viaLibreTranslate($text, $from, $to)
                : $this->viaMyMemory($text, $from, $to);
        }

        if ($result === null || trim($result) === '') {
            return $text;
        }

        $this->writeCache($cacheKey, $result);

        return $result;
    }

    private function readCache(string $key): ?string
    {
        $path = $this->cachePath . DIRECTORY_SEPARATOR . $key . '.json';

        if (!is_file($path)) {
            return null;
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            return null;
        }

        /** @var array{text?: string}|null $data */
        $data = json_decode($raw, true);

        return is_array($data) ? ($data['text'] ?? null) : null;
    }

    private function writeCache(string $key, string $text): void
    {
        $path = $this->cachePath . DIRECTORY_SEPARATOR . $key . '.json';
        file_put_contents($path, json_encode(['text' => $text], JSON_UNESCAPED_UNICODE));
    }

    private function viaLibreTranslate(string $text, string $from, string $to): ?string
    {
        /** @var array{url?: string, api_key?: string} $cfg */
        $cfg = $this->config['libretranslate'] ?? [];
        $baseUrl = rtrim((string) ($cfg['url'] ?? ''), '/');

        if ($baseUrl === '') {
            return null;
        }

        $payload = [
            'q' => $text,
            'source' => $from,
            'target' => $to,
            'format' => 'text',
        ];

        $apiKey = (string) ($cfg['api_key'] ?? '');
        if ($apiKey !== '') {
            $payload['api_key'] = $apiKey;
        }

        $response = $this->httpPostJson($baseUrl . '/translate', $payload);
        if ($response === null) {
            return null;
        }

        /** @var array{translatedText?: string} $data */
        $data = json_decode($response, true);

        return is_array($data) ? ($data['translatedText'] ?? null) : null;
    }

    private function viaMyMemory(string $text, string $from, string $to): ?string
    {
        /** @var array{email?: string} $cfg */
        $cfg = $this->config['mymemory'] ?? [];
        $email = (string) ($cfg['email'] ?? '');

        $query = http_build_query(array_filter([
            'q' => $text,
            'langpair' => $from . '|' . $to,
            'de' => $email !== '' ? $email : null,
        ]));

        $response = $this->httpGet('https://api.mymemory.translated.net/get?' . $query);
        if ($response === null) {
            return null;
        }

        /** @var array{responseData?: array{translatedText?: string}} $data */
        $data = json_decode($response, true);
        $translated = $data['responseData']['translatedText'] ?? null;

        if (!is_string($translated) || strtoupper($translated) === 'INVALID TARGET LANGUAGE') {
            return null;
        }

        return $translated;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function httpPostJson(string $url, array $payload): ?string
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            if ($ch === false) {
                return null;
            }

            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
                CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
                CURLOPT_TIMEOUT => $this->timeout,
            ]);

            $response = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return $response !== false && $status >= 200 && $status < 300 ? $response : null;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\nAccept: application/json\r\n",
                'content' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'timeout' => $this->timeout,
                'ignore_errors' => true,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);

        return $response !== false ? $response : null;
    }

    private function httpGet(string $url): ?string
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            if ($ch === false) {
                return null;
            }

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $this->timeout,
            ]);

            $response = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return $response !== false && $status >= 200 && $status < 300 ? $response : null;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => $this->timeout,
                'ignore_errors' => true,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);

        return $response !== false ? $response : null;
    }
}
