<?php

declare(strict_types=1);

final class AdminMediaUploadService
{
    private ?int $localeId = null;

    public function __construct(
        private readonly PDO $connection,
        private readonly string $publicRoot,
        private readonly string $localeCode = 'en'
    )
    {
    }

    public function store(array $file, string $collection, string $title): array
    {
        $errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($errorCode !== UPLOAD_ERR_OK) {
            return [
                'ok' => false,
                'error' => $this->uploadErrorMessage($errorCode),
            ];
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');
        $originalName = trim((string) ($file['name'] ?? ''));

        if ($tmpName === '' || $originalName === '') {
            return [
                'ok' => false,
                'error' => 'The selected file could not be read.',
            ];
        }

        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
        ];

        if (! isset($allowed[$extension])) {
            return [
                'ok' => false,
                'error' => 'Please upload a JPG, PNG, WEBP, or SVG image.',
            ];
        }

        $size = (int) ($file['size'] ?? 0);

        if ($size <= 0) {
            return [
                'ok' => false,
                'error' => 'The selected image appears to be empty.',
            ];
        }

        if ($size > 10 * 1024 * 1024) {
            return [
                'ok' => false,
                'error' => 'Please upload an image smaller than 10 MB.',
            ];
        }

        $collection = trim(preg_replace('/[^a-z0-9\/_-]+/i', '-', strtolower($collection)) ?? '');
        $collection = trim($collection, '/');
        $collection = $collection === '' ? 'general' : $collection;

        $relativeDirectory = 'uploads/' . $collection . '/' . gmdate('Y') . '/' . gmdate('m');
        $targetDirectory = rtrim($this->publicRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativeDirectory);

        if (! is_dir($targetDirectory) && ! mkdir($targetDirectory, 0775, true) && ! is_dir($targetDirectory)) {
            return [
                'ok' => false,
                'error' => 'The upload folder could not be created on the server.',
            ];
        }

        $baseName = preg_replace('/[^a-z0-9]+/i', '-', pathinfo($originalName, PATHINFO_FILENAME)) ?: 'media';
        $baseName = trim(strtolower($baseName), '-');
        $baseName = $baseName === '' ? 'media' : $baseName;
        $fileName = $baseName . '-' . gmdate('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
        $targetPath = $targetDirectory . DIRECTORY_SEPARATOR . $fileName;

        if (! move_uploaded_file($tmpName, $targetPath)) {
            return [
                'ok' => false,
                'error' => 'The uploaded image could not be moved into the media library.',
            ];
        }

        $filePath = $relativeDirectory . '/' . $fileName;
        $mediaId = $this->upsertMediaRecord($filePath, $originalName, $allowed[$extension], $title !== '' ? $title : $baseName);

        return [
            'ok' => true,
            'media_id' => $mediaId,
            'file_path' => $filePath,
        ];
    }

    private function upsertMediaRecord(string $filePath, string $originalName, string $mimeType, string $title): int
    {
        $statement = $this->connection->prepare(
            'SELECT id FROM media WHERE file_path = :file_path LIMIT 1'
        );
        $statement->execute(['file_path' => $filePath]);
        $mediaId = $statement->fetchColumn();

        if ($mediaId === false) {
            $insert = $this->connection->prepare(
                'INSERT INTO media (file_path, original_name, mime_type)
                 VALUES (:file_path, :original_name, :mime_type)'
            );
            $insert->execute([
                'file_path' => $filePath,
                'original_name' => $originalName,
                'mime_type' => $mimeType,
            ]);
            $mediaId = (int) $this->connection->lastInsertId();
        } else {
            $mediaId = (int) $mediaId;
        }

        $translation = $this->connection->prepare(
            'INSERT INTO media_translations (media_id, locale_id, title, alt_text)
             VALUES (:media_id, :locale_id, :title, :alt_text)
             ON DUPLICATE KEY UPDATE
                title = VALUES(title),
                alt_text = VALUES(alt_text)'
        );
        $translation->execute([
            'media_id' => $mediaId,
            'locale_id' => $this->localeId(),
            'title' => $title,
            'alt_text' => $title,
        ]);

        return $mediaId;
    }

    private function localeId(): int
    {
        if ($this->localeId !== null) {
            return $this->localeId;
        }

        $statement = $this->connection->prepare(
            'SELECT id FROM locales WHERE code = :code AND is_active = 1 LIMIT 1'
        );
        $statement->execute(['code' => $this->localeCode]);
        $resolved = $statement->fetchColumn();

        if ($resolved === false) {
            $fallback = $this->connection->query(
                'SELECT id FROM locales WHERE is_default = 1 LIMIT 1'
            )->fetchColumn();

            if ($fallback === false) {
                throw new RuntimeException('No active locale could be resolved for media uploads.');
            }

            $resolved = $fallback;
        }

        $this->localeId = (int) $resolved;

        return $this->localeId;
    }

    private function uploadErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The selected image is too large for upload.',
            UPLOAD_ERR_PARTIAL => 'The image upload was interrupted. Please try again.',
            UPLOAD_ERR_NO_TMP_DIR => 'The server is missing a temporary upload folder.',
            UPLOAD_ERR_CANT_WRITE => 'The server could not write the uploaded image to disk.',
            UPLOAD_ERR_EXTENSION => 'A server extension blocked the upload.',
            default => 'The image upload failed. Please try again.',
        };
    }
}
