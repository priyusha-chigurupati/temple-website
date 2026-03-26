<?php

declare(strict_types=1);

require __DIR__ . '/../app/Support/helpers.php';
require __DIR__ . '/../app/Support/Env.php';
require __DIR__ . '/../app/Support/View.php';
require __DIR__ . '/../app/Config/Database.php';
require __DIR__ . '/../app/Repositories/ContentRepository.php';
require __DIR__ . '/../app/Repositories/EventRepository.php';
require __DIR__ . '/../app/Repositories/BlogRepository.php';
require __DIR__ . '/../app/Repositories/GalleryRepository.php';
require __DIR__ . '/../app/Repositories/PageRepository.php';
require __DIR__ . '/../app/Repositories/SiteSettingsRepository.php';
require __DIR__ . '/../app/Repositories/UserRepository.php';
require __DIR__ . '/../app/Repositories/AdminAccountRepository.php';
require __DIR__ . '/../app/Repositories/AdminDashboardRepository.php';
require __DIR__ . '/../app/Repositories/AdminEventRepository.php';
require __DIR__ . '/../app/Repositories/AdminGalleryRepository.php';
require __DIR__ . '/../app/Repositories/AdminBlogRepository.php';
require __DIR__ . '/../app/Repositories/AdminPageRepository.php';
require __DIR__ . '/../app/Repositories/AdminSettingsRepository.php';
require __DIR__ . '/../app/Repositories/AdminSubmissionRepository.php';
require __DIR__ . '/../app/Services/GallerySubmissionService.php';
require __DIR__ . '/../app/Services/DonationNotificationService.php';
require __DIR__ . '/../app/Services/ContactInquiryService.php';
require __DIR__ . '/../app/Services/NewsletterSubscriptionService.php';
require __DIR__ . '/../app/Services/AdminAuthService.php';
require __DIR__ . '/../app/Controllers/PageController.php';
require __DIR__ . '/../app/Controllers/AdminController.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$content = require __DIR__ . '/../data/site.php';
$eventRepository = null;
$blogRepository = null;
$galleryRepository = null;
$pageRepository = null;
$siteSettingsRepository = null;
$userRepository = null;
$adminDashboardRepository = null;
$adminEventRepository = null;
$adminGalleryRepository = null;
$adminBlogRepository = null;
$adminPageRepository = null;
$adminSettingsRepository = null;
$adminAccountRepository = null;
$adminSubmissionRepository = null;
$connection = null;

try {
    $connection = Database::connection(dirname(__DIR__));
    $eventRepository = new EventRepository(
        $connection,
        Env::get('APP_DEFAULT_LOCALE', 'en') ?? 'en'
    );
    $blogRepository = new BlogRepository(
        $connection,
        Env::get('APP_DEFAULT_LOCALE', 'en') ?? 'en'
    );
    $galleryRepository = new GalleryRepository(
        $connection,
        Env::get('APP_DEFAULT_LOCALE', 'en') ?? 'en'
    );
    $pageRepository = new PageRepository(
        $connection,
        Env::get('APP_DEFAULT_LOCALE', 'en') ?? 'en'
    );
    $siteSettingsRepository = new SiteSettingsRepository(
        $connection,
        Env::get('APP_DEFAULT_LOCALE', 'en') ?? 'en'
    );
    $userRepository = new UserRepository($connection);
    $adminDashboardRepository = new AdminDashboardRepository(
        $connection,
        Env::get('APP_DEFAULT_LOCALE', 'en') ?? 'en'
    );
    $adminEventRepository = new AdminEventRepository(
        $connection,
        Env::get('APP_DEFAULT_LOCALE', 'en') ?? 'en'
    );
    $adminGalleryRepository = new AdminGalleryRepository(
        $connection,
        Env::get('APP_DEFAULT_LOCALE', 'en') ?? 'en'
    );
    $adminBlogRepository = new AdminBlogRepository(
        $connection,
        Env::get('APP_DEFAULT_LOCALE', 'en') ?? 'en'
    );
    $adminPageRepository = new AdminPageRepository(
        $connection,
        Env::get('APP_DEFAULT_LOCALE', 'en') ?? 'en'
    );
    $adminSettingsRepository = new AdminSettingsRepository(
        $connection,
        Env::get('APP_DEFAULT_LOCALE', 'en') ?? 'en'
    );
    $adminAccountRepository = new AdminAccountRepository(
        $connection,
        $userRepository
    );
    $adminSubmissionRepository = new AdminSubmissionRepository($connection);
} catch (Throwable) {
    $connection = null;
    $eventRepository = null;
    $blogRepository = null;
    $galleryRepository = null;
    $pageRepository = null;
    $siteSettingsRepository = null;
    $userRepository = null;
    $adminDashboardRepository = null;
    $adminEventRepository = null;
    $adminGalleryRepository = null;
    $adminBlogRepository = null;
    $adminPageRepository = null;
    $adminSettingsRepository = null;
    $adminAccountRepository = null;
    $adminSubmissionRepository = null;
}

