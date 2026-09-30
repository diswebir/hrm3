<div class="page-heading"><div class="page-heading-main"><div class="eyebrow">پیکربندی سازمان</div><h1>تنظیمات سامانه</h1><p>هویت سازمان، واحد پول و اطلاعات تماس نمایش داده‌شده در سامانه را تنظیم کنید.</p></div></div>
<div class="settings-grid">
    <section class="card settings-card">
        <div class="card-head"><div><h2 class="card-title">اطلاعات سازمان</h2><p class="card-subtitle">این مشخصات در عنوان‌ها و نمای داخلی استفاده می‌شوند.</p></div><span class="module-icon-large"><?= icon('building') ?></span></div>
        <form method="post" action="<?= h(app_url(['page'=>'settings'])) ?>">
            <input type="hidden" name="action" value="save_settings"><?= csrf_field() ?>
            <div class="form-section"><div class="form-grid">
                <label class="field full-span"><span>نام سازمان / سامانه</span><input name="org_name" maxlength="160" required value="<?= h($settings['org_name'] ?? '') ?>"></label>
                <label class="field"><span>ایمیل سازمان</span><input type="email" name="org_email" dir="ltr" value="<?= h($settings['org_email'] ?? '') ?>" placeholder="hr@company.com"></label>
                <label class="field"><span>تلفن سازمان</span><input type="tel" name="org_phone" value="<?= h($settings['org_phone'] ?? '') ?>"></label>
                <label class="field"><span>واحد پول</span><input name="currency" maxlength="40" value="<?= h($settings['currency'] ?? 'تومان') ?>" placeholder="تومان"></label>
                <label class="field"><span>الگوی هفته کاری</span><input name="work_week" maxlength="120" value="<?= h($settings['work_week'] ?? 'شنبه تا چهارشنبه') ?>"></label>
                <label class="field"><span>شروع سال مالی (ماه-روز)</span><input name="fiscal_year_start" maxlength="5" value="<?= h($settings['fiscal_year_start'] ?? '01-01') ?>" dir="ltr" placeholder="01-01"><small>این مقدار صرفاً برای راهنمای سازمان نگهداری می‌شود.</small></label>
            </div></div>
            <div class="form-footer"><span class="form-footer-note">تنظیمات در SQLite محلی ذخیره می‌شوند.</span><div class="form-footer-actions"><button class="button button-primary" type="submit"><?= icon('check') ?> ذخیره تنظیمات</button></div></div>
        </form>
    </section>
    <div>
        <section class="card backup-card"><span class="backup-card-icon"><?= icon('download') ?></span><h2>پشتیبان‌گیری از پایگاه داده</h2><p>یک نسخه از فایل SQLite فعلی دریافت کنید و در فضای امن خارج از هاست نگهداری کنید. توصیه می‌شود پیش از ارتقا یا تغییرات مهم پشتیبان بگیرید.</p><form method="post" action="<?= h(app_url(['page'=>'settings'])) ?>" data-confirm="نسخه پشتیبان پایگاه داده دانلود شود؟"><input type="hidden" name="action" value="backup"><?= csrf_field() ?><button type="submit" class="button button-secondary button-wide"><?= icon('download') ?> دریافت نسخه پشتیبان</button></form><div class="security-note"><?= icon('shield') ?><span>فایل پشتیبان شامل اطلاعات حساس کارکنان است؛ آن را عمومی یا در مسیر وب‌قابل‌دسترسی نگهداری نکنید.</span></div></section>
        <div class="card" style="padding:17px;margin-top:14px"><h3 style="font-size:11px;margin:0 0 7px">وضعیت نصب</h3><div class="report-dept-row"><span class="report-dept-name">نسخه برنامه</span><strong class="report-dept-value">v<?= h(HRM_VERSION) ?></strong></div><div class="report-dept-row"><span class="report-dept-name">پایگاه داده</span><strong class="report-dept-value">SQLite</strong></div><div class="report-dept-row"><span class="report-dept-name">منطقه زمانی</span><strong class="report-dept-value" style="width:auto"><?= h(date_default_timezone_get()) ?></strong></div><div class="report-dept-row"><span class="report-dept-name">وضعیت</span><strong class="report-dept-value" style="width:auto;color:#168b79">فعال</strong></div></div>
    </div>
</div>
