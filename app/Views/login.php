<?php
$pageTitle = 'ورود به سامانه';
$flashes = take_flashes();
?>
<div class="auth-layout">
    <section class="auth-aside">
        <div class="auth-hero-orb orb-one"></div><div class="auth-hero-orb orb-two"></div>
        <div class="auth-aside-content">
            <div class="brand-mark brand-mark-large">هـ</div>
            <div class="eyebrow light-eyebrow">مدیریت هوشمند سرمایه انسانی</div>
            <h1>هم‌آوا HRM</h1>
            <p>از پرونده همکاران تا رشد و توسعه تیم؛ یک فضای یکپارچه برای کارهای مهم منابع انسانی.</p>
            <div class="auth-feature-list">
                <div><?= icon('users') ?><span>پرونده و چرخه عمر کارکنان</span></div>
                <div><?= icon('chart') ?><span>گزارش‌های شفاف و قابل پیگیری</span></div>
                <div><?= icon('shield') ?><span>دسترسی امن و ماژولار</span></div>
            </div>
        </div>
        <div class="auth-aside-footer">راهکاری ساده، منظم و قابل توسعه برای سازمان شما</div>
    </section>
    <section class="auth-main">
        <div class="auth-card">
            <div class="auth-mobile-brand"><div class="brand-mark">هـ</div><strong>هم‌آوا <span>HRM</span></strong></div>
            <div class="eyebrow">خوش آمدید</div>
            <h2>ورود به سامانه</h2>
            <p class="muted">برای ادامه، اطلاعات حساب کاربری خود را وارد کنید.</p>
            <?php if ($flashes): ?><div class="auth-flashes"><?php foreach ($flashes as $item): ?><div class="alert <?= $item['type'] === 'error' ? 'alert-error' : 'alert-success' ?>"><?= h($item['message']) ?></div><?php endforeach; ?></div><?php endif; ?>
            <form method="post" action="<?= h(app_url(['page' => 'login'])) ?>" class="form-stack auth-form">
                <input type="hidden" name="action" value="login">
                <?= csrf_field() ?>
                <label class="field"><span>ایمیل</span><input type="email" name="email" required autofocus autocomplete="username" placeholder="name@company.com" dir="ltr"></label>
                <label class="field"><span>گذرواژه</span><div class="password-wrap"><input type="password" name="password" required autocomplete="current-password" placeholder="گذرواژه خود را وارد کنید"><button type="button" class="password-toggle" aria-label="نمایش گذرواژه" data-password-toggle>نمایش</button></div></label>
                <button type="submit" class="button button-primary button-wide">ورود امن <?= icon('arrow') ?></button>
            </form>
            <div class="auth-help"><span class="help-dot"></span> برای بازیابی دسترسی با مدیر سامانه تماس بگیرید.</div>
            <div class="auth-version">نسخه <?= h(HRM_VERSION) ?> · پایگاه داده SQLite</div>
        </div>
    </section>
</div>
