<?php

declare(strict_types=1);

require __DIR__ . '/../app/Support/helpers.php';
require __DIR__ . '/../app/Support/View.php';
require __DIR__ . '/../app/Repositories/ContentRepository.php';
require __DIR__ . '/../app/Services/GallerySubmissionService.php';
require __DIR__ . '/../app/Services/DonationNotificationService.php';
require __DIR__ . '/../app/Controllers/PageController.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$content = require __DIR__ . '/../data/site.php';
$repository = new ContentRepository($content);
$gallerySubmissions = new GallerySubmissionService(
    dirname(__DIR__) . '/storage/data/gallery_submissions.json',
    dirname(__DIR__) . '/storage/uploads/gallery-submissions'
);
$donationNotifications = new DonationNotificationService(
    dirname(__DIR__) . '/storage/data/donation_notifications.json'
);
$controller = new PageController($repository, $gallerySubmissions, $donationNotifications);

$path = request_path();

$routes = [
    '/' => static fn () => $controller->home(),
    '/about' => static fn () => $controller->about(),
    '/gallery' => static fn () => $controller->gallery(),
    '/events' => static fn () => $controller->events(),
    '/donations' => static fn () => $controller->donations(),
    '/contact' => static fn () => $controller->contact(),
    '/blog' => static fn () => $controller->blog(),
    '/admin' => static fn () => $controller->placeholder('admin'),
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

http_response_code(404);
$controller->notFound();
