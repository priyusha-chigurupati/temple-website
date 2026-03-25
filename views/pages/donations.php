<?php
declare(strict_types=1);
?>
<?php $impact = $page['impact']; ?>
<?php $transparency = $page['transparency']; ?>
<?php $form = $page['form']; ?>
<?php $prefill = $page['prefill'] ?? []; ?>
<?php $notificationState = $page['notification_state'] ?? []; ?>
<?php $notificationForm = $page['notification_form'] ?? []; ?>

<section class="donations-page">
    <div class="container">
        <section class="donations-hero">
            <div class="donations-hero__copy">
                <p class="eyebrow"><?= e($page['eyebrow']) ?></p>
                <h1 class="donations-hero__title">
                    <span class="donations-hero__title-line"><?= e($page['title_prefix']) ?></span>
                    <span class="donations-hero__title-highlight"><?= e($page['title_highlight']) ?></span>
                </h1>
                <p class="donations-hero__description"><?= e($page['description']) ?></p>

                <article class="donations-impact">
                    <div class="donations-impact__icon" aria-hidden="true">+</div>
                    <div>
                        <h2><?= e($impact['title']) ?></h2>
                        <p><?= e($impact['description']) ?></p>
                    </div>
                </article>
            </div>

            <div class="donations-hero__visual">
                <div class="donations-hero__image">
                    <img src="<?= e(asset($page['hero_image'])) ?>" alt="Temple-themed placeholder artwork for the donations page hero panel">
                </div>
                <div class="donations-transparency">
                    <strong><?= e($transparency['stat']) ?></strong>
                    <span><?= e($transparency['label']) ?></span>
                </div>
            </div>
        </section>

        <div class="spiritual-divider donations-divider"></div>

        <section class="donations-methods">
            <h2 class="donations-section-title"><?= e($page['methods_title']) ?></h2>
            <div class="donations-methods__grid">
                <?php foreach ($page['methods'] as $method): ?>
                    <article class="donation-method-card">
                        <div>
                            <div class="donation-method-card__icon donation-method-card__icon--<?= e($method['tone']) ?>">
                                <span><?= e($method['icon']) ?></span>
                            </div>
                            <h3><?= e($method['title']) ?></h3>
                            <p><?= e($method['description']) ?></p>
                        </div>

                        <?php if ($method['type'] === 'accepted_forms'): ?>
                            <div class="donation-method-card__footer">
                                <ul class="donation-method-card__accepted">
                                    <?php foreach ($method['accepted_forms'] as $accepted): ?>
                                        <li><?= e($accepted) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php elseif ($method['type'] === 'code'): ?>
                            <div class="donation-method-card__footer">
                                <div class="donation-method-card__code"><?= e($method['code']) ?></div>
                            </div>
                        <?php elseif ($method['type'] === 'details'): ?>
                            <dl class="donation-method-card__details">
                                <?php foreach ($method['details'] as $detail): ?>
                                    <div>
                                        <dt><?= e($detail['label']) ?></dt>
                                        <dd><?= e($detail['value']) ?></dd>
                                    </div>
                                <?php endforeach; ?>
                            </dl>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="donations-info">
            <div class="donations-info__copy">
                <h2 class="donations-section-title donations-section-title--left"><?= e($page['instructions_title']) ?></h2>
                <div class="donations-steps">
                    <?php foreach ($page['instructions'] as $item): ?>
                        <article class="donations-step">
                            <span class="donations-step__number"><?= e($item['number']) ?></span>
                            <div>
                                <h3><?= e($item['title']) ?></h3>
                                <p><?= e($item['description']) ?></p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <aside class="donations-benefit">
                    <h3><?= e($page['benefit']['title']) ?></h3>
                    <p><?= e($page['benefit']['description']) ?></p>
                </aside>
            </div>

            <div class="donations-form-card">
                <h2><?= e($form['title']) ?></h2>
                <p><?= e($form['description']) ?></p>
                <?php if (! empty($notificationState['message'])): ?>
                    <p class="donations-form__status donations-form__status--<?= e($notificationState['type'] ?? 'info') ?>">
                        <?= e($notificationState['message']) ?>
                    </p>
                <?php endif; ?>
                <form class="donations-form" action="<?= e(route_url('/donations')) ?>" method="post">
                    <?php if (! empty($prefill['purpose'])): ?>
                        <input type="hidden" name="purpose" value="<?= e($prefill['purpose']) ?>">
                    <?php endif; ?>
                    <div class="donations-form__grid">
                        <?php foreach ($form['fields'] as $field): ?>
                            <?php $wide = ($field['width'] ?? 'half') === 'full' ? ' donations-form__field--wide' : ''; ?>
                            <?php
                            $value = $notificationForm[$field['name']] ?? '';
                            if ($value === '' && $field['name'] === 'amount') {
                                $value = $prefill['amount'] ?? '';
                            }
                            if ($value === '' && $field['name'] === 'message') {
                                $value = $prefill['message'] ?? '';
                            }
                            ?>
                            <label class="donations-form__field<?= $wide ?>">
                                <span><?= e($field['label']) ?></span>
                                <?php if ($field['type'] === 'textarea'): ?>
                                    <textarea name="<?= e($field['name']) ?>" rows="5" placeholder="<?= e($field['placeholder']) ?>"><?= e($value) ?></textarea>
                                <?php elseif ($field['type'] === 'select'): ?>
                                    <select name="<?= e($field['name']) ?>">
                                        <option value="">Select a method</option>
                                        <?php foreach ($field['options'] as $option): ?>
                                            <option value="<?= e($option['value']) ?>"<?= $value === $option['value'] ? ' selected' : '' ?>>
                                                <?= e($option['label']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else: ?>
                                    <input type="<?= e($field['type']) ?>" name="<?= e($field['name']) ?>" placeholder="<?= e($field['placeholder']) ?>" value="<?= e($value) ?>">
                                <?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <p class="donations-form__note"><?= e($form['reference_note']) ?></p>
                    <button class="button button--gradient button--full" type="submit"><?= e($form['button']) ?></button>
                </form>
            </div>
        </section>
    </div>
</section>
