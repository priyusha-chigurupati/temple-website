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
require __DIR__ . '/../app/Repositories/UserRepository.php';
require __DIR__ . '/../app/Repositories/AdminDashboardRepository.php';
require __DIR__ . '/../app/Repositories/AdminEventRepository.php';
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
$userRepository = null;
$adminDashboardRepository = null;
$adminEventRepository = null;
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
    $userRepository = new UserRepository($connection);
    $adminDashboardRepository = new AdminDashboardRepository(
        $connection,
        Env::get('APP_DEFAULT_LOCALE', 'en') ?? 'en'
    );
    $adminEventRepository = new AdminEventRepository(
        $connection,
        Env::get('APP_DEFAULT_LOCALE', 'en') ?? 'en'
    );
} catch (Throwable) {
    $connection = null;
    $eventRepository = null;
    $blogRepository = null;
    $galleryRepository = null;
    $pageRepository = null;
    $userRepository = null;
    $adminDashboardRepository = null;
    $adminEventRepository = null;
}

$repository = new ContentRepository($content, $eventRepository, $blogRepository, $galleryRepository, $pageRepository);
$gallerySubmissions = new GallerySubmissionService(
    dirname(__DIR__) . '/storage/data/gallery_submissions.json',
    dirname(__DIR__) . '/storage/uploads/gallery-submissions'
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
    dirname(__DIR__) . '/storage/data/newsletter_subscriptions.json'
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
) {
    $adminController = new AdminController(
        new AdminAuthService($userRepository),
        $adminDashboardRepository,
        $adminEventRepository
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
    '/admin/logout' => static fn () => $adminController instanceof AdminController ? $adminController->logout() : $controller->placeholder('admin'),
    '/admin/events' => static fn () => $adminController instanceof AdminController ? $adminController->eventsIndex() : $controller->placeholder('admin'),
    '/admin/events/new' => static fn () => $adminController instanceof AdminController ? $adminController->eventsCreate() : $controller->placeholder('admin'),
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

http_response_code(404);
$controller->notFound();
