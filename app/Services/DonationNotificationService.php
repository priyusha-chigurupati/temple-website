<?php

declare(strict_types=1);

final class DonationNotificationService
{
    private const OFFLINE_METHOD = 'temple-offline';

    public function __construct(private readonly string $manifestPath)
    {
    }

    public function submit(array $post, array $allowedMethods): array
    {
        $fullName = trim((string) ($post['full_name'] ?? ''));
        $paymentMethod = trim((string) ($post['payment_method'] ?? ''));
        $amount = trim((string) ($post['amount'] ?? ''));
        $referenceId = trim((string) ($post['reference_id'] ?? ''));
        $phone = trim((string) ($post['phone'] ?? ''));
        $email = trim((string) ($post['email'] ?? ''));
        $address = trim((string) ($post['address'] ?? ''));
        $message = trim((string) ($post['message'] ?? ''));
        $purpose = trim((string) ($post['purpose'] ?? ''));
        $errors = [];

        if ($fullName === '') {
            $errors[] = 'Please enter your full name.';
        }

        if ($paymentMethod === '' || ! array_key_exists($paymentMethod, $allowedMethods)) {
            $errors[] = 'Please choose how you donated.';
        }

        $normalizedAmount = $this->normalizeAmount($amount);

        if ($normalizedAmount === null) {
            $errors[] = 'Please enter a valid donation amount.';
        }

        if ($phone === '' || ! preg_match('/^[0-9+\-\s()]{7,20}$/', $phone)) {
            $errors[] = 'Please enter a valid phone number.';
        }

        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'Please enter a valid email address or leave it blank.';
        }

        if ($address === '') {
            $errors[] = 'Please enter your address.';
        }

        if ($paymentMethod !== '' && $paymentMethod !== self::OFFLINE_METHOD && $referenceId === '') {
            $errors[] = 'Reference ID is required for UPI and bank transfer donations.';
        }

        if ($errors !== []) {
            return [
                'ok' => false,
                'errors' => array_values(array_unique($errors)),
                'old' => [
                    'full_name' => $fullName,
                    'payment_method' => $paymentMethod,
                    'amount' => $amount,
                    'reference_id' => $referenceId,
                    'phone' => $phone,
                    'email' => $email,
                    'address' => $address,
                    'message' => $message,
                    'purpose' => $purpose,
                ],
            ];
        }

        $entry = [
            'id' => 'dn_' . gmdate('YmdHis') . '_' . bin2hex(random_bytes(4)),
            'full_name' => $fullName,
            'payment_method' => $paymentMethod,
            'payment_method_label' => $allowedMethods[$paymentMethod],
            'amount' => $normalizedAmount,
            'reference_id' => $referenceId,
            'phone' => $phone,
            'email' => $email,
            'address' => $address,
            'message' => $message,
            'purpose' => $purpose,
            'created_at' => gmdate('Y-m-d\TH:i:s\Z'),
            'status' => 'pending',
        ];

        $this->appendManifest($entry);

        return [
            'ok' => true,
            'message' => 'Your donation notice has been recorded. The temple team can review and follow up from the admin panel later.',
        ];
    }

    private function normalizeAmount(string $amount): ?string
    {
        $normalized = preg_replace('/[^0-9.]/', '', $amount);

        if (! is_string($normalized) || $normalized === '' || ! is_numeric($normalized)) {
            return null;
        }

        $value = (float) $normalized;

        if ($value <= 0) {
            return null;
        }

        if (floor($value) === $value) {
            return (string) (int) $value;
        }

        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }

    private function appendManifest(array $entry): void
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
        file_put_contents($this->manifestPath, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
