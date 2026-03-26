<?php
declare(strict_types=1);

$adminShellMode = $adminShellMode ?? 'dashboard';
$adminPage = $adminPage ?? '';
$adminUser = $adminUser ?? null;

$adminNavItems = [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'grid_view', 'href' => route_url('/admin'), 'enabled' => true],
    ['key' => 'pages', 'label' => 'Pages', 'icon' => 'description', 'href' => route_url('/admin/pages'), 'enabled' => true],
    ['key' => 'events', 'label' => 'Events', 'icon' => 'calendar_month', 'href' => route_url('/admin/events'), 'enabled' => true],
    ['key' => 'media', 'label' => 'Media', 'icon' => 'photo_library', 'href' => route_url('/admin/media'), 'enabled' => true],
    ['key' => 'blog', 'label' => 'Blog', 'icon' => 'edit_square', 'href' => route_url('/admin/blog'), 'enabled' => true],
    ['key' => 'settings', 'label' => 'Settings', 'icon' => 'settings', 'href' => null, 'enabled' => false],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($metaTitle ?? ($pageTitle ?? 'Admin')) ?></title>
    <meta name="description" content="<?= e($metaDescription ?? '') ?>">
    <meta name="theme-color" content="#fff8ef">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Noto+Serif:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('assets/css/site.css')) ?>">
</head>
<body class="admin-body">
    <?php if ($adminShellMode === 'auth'): ?>
        <main class="admin-auth-shell">
            <?php require $contentView; ?>
        </main>
        <footer class="admin-auth-footer">
            <div>
                <strong>AnkammaThalli Temple</strong>
                <span>&copy; 2026 AnkammaThalli Temple. All Rights Reserved.</span>
            </div>
            <div>
                <span>Support</span>
                <span>Privacy</span>
                <span>Security</span>
            </div>
        </footer>
    <?php else: ?>
        <div class="admin-app-shell">
            <aside class="admin-sidebar">
                <div class="admin-sidebar__brand">
                    <span class="admin-sidebar__title">AnkammaThalli</span>
                    <span class="admin-sidebar__subtitle">Admin Portal</span>
                </div>

                <nav class="admin-sidebar__nav" aria-label="Admin navigation">
                    <?php foreach ($adminNavItems as $item): ?>
                        <?php $isActive = $item['key'] === $adminPage; ?>
                        <?php if ($item['enabled'] && is_string($item['href'])): ?>
                            <a class="admin-sidebar__link<?= $isActive ? ' admin-sidebar__link--active' : '' ?>" href="<?= e($item['href']) ?>">
                                <span class="material-symbols-outlined"><?= e($item['icon']) ?></span>
                                <span><?= e($item['label']) ?></span>
                            </a>
                        <?php else: ?>
                            <span class="admin-sidebar__link admin-sidebar__link--disabled<?= $isActive ? ' admin-sidebar__link--active' : '' ?>">
                                <span class="material-symbols-outlined"><?= e($item['icon']) ?></span>
                                <span><?= e($item['label']) ?></span>
                            </span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </nav>

                <form class="admin-sidebar__logout-form" action="<?= e(route_url('/admin/logout')) ?>" method="post">
                    <?= csrf_input() ?>
                    <button class="admin-sidebar__logout" type="submit">
                        <span class="material-symbols-outlined">logout</span>
                        <span>Logout</span>
                    </button>
                </form>

                <div class="admin-sidebar__user">
                    <div class="admin-sidebar__avatar"><?= e(strtoupper(substr((string) ($adminUser['name'] ?? 'A'), 0, 1))) ?></div>
                    <div>
                        <strong><?= e($adminUser['name'] ?? 'Admin User') ?></strong>
                        <span><?= e($adminUser['email'] ?? 'admin@ankammathalli.local') ?></span>
                    </div>
                </div>
            </aside>

            <main class="admin-main">
                <?php require $contentView; ?>
                <footer class="admin-main__footer">
                    <div>
                        <span class="admin-main__status-dot"></span>
                        <span>Server Status: Optimal</span>
                        <span>Admin Foundation</span>
                    </div>
                    <div>&copy; 2026 AnkammaThalli Temple Admin Console</div>
                </footer>
            </main>
        </div>
    <?php endif; ?>
</body>
</html>