$repository = new ContentRepository($content, $eventRepository, $blogRepository, $galleryRepository, $pageRepository, $siteSettingsRepository);
$gallerySubmissions = new GallerySubmissionService(
    dirname(__DIR__) . '/storage/data/gallery_submissions.json',
    dirname(__DIR__) . '/storage/uploads/gallery-submissions',
    $connection
);
$donationNotifications = new DonationNotificationService(
    dirname(__DIR__) . '/storage/data/donation_notifications.json',
    $connection
);
$contactInquiries = new ContactInquiryService(
    dirname(__DIR__) . '/storage/data/contact_inquiries.json',
    $connection
);
$newsletterSubscriptions = new NewsletterSubscriptionService(
    dirname(__DIR__) . '/storage/data/newsletter_subscriptions.json',
    $connection
);
$controller = new PageController(
    $repository,
    $gallerySubmissions,
    $donationNotifications,
    $contactInquiries,
    $newsletterSubscriptions
);
$adminController = null;

if (
    $userRepository instanceof UserRepository
    && $adminDashboardRepository instanceof AdminDashboardRepository
    && $adminEventRepository instanceof AdminEventRepository
    && $adminGalleryRepository instanceof AdminGalleryRepository
    && $adminBlogRepository instanceof AdminBlogRepository
    && $adminPageRepository instanceof AdminPageRepository
    && $adminSettingsRepository instanceof AdminSettingsRepository
    && $adminAccountRepository instanceof AdminAccountRepository
    && $adminSubmissionRepository instanceof AdminSubmissionRepository
) {
    $adminController = new AdminController(
        new AdminAuthService(
            $userRepository,
            max(60, (int) (Env::get('ADMIN_SESSION_TIMEOUT', '1800') ?? '1800'))
        ),
        $adminDashboardRepository,
        $adminEventRepository,
        $adminGalleryRepository,
        $adminBlogRepository,
        $adminPageRepository,
        $adminSettingsRepository,
        $adminAccountRepository,
        $adminSubmissionRepository
    );
}

$path = request_path();

$routes = [
    '/' => static fn () => $controller->home(),
    '/about' => static fn () => $controller->about(),
    '/gallery' => static fn () => $controller->gallery(),
    '/events' => static fn () => $controller->events(),
    '/donations' => static fn () => $controller->donations(),
    '/contact' => static fn () => $controller->contact(),
    '/blog' => static fn () => $controller->blog(),
    '/admin' => static fn () => $adminController instanceof AdminController ? $adminController->dashboard() : $controller->placeholder('admin'),
    '/admin/login' => static fn () => $adminController instanceof AdminController ? $adminController->login() : $controller->placeholder('admin'),
    '/admin/forgot-password' => static fn () => $adminController instanceof AdminController ? $adminController->forgotPassword() : $controller->placeholder('admin'),
    '/admin/reset-password' => static fn () => $adminController instanceof AdminController ? $adminController->resetPassword() : $controller->placeholder('admin'),
    '/admin/logout' => static fn () => $adminController instanceof AdminController ? $adminController->logout() : $controller->placeholder('admin'),
    '/admin/account' => static fn () => $adminController instanceof AdminController ? $adminController->accountProfile() : $controller->placeholder('admin'),
    '/admin/account/password' => static fn () => $adminController instanceof AdminController ? $adminController->accountPassword() : $controller->placeholder('admin'),
    '/admin/submissions' => static fn () => $adminController instanceof AdminController ? $adminController->submissionsIndex() : $controller->placeholder('admin'),
    '/admin/submissions/contact' => static fn () => $adminController instanceof AdminController ? $adminController->submissionsContactIndex() : $controller->placeholder('admin'),
    '/admin/submissions/donations' => static fn () => $adminController instanceof AdminController ? $adminController->submissionsDonationsIndex() : $controller->placeholder('admin'),
    '/admin/submissions/gallery' => static fn () => $adminController instanceof AdminController ? $adminController->submissionsGalleryIndex() : $controller->placeholder('admin'),
    '/admin/submissions/newsletter' => static fn () => $adminController instanceof AdminController ? $adminController->submissionsNewsletterIndex() : $controller->placeholder('admin'),
    '/admin/events' => static fn () => $adminController instanceof AdminController ? $adminController->eventsIndex() : $controller->placeholder('admin'),
    '/admin/events/new' => static fn () => $adminController instanceof AdminController ? $adminController->eventsCreate() : $controller->placeholder('admin'),
    '/admin/pages' => static fn () => $adminController instanceof AdminController ? $adminController->pagesIndex() : $controller->placeholder('admin'),
    '/admin/pages/home' => static fn () => $adminController instanceof AdminController ? $adminController->pagesHome() : $controller->placeholder('admin'),
    '/admin/pages/about' => static fn () => $adminController instanceof AdminController ? $adminController->pagesAbout() : $controller->placeholder('admin'),
    '/admin/pages/contact' => static fn () => $adminController instanceof AdminController ? $adminController->pagesContact() : $controller->placeholder('admin'),
    '/admin/pages/donations' => static fn () => $adminController instanceof AdminController ? $adminController->pagesDonations() : $controller->placeholder('admin'),
    '/admin/settings' => static fn () => $adminController instanceof AdminController ? $adminController->settingsIndex() : $controller->placeholder('admin'),
    '/admin/settings/general' => static fn () => $adminController instanceof AdminController ? $adminController->settingsGeneral() : $controller->placeholder('admin'),
    '/admin/settings/header' => static fn () => $adminController instanceof AdminController ? $adminController->settingsHeader() : $controller->placeholder('admin'),
    '/admin/settings/seo' => static fn () => $adminController instanceof AdminController ? $adminController->settingsSeo() : $controller->placeholder('admin'),
    '/admin/settings/footer' => static fn () => $adminController instanceof AdminController ? $adminController->settingsFooter() : $controller->placeholder('admin'),
    '/admin/settings/notifications' => static fn () => $adminController instanceof AdminController ? $adminController->settingsNotifications() : $controller->placeholder('admin'),
    '/admin/media' => static fn () => $adminController instanceof AdminController ? $adminController->galleryIndex() : $controller->placeholder('admin'),
    '/admin/media/new' => static fn () => $adminController instanceof AdminController ? $adminController->galleryCreate() : $controller->placeholder('admin'),
    '/admin/blog' => static fn () => $adminController instanceof AdminController ? $adminController->blogIndex() : $controller->placeholder('admin'),
    '/admin/blog/new' => static fn () => $adminController instanceof AdminController ? $adminController->blogCreate() : $controller->placeholder('admin'),
];

