<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($metaTitle ?? $site['name']) ?></title>
    <meta name="description" content="<?= e($metaDescription ?? '') ?>">
    <meta name="theme-color" content="#fff8ef">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Noto+Serif:ital,wght@0,400;0,700;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('assets/css/site.css')) ?>">
</head>
<body>
    <a class="skip-link" href="#main-content">Skip to content</a>
    <?php require __DIR__ . '/../partials/header.php'; ?>
    <main id="main-content">
        <?php require $contentView; ?>
    </main>
    <?php require __DIR__ . '/../partials/footer.php'; ?>
    <script src="<?= e(asset('assets/js/site.js')) ?>" defer></script>
</body>
</html>
