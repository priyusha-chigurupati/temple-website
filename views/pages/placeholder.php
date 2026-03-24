<?php
declare(strict_types=1);
?>
<section class="placeholder-page">
    <div class="container placeholder-page__inner">
        <p class="eyebrow"><?= e($page['eyebrow'] ?? 'Planned Page') ?></p>
        <h1 class="placeholder-page__title"><?= e($page['title'] ?? 'This page is coming soon.') ?></h1>
        <p class="placeholder-page__description"><?= e($page['description'] ?? 'This route is reserved and will be implemented in a later milestone.') ?></p>
        <?php if (! empty($page['cta'])): ?>
            <a class="button button--gradient" href="<?= e(route_url($page['cta']['href'])) ?>"><?= e($page['cta']['label']) ?></a>
        <?php endif; ?>
    </div>
</section>
