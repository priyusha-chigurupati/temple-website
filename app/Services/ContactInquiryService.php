<?php

declare(strict_types=1);

final class ContactInquiryService
{
    public function __construct(
        private readonly string $manifestPath,
        private readonly ?PDO $connection = null
    )
    {
    }

    public function submit(array $post, array $allowedSubjects): array
    {
        $fullName = trim((string) ($post['full_name'] ?? ''));
        $email = trim((string) ($post['email'] ?? ''));
        $subject = trim((string) ($post['subject'] ?? ''));
        $message = trim((string) ($post['message'] ?? ''));
        $errors = [];

        if ($fullName === '') {
            $errors[] = 'Please enter your full name.';
        }

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'Please enter a valid email address.';
        }

        if ($subject === '' || ! in_array($subject, $allowedSubjects, true)) {
            $errors[] = 'Please select a valid subject.';
        }

        if ($message === '') {
            $errors[] = 'Please enter your message.';
        }

        if ($errors !== []) {
            return [
                'ok' => false,
                'errors' => array_values(array_unique($errors)),
                'old' => [
                    'full_name' => $fullName,
                    'email' => $email,
                    'subject' => $subject,
                    'message' => $message,
                ],
            ];
        }

        $entry = [
            'id' => 'ci_' . gmdate('YmdHis') . '_' . bin2hex(random_bytes(4)),
            'full_name' => $fullName,
            'email' => $email,
            'subject' => $subject,
            'message' => $message,
            'status' => 'pending',
            'created_at' => gmdate('Y-m-d\TH:i:s\Z'),
        ];

        if (! $this->store($entry)) {
            return [
                'ok' => false,
                'errors' => ['The contact message could not be saved. Please try again.'],
                'old' => [
                    'full_name' => $fullName,
                    'email' => $email,
                    'subject' => $subject,
                    'message' => $message,
                ],
            ];
        }

        return [
            'ok' => true,
            'message' => 'Your message has been sent to the temple team.',
        ];
    }

    private function store(array $entry): bool
    {
        if ($this->connection instanceof PDO) {
            try {
                $statement = $this->connection->prepare(
                    'INSERT INTO contact_inquiries (full_name, email, subject, message, status)
                     VALUES (:full_name, :email, :subject, :message, :status)'
                );
                $statement->execute([
                    'full_name' => $entry['full_name'],
                    'email' => $entry['email'],
                    'subject' => $entry['subject'],
                    'message' => $entry['message'],
                    'status' => $entry['status'],
                ]);

                return true;
            } catch (Throwable) {
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
