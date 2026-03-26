<?php

declare(strict_types=1);

final class SiteSettingsRepository
{
    private ?int $localeId = null;

    public function __construct(
        private readonly PDO $connection,
        private readonly string $localeCode = 'en'
    )
    {
    }

    public function siteOverrides(): array
    {
        return [
            'name' => $this->setting('site.name'),
            'tagline' => $this->setting('site.tagline'),
        ];
    }

    public function footerOverrides(): array
    {
        return [
            'description' => $this->setting('footer.description'),
            'quick_links' => $this->setting('footer.quick_links', []),
            'address' => [
                'lines' => $this->setting('footer.address.lines', []),
                'morning' => $this->setting('footer.address.morning'),
                'evening' => $this->setting('footer.address.evening'),
            ],
            'newsletter' => [
                'title' => $this->setting('footer.newsletter.title'),
                'description' => $this->setting('footer.newsletter.description'),
            ],
            'social_links' => $this->setting('footer.social_links', []),
            'legal' => $this->setting('footer.legal', []),
        ];
    }

    public function navigationOverrides(): array
    {
        return $this->setting('header.navigation_items', []);
    }

    public function headerOverrides(): array
    {
        return [
            'primary_cta' => [
                'label' => $this->setting('header.primary_cta.label'),
                'href' => $this->setting('header.primary_cta.href'),
            ],
        ];
    }

    public function seoOverrides(): array
    {
        return [
            'title_suffix' => $this->setting('seo.title_suffix'),
            'default_description' => $this->setting('seo.default_description'),
        ];
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
