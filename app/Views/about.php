<?php
$featureNumber=0;
$featureTotal=0;
foreach ($featureGroups as $group) { $featureTotal += count($group['items']); }
?>
<div class="about-hero">
    <div class="eyebrow" style="color:#6ce0c9">راهنمای سامانه · نسخه <?= h(HRM_VERSION) ?></div>
    <h1>درباره هم‌آوا HRM</h1>
    <p>هم‌آوا یک سامانه مدیریت منابع انسانی ماژولار با PHP و SQLite است؛ برای نصب روی هاست cPanel طراحی شده و از مدیریت پرونده کارکنان تا حضور، مرخصی، جذب، رشد، حقوق و عملیات داخلی را در یک پنل فارسی گرد هم می‌آورد. در این راهنما، هر قابلیت شماره‌گذاری شده و مسیر استفاده از آن توضیح داده شده است.</p>
    <div class="about-badges"><span>PHP 8.1+</span><span>SQLite · PDO</span><span>رابط فارسی و راست‌چین</span><span><?= number_format($featureTotal) ?> قابلیت مستند</span><span>۲۹ ماژول آماده</span></div>
</div>
<div class="feature-toolbar"><div><h2>فهرست قابلیت‌ها و راهنمای استفاده</h2><span>برای یافتن قابلیت، عنوان یا واژه‌ای از توضیح را جست‌وجو کنید.</span></div><label class="feature-search"><?= icon('search') ?><input type="search" data-feature-search placeholder="جست‌وجو در قابلیت‌ها…" aria-label="جست‌وجو در قابلیت‌ها"></label></div>
<div class="feature-groups" data-feature-groups>
<?php foreach ($featureGroups as $group): ?>
    <section class="card feature-group" data-feature-group>
        <header class="feature-group-head"><h3><span><?= number_format($featureNumber + 1) ?></span><?= h($group['title']) ?></h3><small><?= number_format(count($group['items'])) ?> قابلیت</small></header>
        <div class="feature-list">
            <?php foreach ($group['items'] as $feature): $featureNumber++; ?>
                <article class="feature-item" data-feature-item><span class="feature-number"><?= str_pad((string)$featureNumber,2,'0',STR_PAD_LEFT) ?></span><div><h4><?= h($feature[0]) ?></h4><p><?= h($feature[1]) ?></p></div></article>
            <?php endforeach; ?>
        </div>
    </section>
<?php endforeach; ?>
</div>
<div class="guide-card card"><div><h3>راهنمای توسعه و افزودن ماژول</h3><p>ساختار پوشه‌ها، manifest، نوع فیلدها، اعتبارسنجی، مجوزها و نکات انتشار در مستندات توسعه آمده است.</p></div><a class="button button-primary" href="docs/ADD_MODULE_FA.md" target="_blank" rel="noopener"><?= icon('book') ?> بازکردن راهنمای افزودن ماژول</a></div>
<div class="guide-card card" style="margin-top:10px"><div><h3>راهنمای نصب cPanel و نگهداری</h3><p>پیش‌نیاز PHP، فعال‌سازی PDO_SQLite، انتقال فایل‌ها، مجوز storage و تهیه پشتیبان را مرحله‌به‌مرحله ببینید.</p></div><a class="button button-secondary" href="docs/CPANEL_FA.md" target="_blank" rel="noopener"><?= icon('file') ?> راهنمای نصب و نگهداری</a></div>
