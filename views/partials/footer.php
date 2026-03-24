<?php
declare(strict_types=1);
?>
<footer class="site-footer">
    <div class="container footer-grid">
        <section>
            <h2 class="footer-title"><?= e($site['name']) ?></h2>
            <p class="footer-copy"><?= e($footer['description']) ?></p>
            <div class="footer-social" aria-label="Temple social links">
                <?php foreach ($footer['social_links'] as $link): ?>
                    <a class="footer-social__link" href="<?= e($link['href']) ?>" aria-label="<?= e($link['label']) ?>">
                        <span><?= e($link['short']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
        <section>
            <h2 class="footer-heading">Quick Links</h2>
            <ul class="footer-links">
                <?php foreach ($footer['quick_links'] as $link): ?>
                    <li><a href="<?= e(route_url($link['href'])) ?>"><?= e($link['label']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </section>
        <section>
            <h2 class="footer-heading">Address & Timings</h2>
            <p class="footer-copy">
                <?= e(implode(', ', $footer['address']['lines'])) ?><br>
                <strong class="footer-time-label">Morning:</strong> <?= e($footer['address']['morning']) ?><br>
                <strong class="footer-time-label">Evening:</strong> <?= e($footer['address']['evening']) ?>
            </p>
        </section>
        <section>
            <h2 class="footer-heading"><?= e($footer['newsletter']['title']) ?></h2>
            <p class="footer-copy"><?= e($footer['newsletter']['description']) ?></p>
            <form class="newsletter-form" action="#" method="post">
                <label class="sr-only" for="newsletter-email">Email address</label>
                <input id="newsletter-email" name="email" type="email" placeholder="Your email">
                <button type="submit" aria-label="Subscribe">Send</button>
            </form>
        </section>
    </div>
    <div class="container footer-bottom">
        <p>&copy; <?= e((string) date('Y')) ?> <?= e($site['name']) ?>. All rights reserved.</p>
        <div class="footer-legal">
            <?php foreach ($footer['legal'] as $link): ?>
                <a href="<?= e($link['href']) ?>"><?= e($link['label']) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</footer>
