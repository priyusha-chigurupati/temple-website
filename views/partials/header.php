<?php
declare(strict_types=1);
?>
<header class="site-header">
    <div class="container site-header__inner">
        <a class="site-brand" href="<?= e(route_url('/')) ?>"><?= e($site['name']) ?></a>
        <button
            class="site-nav-toggle"
            type="button"
            aria-expanded="false"
            aria-controls="primary-navigation"
            data-nav-toggle
        >
            <span class="sr-only">Toggle navigation</span>
            <span class="site-nav-toggle__bar" aria-hidden="true"></span>
            <span class="site-nav-toggle__bar" aria-hidden="true"></span>
            <span class="site-nav-toggle__bar" aria-hidden="true"></span>
        </button>
        <nav id="primary-navigation" class="site-nav" aria-label="Primary navigation">
            <ul class="site-nav__list">
                <?php foreach ($navigation as $item): ?>
                    <?php $slug = trim($item['href'], '/'); ?>
                    <?php $isHome = $item['href'] === '/'; ?>
                    <?php $isActive = $isHome ? $activePage === 'home' : $activePage === $slug; ?>
                    <li>
                        <a class="site-nav__link<?= $isActive ? ' is-active' : '' ?>" href="<?= e(route_url($item['href'])) ?>">
                            <?= e($item['label']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
        <div class="site-header__actions">
            <a class="button button--primary button--small" href="<?= e(route_url('/donations')) ?>">Donate Now</a>
        </div>
    </div>
</header>
