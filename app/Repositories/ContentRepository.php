<?php

declare(strict_types=1);

final class ContentRepository
{
    public function __construct(private readonly array $content)
    {
    }

    public function site(): array
    {
        return $this->content['site'];
    }

    public function navigation(): array
    {
        return $this->content['navigation'];
    }

    public function footer(): array
    {
        return $this->content['footer'];
    }

    public function home(): array
    {
        return $this->content['pages']['home'];
    }

    public function page(string $slug): array
    {
        return $this->content['pages'][$slug] ?? [];
    }
}
