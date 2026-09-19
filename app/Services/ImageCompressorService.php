<?php

declare(strict_types=1);

namespace App\Services;

final class ImageCompressorService
{
    private const DEFAULT_QUALITY = 82;
    private const DEFAULT_MAX_DIMENSION = 2400;

    /**
     * Compresse et convertit une image uploadée en WebP.
     */
    public function convertUploadToWebp(string $sourcePath, string $destinationPath): void
    {
        if (!is_file($sourcePath) || !is_readable($sourcePath)) {
            throw new \RuntimeException(__('host.images.error_upload'));
        }

        $directory = dirname($destinationPath);
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new \RuntimeException(__('host.images.error_storage'));
        }

        $quality = (int) config('app', 'image_webp_quality', self::DEFAULT_QUALITY);
        $maxDimension = (int) config('app', 'image_max_dimension', self::DEFAULT_MAX_DIMENSION);
        $tempSource = $this->normalizeSource($sourcePath, $maxDimension);

        try {
            if ($this->convertWithGdWebp($tempSource, $destinationPath, $quality)) {
                return;
            }

            if ($this->convertWithCwebp($tempSource, $destinationPath, $quality)) {
                return;
            }

            throw new \RuntimeException(__('host.images.error_webp'));
        } finally {
            if ($tempSource !== $sourcePath && is_file($tempSource)) {
                @unlink($tempSource);
            }
        }
    }

    private function normalizeSource(string $sourcePath, int $maxDimension): string
    {
        if (!extension_loaded('gd')) {
            return $sourcePath;
        }

        $info = @getimagesize($sourcePath);
        if ($info === false) {
            return $sourcePath;
        }

        [$width, $height] = $info;
        if ($width <= $maxDimension && $height <= $maxDimension) {
            return $sourcePath;
        }

        $image = $this->loadGdImage($sourcePath, (string) ($info['mime'] ?? ''));
        if ($image === null) {
            return $sourcePath;
        }

        $scale = min($maxDimension / $width, $maxDimension / $height, 1);
        $targetW = max(1, (int) round($width * $scale));
        $targetH = max(1, (int) round($height * $scale));

        $resized = imagecreatetruecolor($targetW, $targetH);
        if ($resized === false) {
            return $sourcePath;
        }

        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $targetW, $targetH, $width, $height);

        $temp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zig_img_' . bin2hex(random_bytes(8)) . '.png';
        if (!imagepng($resized, $temp)) {
            return $sourcePath;
        }

        return $temp;
    }

    private function convertWithGdWebp(string $sourcePath, string $destinationPath, int $quality): bool
    {
        if (!function_exists('imagewebp')) {
            return false;
        }

        $info = @getimagesize($sourcePath);
        if ($info === false) {
            return false;
        }

        $image = $this->loadGdImage($sourcePath, (string) ($info['mime'] ?? ''));
        if ($image === null) {
            return false;
        }

        $success = imagewebp($image, $destinationPath, $quality);

        return $success && is_file($destinationPath);
    }

    private function convertWithCwebp(string $sourcePath, string $destinationPath, int $quality): bool
    {
        if (!function_exists('exec')) {
            return false;
        }

        $binary = $this->findCwebpBinary();
        if ($binary === null) {
            return false;
        }

        $command = sprintf(
            '%s -q %d %s -o %s',
            escapeshellarg($binary),
            max(1, min(100, $quality)),
            escapeshellarg($sourcePath),
            escapeshellarg($destinationPath)
        );

        exec($command, $output, $exitCode);

        return $exitCode === 0 && is_file($destinationPath);
    }

    private function findCwebpBinary(): ?string
    {
        $candidates = [
            '/usr/local/bin/cwebp',
            '/opt/homebrew/bin/cwebp',
            '/usr/bin/cwebp',
            'cwebp',
        ];

        foreach ($candidates as $candidate) {
            if ($candidate === 'cwebp') {
                exec('command -v cwebp 2>/dev/null', $which, $code);
                if ($code === 0 && !empty($which[0]) && is_executable(trim($which[0]))) {
                    return trim($which[0]);
                }
                continue;
            }

            if (is_executable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @return \GdImage|resource|null
     */
    private function loadGdImage(string $path, string $mime)
    {
        return match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($path) ?: null,
            'image/png' => $this->loadPng($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? (@imagecreatefromwebp($path) ?: null) : null,
            'image/gif' => @imagecreatefromgif($path) ?: null,
            default => null,
        };
    }

    /**
     * @return \GdImage|resource|null
     */
    private function loadPng(string $path)
    {
        $image = @imagecreatefrompng($path);
        if ($image === false) {
            return null;
        }

        imagealphablending($image, true);
        imagesavealpha($image, true);

        return $image;
    }
}
