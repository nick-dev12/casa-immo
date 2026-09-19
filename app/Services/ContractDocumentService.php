<?php

declare(strict_types=1);

namespace App\Services;

final class ContractDocumentService
{
    /** @var list<string> */
    private const PDF_MIMES = ['application/pdf'];

    /** @var list<string> */
    private const PHOTO_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    /**
     * @param array<string, mixed>|null $file
     */
    public function storePdf(?array $file, int $contractId): string
    {
        $path = $this->store($file, 'uploads/contracts/' . $contractId, self::PDF_MIMES, [
            'application/pdf' => 'pdf',
        ], 10 * 1024 * 1024, true);

        if ($path === null) {
            throw new \RuntimeException(__('admin.rental.error_contract_required'));
        }

        return $path;
    }

    /**
     * @param array<string, mixed>|null $file
     */
    public function storePhoto(?array $file, int $contractId): ?string
    {
        return $this->store($file, 'uploads/contracts/' . $contractId, self::PHOTO_MIMES, [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ], 5 * 1024 * 1024, false);
    }

    /**
     * @param array<string, mixed>|null $files
     * @return list<string>
     */
    public function storePropertyPhotos(?array $files, int $contractId, int $maxCount = 5): array
    {
        $normalized = $this->normalizeUploadedFiles($files);
        if ($normalized === []) {
            return [];
        }

        if (count($normalized) > $maxCount) {
            throw new \RuntimeException(__('admin.rental.error_photos_max'));
        }

        $paths = [];
        foreach ($normalized as $file) {
            $path = $this->store($file, 'uploads/contracts/' . $contractId, self::PHOTO_MIMES, [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
            ], 5 * 1024 * 1024, false);

            if ($path !== null) {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    /**
     * @param array<string, mixed>|null $files
     * @return list<array<string, mixed>>
     */
    private function normalizeUploadedFiles(?array $files): array
    {
        if ($files === null || ($files['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return [];
        }

        if (!is_array($files['name'] ?? null)) {
            return [$files];
        }

        $normalized = [];
        foreach ($files['name'] as $index => $name) {
            if (($files['error'][$index] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $normalized[] = [
                'name' => $name,
                'type' => $files['type'][$index] ?? '',
                'tmp_name' => $files['tmp_name'][$index] ?? '',
                'error' => $files['error'][$index] ?? UPLOAD_ERR_NO_FILE,
                'size' => $files['size'][$index] ?? 0,
            ];
        }

        return $normalized;
    }

    /**
     * @param array<string, mixed>|null $file
     * @param list<string> $allowedMimes
     * @param array<string, string> $extensions
     */
    private function store(
        ?array $file,
        string $folder,
        array $allowedMimes,
        array $extensions,
        int $maxBytes,
        bool $required
    ): ?string {
        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            if ($required) {
                throw new \RuntimeException(__('admin.rental.error_contract_required'));
            }

            return null;
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException(__('admin.rental.error_upload'));
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > $maxBytes) {
            throw new \RuntimeException(__('admin.rental.error_file_size'));
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new \RuntimeException(__('admin.rental.error_upload'));
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp) ?: '';
        if (!in_array($mime, $allowedMimes, true)) {
            throw new \RuntimeException(__('admin.rental.error_file_type'));
        }

        $directory = public_path(trim($folder, '/'));
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new \RuntimeException(__('admin.rental.error_storage'));
        }

        $extension = $extensions[$mime] ?? 'bin';
        $filename = bin2hex(random_bytes(8)) . '.' . $extension;
        $absolute = $directory . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($tmp, $absolute)) {
            throw new \RuntimeException(__('admin.rental.error_upload'));
        }

        return trim($folder, '/') . '/' . $filename;
    }
}
