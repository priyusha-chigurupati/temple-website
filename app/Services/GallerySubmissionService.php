<?php

declare(strict_types=1);

final class GallerySubmissionService
{
    private const MAX_FILES = 6;
    private const MAX_FILE_SIZE = 8388608;
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    public function __construct(
        private readonly string $manifestPath,
        private readonly string $uploadsDirectory,
        private readonly ?PDO $connection = null
    ) {
    }

    public function submit(array $post, array $files): array
    {
        $name = trim((string) ($post['name'] ?? ''));
        $email = trim((string) ($post['email'] ?? ''));
        $description = trim((string) ($post['description'] ?? ''));
        $normalizedFiles = $this->normalizeFiles($files['photos'] ?? null);
        $errors = [];

        if ($name === '') {
            $errors[] = 'Please enter your name.';
        }

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'Please enter a valid email address.';
        }

        if ($normalizedFiles === []) {
            $errors[] = 'Please upload at least one photo.';
        }

        if (count($normalizedFiles) > self::MAX_FILES) {
            $errors[] = 'You can upload up to ' . self::MAX_FILES . ' photos at a time.';
        }

        foreach ($normalizedFiles as $file) {
            $fileErrors = $this->validateFile($file);
            foreach ($fileErrors as $error) {
                $errors[] = $error;
            }
        }

        if ($errors !== []) {
            return [
                'ok' => false,
                'errors' => array_values(array_unique($errors)),
                'old' => [
                    'name' => $name,
                    'email' => $email,
                    'description' => $description,
                ],
            ];
        }

        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $submissionId = 'gs_' . gmdate('YmdHis') . '_' . bin2hex(random_bytes(4));
        $targetDirectory = $this->uploadsDirectory . DIRECTORY_SEPARATOR . gmdate('Y') . DIRECTORY_SEPARATOR . gmdate('m');

        if (! is_dir($targetDirectory) && ! mkdir($targetDirectory, 0775, true) && ! is_dir($targetDirectory)) {
            return [
                'ok' => false,
                'errors' => ['The upload directory could not be created.'],
                'old' => [
                    'name' => $name,
                    'email' => $email,
                    'description' => $description,
                ],
            ];
        }

        $storedFiles = [];

        foreach ($normalizedFiles as $index => $file) {
            $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
            $safeExtension = $extension === '' ? 'jpg' : $extension;
            $targetName = $submissionId . '_' . ($index + 1) . '.' . $safeExtension;
            $targetPath = $targetDirectory . DIRECTORY_SEPARATOR . $targetName;
            $relativeDirectory = 'storage/uploads/gallery-submissions/' . gmdate('Y') . '/' . gmdate('m');

            if (! move_uploaded_file((string) $file['tmp_name'], $targetPath)) {
                return [
                    'ok' => false,
                    'errors' => ['One or more files could not be uploaded. Please try again.'],
                    'old' => [
                        'name' => $name,
                        'email' => $email,
                        'description' => $description,
                    ],
                ];
            }

            $storedFiles[] = [
                'original_name' => (string) $file['name'],
                'stored_path' => $relativeDirectory . '/' . $targetName,
                'mime_type' => (string) $file['type'],
                'size_bytes' => (int) $file['size'],
            ];
        }

        $entry = [
            'id' => $submissionId,
            'name' => $name,
            'email' => $email,
            'description' => $description,
            'status' => 'pending',
            'created_at' => $timestamp,
            'files' => $storedFiles,
        ];

        if (! $this->store($entry)) {
            return [
                'ok' => false,
                'errors' => ['The submission could not be recorded after the files were uploaded. Please try again.'],
                'old' => [
                    'name' => $name,
                    'email' => $email,
                    'description' => $description,
                ],
            ];
        }

        return [
            'ok' => true,
            'message' => 'Thank you. Your photos have been submitted for review.',
        ];
    }

    private function normalizeFiles(mixed $files): array
    {
        if (! is_array($files) || ! isset($files['name'], $files['tmp_name'], $files['error'], $files['size'])) {
            return [];
        }

        $normalized = [];
        $names = $files['name'];

        if (! is_array($names)) {
            return [];
        }

        foreach (array_keys($names) as $index) {
            $error = $files['error'][$index] ?? UPLOAD_ERR_NO_FILE;

            if ($error === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $normalized[] = [
                'name' => $files['name'][$index] ?? '',
                'type' => $files['type'][$index] ?? '',
                'tmp_name' => $files['tmp_name'][$index] ?? '',
                'error' => $error,
                'size' => $files['size'][$index] ?? 0,
            ];
        }

        return $normalized;
    }

    private function validateFile(array $file): array
    {
        $errors = [];
        $errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        $name = (string) ($file['name'] ?? 'Photo');
        $size = (int) ($file['size'] ?? 0);
        $tmpName = (string) ($file['tmp_name'] ?? '');
        $mimeType = (string) ($file['type'] ?? '');

        if ($errorCode !== UPLOAD_ERR_OK) {
            $errors[] = $name . ' could not be uploaded.';
            return $errors;
        }

        if ($size <= 0 || $size > self::MAX_FILE_SIZE) {
            $errors[] = $name . ' exceeds the 8 MB upload limit.';
        }

        $detectedMime = $tmpName !== '' && is_file($tmpName)
            ? (string) mime_content_type($tmpName)
            : $mimeType;

        if (! in_array($detectedMime, self::ALLOWED_MIME_TYPES, true)) {
            $errors[] = $name . ' must be a JPG, PNG, or WEBP image.';
        }

        return $errors;
    }

    private function store(array $entry): bool
    {
        if ($this->connection instanceof PDO) {
            try {
                $this->connection->beginTransaction();

                $submission = $this->connection->prepare(
                    'INSERT INTO gallery_submissions
                        (submitter_name, submitter_email, description, status)
                     VALUES
                        (:submitter_name, :submitter_email, :description, :status)'
                );
                $submission->execute([
                    'submitter_name' => $entry['name'],
                    'submitter_email' => $entry['email'],
                    'description' => $entry['description'] !== '' ? $entry['description'] : null,
                    'status' => $entry['status'],
                ]);

                $submissionId = (int) $this->connection->lastInsertId();
                $fileInsert = $this->connection->prepare(
                    'INSERT INTO gallery_submission_files
                        (submission_id, file_path, original_name, mime_type, size_bytes)
                     VALUES
                        (:submission_id, :file_path, :original_name, :mime_type, :size_bytes)'
                );

                foreach ($entry['files'] as $file) {
                    $fileInsert->execute([
                        'submission_id' => $submissionId,
                        'file_path' => $file['stored_path'],
                        'original_name' => $file['original_name'],
                        'mime_type' => $file['mime_type'],
                        'size_bytes' => (int) $file['size_bytes'],
                    ]);
                }

                $this->connection->commit();

                return true;
            } catch (Throwable) {
                if ($this->connection->inTransaction()) {
                    $this->connection->rollBack();
                }

                return $this->appendManifest($entry);
            }
        }

        return $this->appendManifest($entry);
    }

    private function appendManifest(array $entry): bool
    {
        $existing = [];

        if (is_file($this->manifestPath)) {
            $json = file_get_contents($this->manifestPath);
            $decoded = is_string($json) ? json_decode($json, true) : null;
            if (is_array($decoded)) {
                $existing = $decoded;
            }
        } else {
            $directory = dirname($this->manifestPath);
            if (! is_dir($directory)) {
                mkdir($directory, 0775, true);
            }
        }

        $existing[] = $entry;

        return file_put_contents($this->manifestPath, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !== false;
    }
}
