<?php

declare(strict_types=1);

require __DIR__ . '/../app/Support/helpers.php';
require __DIR__ . '/../app/Support/View.php';
require __DIR__ . '/../app/Repositories/ContentRepository.php';
require __DIR__ . '/../app/Controllers/PageController.php';

$content = require __DIR__ . '/../data/site.php';
$repository = new ContentRepository($content);
$controller = new PageController($repository);

$path = request_path();

$routes = [
    '/' => static fn () => $controller->home(),
    '/about' => static fn () => $controller->about(),
    '/gallery' => static fn () => $controller->gallery(),
    '/events' => static fn () => $controller->events(),
    '/donations' => static fn () => $controller->donations(),
    '/contact' => static fn () => $controller->contact(),
    '/blog' => static fn () => $controller->placeholder('blog'),
    '/admin' => static fn () => $controller->placeholder('admin'),
];

if (isset($routes[$path])) {
    $routes[$path]();
    exit;
}

http_response_code(404);
$controller->notFound();
