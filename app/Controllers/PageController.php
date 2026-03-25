<?php

declare(strict_types=1);

final class PageController
{
    public function __construct(
        private readonly ContentRepository $content,
        private readonly GallerySubmissionService $gallerySubmissions
    )
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
        $selectedCategory = query_value('category') ?? 'all';

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->handleGallerySubmission($selectedCategory);
        }

        $page = $this->content->gallery($selectedCategory);
        $page['submission_state'] = flash_pull('gallery_submission_state', []);
        $page['submission_form'] = flash_pull('gallery_submission_form', [
            'name' => '',
            'email' => '',
            'description' => '',
        ]);

        $this->renderPage('gallery', 'pages/gallery', $page);
    }

    public function events(): void
    {
        $this->renderPage('events', 'pages/events', $this->content->page('events'));
    }

    public function donations(): void
    {
        $page = $this->content->page('donations');
        $page['prefill'] = $this->donationPrefill();

        $this->renderPage('donations', 'pages/donations', $page);
    }

    public function contact(): void
    {
        $this->renderPage('contact', 'pages/contact', $this->content->page('contact'));
    }

    public function blog(): void
    {
        $this->renderPage('blog', 'pages/blog', $this->content->page('blog'));
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

    private function handleGallerySubmission(string $selectedCategory): never
    {
        $result = $this->gallerySubmissions->submit($_POST, $_FILES);
        $redirectUrl = route_url_with_query('/gallery', [
            'category' => $selectedCategory === 'all' ? null : $selectedCategory,
            'modal' => $result['ok'] ? null : 'submit',
        ]);

        if ($result['ok']) {
            flash_set('gallery_submission_state', [
                'type' => 'success',
                'message' => $result['message'],
            ]);
            flash_set('gallery_submission_form', [
                'name' => '',
                'email' => '',
                'description' => '',
            ]);
            redirect_to($redirectUrl);
        }

        flash_set('gallery_submission_state', [
            'type' => 'error',
            'message' => implode(' ', $result['errors'] ?? ['The gallery submission could not be processed.']),
            'auto_open' => true,
        ]);
        flash_set('gallery_submission_form', $result['old'] ?? [
            'name' => '',
            'email' => '',
            'description' => '',
        ]);

        redirect_to($redirectUrl);
    }

    private function donationPrefill(): array
    {
        $customAmount = $this->sanitizeAmount(query_value('custom_amount'));

        if ($customAmount !== null) {
            return [
                'purpose' => 'General Donation',
                'amount' => $customAmount,
                'message' => 'Purpose: General Donation',
            ];
        }

        $selectedPurpose = query_value('purpose');

        if ($selectedPurpose === null) {
            return [];
        }

        $quickOptions = $this->content->home()['donation_section']['quick_options'] ?? [];

        foreach ($quickOptions as $option) {
            if (($option['purpose'] ?? '') !== $selectedPurpose) {
                continue;
            }

            $presetAmount = $this->sanitizeAmount((string) ($option['amount_value'] ?? ''));

            if ($presetAmount === null) {
                break;
            }

            return [
                'purpose' => $selectedPurpose,
                'amount' => $presetAmount,
                'message' => 'Purpose: ' . $selectedPurpose,
            ];
        }

        return [];
    }

    private function sanitizeAmount(?string $amount): ?string
    {
        if ($amount === null) {
            return null;
        }

        $normalized = preg_replace('/[^0-9.]/', '', $amount);

        if (! is_string($normalized) || $normalized === '' || ! is_numeric($normalized)) {
            return null;
        }

        $floatValue = (float) $normalized;

        if ($floatValue <= 0) {
            return null;
        }

        if (floor($floatValue) === $floatValue) {
            return (string) (int) $floatValue;
        }

        return rtrim(rtrim(number_format($floatValue, 2, '.', ''), '0'), '.');
    }
}
