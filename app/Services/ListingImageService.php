<?php

declare(strict_types=1);

namespace App\Services;

final class ListingImageService
{
    /** @var list<string> */
    private const ALLOWED_MIMES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/webp',
        'image/gif',
        'image/heic',
        'image/heif',
    ];

    public function __construct(
        private readonly ImageCompressorService $compressor = new ImageCompressorService(),
    ) {
    }

    /**
     * @param array<string, mixed>|null $file
     */
    public function store(?array $file, string $folder): ?string
    {
        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException(__('host.images.error_upload'));
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new \RuntimeException(__('host.images.error_upload'));
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp) ?: '';
        if (!in_array($mime, self::ALLOWED_MIMES, true)) {
            throw new \RuntimeException(__('host.images.error_type'));
        }

        $directory = public_path(trim($folder, '/'));
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new \RuntimeException(__('host.images.error_storage'));
        }

        $filename = bin2hex(random_bytes(8)) . '.webp';
        $absolute = $directory . DIRECTORY_SEPARATOR . $filename;

        $this->compressor->convertUploadToWebp($tmp, $absolute);

        return trim($folder, '/') . '/' . $filename;
    }

    /**
     * @param array<string, mixed>|null $filesInput
     * @return list<string>
     */
    public function storeMany(?array $filesInput, string $folder, int $maxNew): array
    {
        if ($filesInput === null) {
            return [];
        }

        $normalized = $this->normalizeFilesArray($filesInput);
        if ($normalized === []) {
            return [];
        }

        if (count($normalized) > $maxNew) {
            throw new \RuntimeException(__('host.images.error_max', ['max' => $maxNew]));
        }

        $paths = [];
        foreach ($normalized as $file) {
            $path = $this->store($file, $folder);
            if ($path !== null) {
                $paths[] = $path;
            }
        }

        return $paths;
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

    /**
     * @param array<string, mixed> $filesInput
     * @return list<array<string, mixed>>
     */
    private function normalizeFilesArray(array $filesInput): array
    {
        if (!isset($filesInput['name']) || !is_array($filesInput['name'])) {
            $error = (int) ($filesInput['error'] ?? UPLOAD_ERR_NO_FILE);

            return $error !== UPLOAD_ERR_NO_FILE ? [$filesInput] : [];
        }

        $files = [];
        foreach ($filesInput['name'] as $index => $name) {
            if (($filesInput['error'][$index] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $files[] = [
                'name' => $name,
                'type' => $filesInput['type'][$index] ?? '',
                'tmp_name' => $filesInput['tmp_name'][$index] ?? '',
                'error' => $filesInput['error'][$index] ?? UPLOAD_ERR_NO_FILE,
                'size' => $filesInput['size'][$index] ?? 0,
            ];
        }

        return $files;
    }
}
