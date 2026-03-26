<?php

declare(strict_types=1);

function request_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

    if (! is_string($path) || $path === '') {
        return '/';
    }

    $normalized = '/' . trim($path, '/');

    return $normalized === '//' ? '/' : $normalized;
}

function base_url(): string
{
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $directory = str_replace('\\', '/', dirname($scriptName));
    $directory = rtrim($directory, '/.');

    return $directory === '' ? '' : $directory;
}

function asset(string $path): string
{
    return base_url() . '/' . ltrim($path, '/');
}

function route_url(string $path = '/'): string
{
    $cleanPath = '/' . ltrim($path, '/');

    return $cleanPath === '/' ? base_url() . '/' : base_url() . $cleanPath;
}

function route_url_with_query(string $path, array $query = []): string
{
    $url = route_url($path);
    $filtered = array_filter(
        $query,
        static fn ($value): bool => $value !== null && $value !== ''
    );

    if ($filtered === []) {
        return $url;
    }

    return $url . '?' . http_build_query($filtered);
}

function query_value(string $key): ?string
{
    $value = $_GET[$key] ?? null;

    if (! is_scalar($value)) {
        return null;
    }

    $trimmed = trim((string) $value);

    return $trimmed === '' ? null : $trimmed;
}

function flash_set(string $key, mixed $value): void
{
    $_SESSION['_flash'][$key] = $value;
}

function flash_pull(string $key, mixed $default = null): mixed
{
    if (! isset($_SESSION['_flash'][$key])) {
        return $default;
    }

    $value = $_SESSION['_flash'][$key];
    unset($_SESSION['_flash'][$key]);

    return $value;
}

function redirect_to(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function session_put(string $key, mixed $value): void
{
    $_SESSION[$key] = $value;
}

function session_value(string $key, mixed $default = null): mixed
{
    return $_SESSION[$key] ?? $default;
}

function session_forget(string $key): void
{
    unset($_SESSION[$key]);
}

function csrf_token(): string
{
    $token = session_value('_csrf_token');

    if (is_string($token) && $token !== '') {
        return $token;
    }

    $token = bin2hex(random_bytes(32));
    session_put('_csrf_token', $token);

    return $token;
}

function csrf_input(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_is_valid(?string $token): bool
{
    $sessionToken = session_value('_csrf_token');

    return is_string($token)
        && is_string($sessionToken)
        && $sessionToken !== ''
        && hash_equals($sessionToken, $token);
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
