<?php

declare(strict_types=1);

final class NewsletterSubscriptionService
{
    public function __construct(
        private readonly string $manifestPath,
        private readonly ?PDO $connection = null
    )
    {
    }

    public function subscribe(array $post, string $source): array
    {
        $email = trim((string) ($post['email'] ?? ''));

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return [
                'ok' => false,
                'errors' => ['Please enter a valid email address.'],
                'old' => ['email' => $email],
            ];
        }

        $existing = $this->readManifest();

        foreach ($existing as $entry) {
            if (($entry['email'] ?? '') === $email) {
                return [
                    'ok' => true,
                    'message' => 'This email is already subscribed for temple updates.',
                ];
            }
        }

        $existing[] = [
            'id' => 'ns_' . gmdate('YmdHis') . '_' . bin2hex(random_bytes(4)),
            'email' => $email,
            'source' => $source,
            'status' => 'active',
            'created_at' => gmdate('Y-m-d\TH:i:s\Z'),
        ];

        if (! $this->store($email, $source, $existing)) {
            return [
                'ok' => false,
                'errors' => ['The subscription could not be saved. Please try again.'],
                'old' => ['email' => $email],
            ];
        }

        return [
            'ok' => true,
            'message' => 'You have been subscribed to temple updates.',
        ];
    }

    private function store(string $email, string $source, array $fallbackEntries): bool
    {
        if ($this->connection instanceof PDO) {
            try {
                $statement = $this->connection->prepare(
                    'INSERT INTO newsletter_subscriptions (email, source, status)
                     VALUES (:email, :source, :status)
                     ON DUPLICATE KEY UPDATE
                        source = VALUES(source),
                        status = VALUES(status)'
                );
                $statement->execute([
                    'email' => $email,
                    'source' => $source,
                    'status' => 'active',
                ]);

                return true;
            } catch (Throwable) {
                return $this->writeManifest($fallbackEntries);
            }
        }

        return $this->writeManifest($fallbackEntries);
    }

    private function readManifest(): array
    {
        if (! is_file($this->manifestPath)) {
            return [];
        }

        $json = file_get_contents($this->manifestPath);
        $decoded = is_string($json) ? json_decode($json, true) : null;

        return is_array($decoded) ? $decoded : [];
    }

    private function writeManifest(array $entries): bool
    {
        $directory = dirname($this->manifestPath);

        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        return file_put_contents($this->manifestPath, json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !== false;
    }
}
