<?php

declare(strict_types=1);

final class AdminSettingsRepository
{
    private ?int $localeId = null;

    public function __construct(
        private readonly PDO $connection,
        private readonly string $localeCode = 'en'
    )
    {
    }

    public function settingsItems(): array
    {
        return [
            [
                'title' => 'Footer & Global Footer Content',
                'description' => 'Manage the public footer description, links, timings, newsletter copy, and social links.',
                'href' => route_url('/admin/settings/footer'),
                'status' => 'ready',
                'fields_count' => 24,
                'updated_label' => $this->updatedLabel(),
            ],
        ];
    }

    public function footerEditor(): array
    {
        return [
            'site_name' => (string) $this->setting('site.name', 'AnkammaThalli Temple'),
            'description' => (string) $this->setting('footer.description', ''),
            'quick_links' => $this->normalizeLinks($this->setting('footer.quick_links', []), 4, '/'),
            'address_lines' => $this->normalizeStringList($this->setting('footer.address.lines', []), 3),
            'morning' => (string) $this->setting('footer.address.morning', ''),
            'evening' => (string) $this->setting('footer.address.evening', ''),
            'newsletter_title' => (string) $this->setting('footer.newsletter.title', 'Newsletter'),
            'newsletter_description' => (string) $this->setting('footer.newsletter.description', ''),
            'social_links' => $this->normalizeSocialLinks($this->setting('footer.social_links', []), 3),
            'legal_links' => $this->normalizeLinks($this->setting('footer.legal', []), 2, '#'),
        ];
    }

