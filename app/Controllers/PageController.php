<?php

declare(strict_types=1);

final class PageController
{
    public function __construct(private readonly ContentRepository $content)
    {
    }

    public function home(): void
    {
        $this->renderPage('home', 'pages/home', $this->content->home());
    }

    public function about(): void
    {
        $this->renderPage('about', 'pages/about', $this->content->page('about'));
    }

    public function gallery(): void
    {
        $this->renderPage('gallery', 'pages/gallery', $this->content->page('gallery'));
    }

    public function events(): void
    {
        $this->renderPage('events', 'pages/events', $this->content->page('events'));
    }

    public function donations(): void
    {
        $this->renderPage('donations', 'pages/donations', $this->content->page('donations'));
    }

    public function contact(): void
    {
        $this->renderPage('contact', 'pages/contact', $this->content->page('contact'));
    }

    public function placeholder(string $slug): void
    {
        $page = $this->content->page($slug);

        $this->renderPage($slug, 'pages/placeholder', $page);
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

    private function renderPage(string $activePage, string $view, array $page): void
    {
        $site = $this->content->site();

        View::render($view, [
            'site' => $site,
            'navigation' => $this->content->navigation(),
            'footer' => $this->content->footer(),
            'page' => $page,
            'activePage' => $activePage,
            'metaTitle' => $page['meta']['title'] ?? ($site['name'] . ' | Coming Soon'),
            'metaDescription' => $page['meta']['description'] ?? 'This page is queued for implementation in a later milestone.',
        ]);
    }
}
