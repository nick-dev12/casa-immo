<?php

declare(strict_types=1);

namespace App\Services;

final class ListingVideoService
{
    /** @var list<string> */
    private const ALLOWED_MIMES = [
        'video/mp4',
        'video/webm',
        'video/quicktime',
    ];

    /** @var array<string, string> */
    private const EXTENSIONS = [
        'video/mp4' => 'mp4',
        'video/webm' => 'webm',
        'video/quicktime' => 'mov',
    ];

    /**
     * @param array<string, mixed>|null $file
     */
    public function store(?array $file, string $folder): ?string
    {
        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException(__('host.video.error_upload'));
        }

        $maxBytes = $this->maxBytes();
        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > $maxBytes) {
            throw new \RuntimeException(__('host.video.error_size', [
                'max' => (int) config('app', 'property_video_max_mb', 50),
            ]));
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new \RuntimeException(__('host.video.error_upload'));
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp) ?: '';
        if (!in_array($mime, self::ALLOWED_MIMES, true)) {
            throw new \RuntimeException(__('host.video.error_type'));
        }

        $directory = public_path(trim($folder, '/'));
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new \RuntimeException(__('host.video.error_storage'));
        }

        $extension = self::EXTENSIONS[$mime] ?? 'mp4';
        $filename = 'video-' . bin2hex(random_bytes(8)) . '.' . $extension;
        $absolute = $directory . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($tmp, $absolute)) {
            throw new \RuntimeException(__('host.video.error_upload'));
        }

        return trim($folder, '/') . '/' . $filename;
    }

    public function deleteFile(?string $relativePath): void
    {
        if ($relativePath === null || $relativePath === '') {
            return;
        }

        if (str_starts_with($relativePath, 'http://') || str_starts_with($relativePath, 'https://')) {
            return;
        }

        $absolute = public_path(ltrim($relativePath, '/'));
        if (is_file($absolute)) {
            @unlink($absolute);
        }
    }

    private function maxBytes(): int
    {
        $maxMb = max(1, (int) config('app', 'property_video_max_mb', 50));

        return $maxMb * 1024 * 1024;
    }
}
