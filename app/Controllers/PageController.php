<?php

declare(strict_types=1);

final class PageController
{
    public function __construct(private readonly ContentRepository $content)
    {
    }

    public function home(): void
    {
        $site = $this->content->site();
        $page = $this->content->home();

        View::render('pages/home', [
            'site' => $site,
            'navigation' => $this->content->navigation(),
            'footer' => $this->content->footer(),
            'page' => $page,
            'activePage' => 'home',
            'metaTitle' => $page['meta']['title'],
            'metaDescription' => $page['meta']['description'],
        ]);
    }

    public function placeholder(string $slug): void
    {
        $site = $this->content->site();
        $page = $this->content->page($slug);

        View::render('pages/placeholder', [
            'site' => $site,
            'navigation' => $this->content->navigation(),
            'footer' => $this->content->footer(),
            'page' => $page,
            'activePage' => $slug,
            'metaTitle' => $page['meta']['title'] ?? ($site['name'] . ' | Coming Soon'),
            'metaDescription' => $page['meta']['description'] ?? 'This page is queued for implementation in a later milestone.',
        ]);
    }

    public function notFound(): void
    {
        $site = $this->content->site();

        View::render('pages/placeholder', [
            'site' => $site,
            'navigation' => $this->content->navigation(),
            'footer' => $this->content->footer(),
            'page' => [
                'eyebrow' => 'Page Not Found',
                'title' => 'The page you requested is not available.',
                'description' => 'Please return to the Home page and continue exploring the temple website.',
                'cta' => [
                    'label' => 'Return Home',
                    'href' => '/',
                ],
            ],
            'activePage' => '',
            'metaTitle' => '404 | ' . $site['name'],
            'metaDescription' => 'The requested page could not be found.',
        ]);
    }
}
