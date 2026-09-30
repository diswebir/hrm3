<?php
$displayName = (string)($profile['name'] ?? $currentUser['name'] ?? '');
$employeeName = trim((string)($profile['first_name'] ?? '') . ' ' . (string)($profile['last_name'] ?? ''));
?>
<div class="page-heading"><div class="page-heading-main"><div class="eyebrow">حساب و امنیت</div><h1>حساب کاربری من</h1><p>اطلاعات ورود خود را مرور و گذرواژه را به‌صورت امن به‌روزرسانی کنید.</p></div></div>
<div class="settings-grid">
    <section class="card settings-card">
        <div class="card-head"><div><h2 class="card-title">اطلاعات حساب</h2><p class="card-subtitle">جزئیات دسترسی جاری شما</p></div><span class="avatar" style="width:42px;height:42px"><?= h(initials($displayName)) ?></span></div>
        <div class="form-section"><div class="report-dept-row"><span class="report-dept-name">نام نمایشی</span><strong><?= h($displayName) ?></strong></div><div class="report-dept-row"><span class="report-dept-name">ایمیل ورود</span><strong dir="ltr"><?= h($profile['email'] ?? '') ?></strong></div><div class="report-dept-row"><span class="report-dept-name">نقش</span><strong><span class="role-chip role-<?= h($profile['role'] ?? '') ?>"><?= h(role_label((string)($profile['role'] ?? ''))) ?></span></strong></div><div class="report-dept-row"><span class="report-dept-name">پرونده کارمند</span><strong><?= $employeeName !== '' ? h($employeeName . ' · ' . ($profile['employee_code'] ?? '')) : 'متصل نیست' ?></strong></div><div class="report-dept-row"><span class="report-dept-name">عضویت از</span><strong><?= h(date_fmt($profile['created_at'] ?? '')) ?></strong></div><div class="report-dept-row"><span class="report-dept-name">آخرین ورود</span><strong><?= h($profile['last_login_at'] ? date_fmt($profile['last_login_at']) . ' · ' . date('H:i', strtotime($profile['last_login_at'])) : '—') ?></strong></div></div>
    </section>
    <section class="card settings-card">
        <div class="card-head"><div><h2 class="card-title">تغییر گذرواژه</h2><p class="card-subtitle">برای امنیت بیشتر از گذرواژه یکتا استفاده کنید.</p></div><span class="backup-card-icon" style="margin:0"><?= icon('shield') ?></span></div>
        <form method="post" action="<?= h(app_url(['page'=>'profile'])) ?>">
            <input type="hidden" name="action" value="change_password"><?= csrf_field() ?>
            <div class="form-section"><div class="form-stack">
                <label class="field"><span>گذرواژه فعلی</span><input type="password" name="current_password" required autocomplete="current-password"></label>
                <label class="field"><span>گذرواژه جدید</span><input type="password" name="new_password" required minlength="10" autocomplete="new-password"><small>حداقل ۱۰ نویسه؛ از گذرواژه‌ای که جای دیگری استفاده می‌کنید، استفاده نکنید.</small></label>
                <label class="field"><span>تکرار گذرواژه جدید</span><input type="password" name="password_confirm" required minlength="10" autocomplete="new-password"></label>
            </div></div>
            <div class="form-footer"><span class="form-footer-note">گذرواژه با `password_hash()` ذخیره می‌شود.</span><div class="form-footer-actions"><button class="button button-primary" type="submit"><?= icon('check') ?> تغییر گذرواژه</button></div></div>
        </form>
    </section>
</div>