if (isset($routes[$path])) {
    $routes[$path]();
    exit;
}

if (str_starts_with($path, '/events/')) {
    $slug = trim(substr($path, strlen('/events/')), '/');

    if ($slug !== '') {
        $controller->eventDetail($slug);
        exit;
    }
}

if (str_starts_with($path, '/blog/')) {
    $slug = trim(substr($path, strlen('/blog/')), '/');

    if ($slug !== '') {
        $controller->blogDetail($slug);
        exit;
    }
}

if ($adminController instanceof AdminController && preg_match('#^/admin/events/(\d+)/(edit|delete)$#', $path, $matches) === 1) {
    $eventId = (int) $matches[1];

    if ($matches[2] === 'edit') {
        $adminController->eventsEdit($eventId);
        exit;
    }

    $adminController->eventsDelete($eventId);
    exit;
}

if ($adminController instanceof AdminController && preg_match('#^/admin/media/(\d+)/(edit|delete)$#', $path, $matches) === 1) {
    $itemId = (int) $matches[1];

    if ($matches[2] === 'edit') {
        $adminController->galleryEdit($itemId);
        exit;
    }

    $adminController->galleryDelete($itemId);
    exit;
}

if ($adminController instanceof AdminController && preg_match('#^/admin/blog/(\d+)/(edit|delete)$#', $path, $matches) === 1) {
    $postId = (int) $matches[1];

    if ($matches[2] === 'edit') {
        $adminController->blogEdit($postId);
        exit;
    }

    $adminController->blogDelete($postId);
    exit;
}

if ($adminController instanceof AdminController && preg_match('#^/admin/submissions/contact/(\d+)(/review)?$#', $path, $matches) === 1) {
    $submissionId = (int) $matches[1];

    if (($matches[2] ?? '') === '/review') {
        $adminController->submissionsContactReview($submissionId);
        exit;
    }

    $adminController->submissionsContactShow($submissionId);
    exit;
}

if ($adminController instanceof AdminController && preg_match('#^/admin/submissions/donations/(\d+)(/review)?$#', $path, $matches) === 1) {
    $submissionId = (int) $matches[1];

    if (($matches[2] ?? '') === '/review') {
        $adminController->submissionsDonationReview($submissionId);
        exit;
    }

    $adminController->submissionsDonationShow($submissionId);
    exit;
}

if ($adminController instanceof AdminController && preg_match('#^/admin/submissions/gallery/(\d+)(/review)?$#', $path, $matches) === 1) {
    $submissionId = (int) $matches[1];

    if (($matches[2] ?? '') === '/review') {
        $adminController->submissionsGalleryReview($submissionId);
        exit;
    }

    $adminController->submissionsGalleryShow($submissionId);
    exit;
}

if ($adminController instanceof AdminController && preg_match('#^/admin/submissions/newsletter/(\d+)/(activate|unsubscribe)$#', $path, $matches) === 1) {
    $subscriptionId = (int) $matches[1];

    if ($matches[2] === 'activate') {
        $adminController->submissionsNewsletterActivate($subscriptionId);
        exit;
    }

    $adminController->submissionsNewsletterUnsubscribe($subscriptionId);
    exit;
}

http_response_code(404);
$controller->notFound();