    public function saveFooter(array $input): array
    {
        $old = $this->footerFormFromInput($input);
        $errors = [];

        if ($old['site_name'] === '') {
            $errors[] = 'Please enter the site name used in the footer.';
        }

        if ($old['description'] === '') {
            $errors[] = 'Please enter the footer description.';
        }

        if ($old['newsletter_title'] === '') {
            $errors[] = 'Please enter the newsletter title.';
        }

        foreach ($old['quick_links'] as $link) {
            if (($link['label'] ?? '') === '' || ($link['href'] ?? '') === '') {
                $errors[] = 'Please complete all quick link labels and href values.';
                break;
            }
        }

        foreach ($old['social_links'] as $link) {
            if (($link['label'] ?? '') === '' || ($link['href'] ?? '') === '' || ($link['short'] ?? '') === '') {
                $errors[] = 'Please complete all social link labels, short labels, and href values.';
                break;
            }
        }

        foreach ($old['legal_links'] as $link) {
            if (($link['label'] ?? '') === '' || ($link['href'] ?? '') === '') {
                $errors[] = 'Please complete all legal link labels and href values.';
                break;
            }
        }

        if ($errors !== []) {
            return [
                'ok' => false,
                'errors' => $errors,
                'old' => $old,
            ];
        }

        try {
            $this->connection->beginTransaction();

            $this->upsertSetting('site.name', $old['site_name'], true, 'string');
            $this->upsertSetting('footer.description', $old['description'], true, 'text');
            $this->upsertSetting('footer.quick_links', $old['quick_links'], true, 'json');
            $this->upsertSetting('footer.address.lines', array_values(array_filter($old['address_lines'], static fn (string $line): bool => trim($line) !== '')), true, 'json');
            $this->upsertSetting('footer.address.morning', $old['morning'], true, 'string');
            $this->upsertSetting('footer.address.evening', $old['evening'], true, 'string');
            $this->upsertSetting('footer.newsletter.title', $old['newsletter_title'], true, 'string');
            $this->upsertSetting('footer.newsletter.description', $old['newsletter_description'], true, 'text');
            $this->upsertSetting('footer.social_links', $old['social_links'], true, 'json');
            $this->upsertSetting('footer.legal', $old['legal_links'], true, 'json');

            $this->connection->commit();

            return ['ok' => true];
        } catch (Throwable) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            return [
                'ok' => false,
                'errors' => ['The footer settings could not be saved.'],
                'old' => $old,
            ];
        }
    }

    private function footerFormFromInput(array $input): array
    {
        $quickLinks = [];

        for ($index = 1; $index <= 4; $index++) {
            $quickLinks[] = [
                'label' => trim((string) ($input['quick_link_' . $index . '_label'] ?? '')),
                'href' => trim((string) ($input['quick_link_' . $index . '_href'] ?? '')),
            ];
        }

        $socialLinks = [];

        for ($index = 1; $index <= 3; $index++) {
            $socialLinks[] = [
                'label' => trim((string) ($input['social_link_' . $index . '_label'] ?? '')),
                'short' => trim((string) ($input['social_link_' . $index . '_short'] ?? '')),
                'href' => trim((string) ($input['social_link_' . $index . '_href'] ?? '')),
            ];
        }

        $legalLinks = [];

        for ($index = 1; $index <= 2; $index++) {
            $legalLinks[] = [
                'label' => trim((string) ($input['legal_link_' . $index . '_label'] ?? '')),
                'href' => trim((string) ($input['legal_link_' . $index . '_href'] ?? '')),
            ];
        }

        return [
            'site_name' => trim((string) ($input['site_name'] ?? '')),
            'description' => trim((string) ($input['description'] ?? '')),
            'quick_links' => $quickLinks,
            'address_lines' => [
                trim((string) ($input['address_line_1'] ?? '')),
                trim((string) ($input['address_line_2'] ?? '')),
                trim((string) ($input['address_line_3'] ?? '')),
            ],
            'morning' => trim((string) ($input['morning'] ?? '')),
            'evening' => trim((string) ($input['evening'] ?? '')),
            'newsletter_title' => trim((string) ($input['newsletter_title'] ?? '')),
            'newsletter_description' => trim((string) ($input['newsletter_description'] ?? '')),
            'social_links' => $socialLinks,
            'legal_links' => $legalLinks,
        ];
    }

    private function upsertSetting(string $key, mixed $value, bool $isTranslatable, string $type): void
    {
        $encodedValue = $type === 'json'
            ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : (string) $value;

        $lookup = $this->connection->prepare('SELECT id FROM site_settings WHERE setting_key = :setting_key LIMIT 1');
        $lookup->execute(['setting_key' => $key]);
        $settingId = $lookup->fetchColumn();

        if ($settingId === false) {
            $insert = $this->connection->prepare(
                'INSERT INTO site_settings (setting_key, setting_value, setting_type, is_translatable)
                 VALUES (:setting_key, :setting_value, :setting_type, :is_translatable)'
            );
            $insert->execute([
                'setting_key' => $key,
                'setting_value' => $encodedValue,
                'setting_type' => $type,
                'is_translatable' => $isTranslatable ? 1 : 0,
            ]);
            $settingId = (int) $this->connection->lastInsertId();
        } else {
            $update = $this->connection->prepare(
                'UPDATE site_settings
                 SET setting_value = :setting_value,
                     setting_type = :setting_type,
                     is_translatable = :is_translatable
                 WHERE id = :id'
            );
            $update->execute([
                'setting_value' => $encodedValue,
                'setting_type' => $type,
                'is_translatable' => $isTranslatable ? 1 : 0,
                'id' => (int) $settingId,
            ]);
            $settingId = (int) $settingId;
        }

        if (! $isTranslatable) {
            return;
        }

        $translation = $this->connection->prepare(
            'INSERT INTO site_setting_translations (setting_id, locale_id, setting_value)
             VALUES (:setting_id, :locale_id, :setting_value)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );
        $translation->execute([
            'setting_id' => $settingId,
            'locale_id' => $this->localeId(),
            'setting_value' => $encodedValue,
        ]);
    }

    private function setting(string $key, mixed $default = null): mixed
    {
        $statement = $this->connection->prepare(
            'SELECT s.setting_value,
                    s.setting_type,
                    COALESCE(t.setting_value, s.setting_value) AS resolved_value
             FROM site_settings s
             LEFT JOIN site_setting_translations t
               ON t.setting_id = s.id
              AND t.locale_id = :locale_id
             WHERE s.setting_key = :setting_key
             LIMIT 1'
        );
        $statement->execute([
            'locale_id' => $this->localeId(),
            'setting_key' => $key,
        ]);

        $row = $statement->fetch();

        if (! is_array($row)) {
            return $default;
        }

        $value = $row['resolved_value'] ?? null;

        if (! is_string($value) || trim($value) === '') {
            return $default;
        }

        if (($row['setting_type'] ?? 'string') === 'json') {
            $decoded = json_decode($value, true);

            return json_last_error() === JSON_ERROR_NONE ? $decoded : $default;
        }

        return $value;
    }

    private function normalizeLinks(mixed $links, int $count, string $fallbackHref): array
    {
        $items = is_array($links) ? array_values($links) : [];

        while (count($items) < $count) {
            $items[] = ['label' => '', 'href' => $fallbackHref];
        }

        return array_map(static function (mixed $item) use ($fallbackHref): array {
            $item = is_array($item) ? $item : [];

            return [
                'label' => (string) ($item['label'] ?? ''),
                'href' => (string) ($item['href'] ?? $fallbackHref),
            ];
        }, array_slice($items, 0, $count));
    }

    private function normalizeSocialLinks(mixed $links, int $count): array
    {
        $items = is_array($links) ? array_values($links) : [];

        while (count($items) < $count) {
            $items[] = ['label' => '', 'short' => '', 'href' => '#'];
        }

        return array_map(static function (mixed $item): array {
            $item = is_array($item) ? $item : [];

            return [
                'label' => (string) ($item['label'] ?? ''),
                'short' => (string) ($item['short'] ?? ''),
                'href' => (string) ($item['href'] ?? '#'),
            ];
        }, array_slice($items, 0, $count));
    }

    private function normalizeStringList(mixed $value, int $count): array
    {
        $items = is_array($value) ? array_values($value) : [];

        while (count($items) < $count) {
            $items[] = '';
        }

        return array_map(static fn (mixed $item): string => (string) $item, array_slice($items, 0, $count));
    }

    private function updatedLabel(): string
    {
        $statement = $this->connection->query('SELECT MAX(updated_at) FROM site_settings');
        $value = $statement->fetchColumn();

        if (! is_string($value) || trim($value) === '') {
            return 'Settings pending';
        }

        $timestamp = strtotime($value);

        return $timestamp === false ? 'Settings pending' : date('M j, Y', $timestamp);
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
            throw new RuntimeException('The configured locale is not available in the database.');
        }

        $this->localeId = (int) $resolved;

        return $this->localeId;
    }
}
