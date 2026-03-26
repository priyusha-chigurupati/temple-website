<?php

declare(strict_types=1);

require __DIR__ . '/../app/Support/Env.php';
require __DIR__ . '/../app/Config/Database.php';

$content = require __DIR__ . '/../data/site.php';
$site = is_array($content['site'] ?? null) ? $content['site'] : [];
$contact = is_array($content['pages']['contact'] ?? null) ? $content['pages']['contact'] : [];
$contactInfo = is_array($contact['info']['items'] ?? null) ? $contact['info']['items'] : [];
$map = is_array($contact['map'] ?? null) ? $contact['map'] : [];

$phone = '';
$email = '';
$address = '';

foreach ($contactInfo as $item) {
    $title = strtolower((string) ($item['title'] ?? ''));
    $lines = is_array($item['lines'] ?? null) ? $item['lines'] : [];

    if ($title === 'temple location') {
        $parts = array_map(static fn (array $line): string => trim((string) ($line['text'] ?? '')), $lines);
        $address = implode(' ', array_filter($parts, static fn (string $value): bool => $value !== ''));
    }

    if ($title === 'direct reach') {
        foreach ($lines as $line) {
            $text = (string) ($line['text'] ?? '');
            if (str_starts_with($text, 'Phone:')) {
                $phone = trim(substr($text, strlen('Phone:')));
            }
            if (str_starts_with($text, 'Email:')) {
                $email = trim(substr($text, strlen('Email:')));
            }
        }
    }
}

$locale = Env::get('APP_DEFAULT_LOCALE', 'en') ?? 'en';
$connection = Database::connection(dirname(__DIR__));

$localeStatement = $connection->prepare('SELECT id FROM locales WHERE code = :code LIMIT 1');
$localeStatement->execute(['code' => $locale]);
$localeId = $localeStatement->fetchColumn();

if ($localeId === false) {
    throw new RuntimeException('Default locale not found in locales table.');
}

$upsertSetting = static function (PDO $connection, int $localeId, string $key, mixed $value, string $type, bool $translatable): void {
    $encodedValue = $type === 'json'
        ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        : (string) $value;

    $lookup = $connection->prepare('SELECT id FROM site_settings WHERE setting_key = :setting_key LIMIT 1');
    $lookup->execute(['setting_key' => $key]);
    $settingId = $lookup->fetchColumn();

    if ($settingId === false) {
        $insert = $connection->prepare(
            'INSERT INTO site_settings (setting_key, setting_value, setting_type, is_translatable)
             VALUES (:setting_key, :setting_value, :setting_type, :is_translatable)'
        );
        $insert->execute([
            'setting_key' => $key,
            'setting_value' => $encodedValue,
            'setting_type' => $type,
            'is_translatable' => $translatable ? 1 : 0,
        ]);
        $settingId = (int) $connection->lastInsertId();
    } else {
        $update = $connection->prepare(
            'UPDATE site_settings
             SET setting_value = :setting_value,
                 setting_type = :setting_type,
                 is_translatable = :is_translatable
             WHERE id = :id'
        );
        $update->execute([
            'setting_value' => $encodedValue,
            'setting_type' => $type,
            'is_translatable' => $translatable ? 1 : 0,
            'id' => (int) $settingId,
        ]);
        $settingId = (int) $settingId;
    }

    if (! $translatable) {
        return;
    }

    $translation = $connection->prepare(
        'INSERT INTO site_setting_translations (setting_id, locale_id, setting_value)
         VALUES (:setting_id, :locale_id, :setting_value)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );
    $translation->execute([
        'setting_id' => $settingId,
        'locale_id' => $localeId,
        'setting_value' => $encodedValue,
    ]);
};

$connection->beginTransaction();

try {
    $upsertSetting($connection, (int) $localeId, 'site.name', $site['name'] ?? 'AnkammaThalli Temple', 'string', true);
    $upsertSetting($connection, (int) $localeId, 'site.tagline', $site['tagline'] ?? '', 'string', true);
    $upsertSetting($connection, (int) $localeId, 'site.contact.phone', $phone, 'string', false);
    $upsertSetting($connection, (int) $localeId, 'site.contact.email', $email, 'string', false);
    $upsertSetting($connection, (int) $localeId, 'site.contact.address', $address, 'text', true);
    $upsertSetting($connection, (int) $localeId, 'site.contact.map_url', $map['href'] ?? '', 'string', false);

    $connection->commit();

    echo 'General settings seeded successfully.' . PHP_EOL;
} catch (Throwable $exception) {
    if ($connection->inTransaction()) {
        $connection->rollBack();
    }

    fwrite(STDERR, 'General settings seed failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
