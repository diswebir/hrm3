<?php
declare(strict_types=1);

use HRM\Core\Database;
use HRM\Core\Schema;
use HRM\Core\Security;

require __DIR__ . '/app/bootstrap.php';

if (is_file(HRM_STORAGE . '/installed.lock')) {
    header('Location: index.php', true, 302);
    exit;
}

if (!is_dir(HRM_STORAGE)) {
    @mkdir(HRM_STORAGE, 0700, true);
}
if (!is_dir(HRM_STORAGE . '/uploads')) {
    @mkdir(HRM_STORAGE . '/uploads', 0700, true);
}

$requirements = [
    ['PHP 8.1 یا جدیدتر', PHP_VERSION_ID >= 80100, 'نسخه PHP را از بخش MultiPHP Manager در cPanel تغییر دهید.'],
    ['افزونه PDO_SQLite', extension_loaded('pdo_sqlite'), 'از Select PHP Version / Extensions افزونه pdo_sqlite را فعال کنید.'],
    ['پوشه storage قابل نوشتن', is_dir(HRM_STORAGE) && is_writable(HRM_STORAGE), 'مجوز نوشتن پوشه storage را برای کاربر PHP فعال کنید (معمولاً 0750 یا 0770).'],
];
$ready = !in_array(false, array_column($requirements, 1), true);
$error = '';
$success = '';
$name = trim((string)($_POST['name'] ?? 'مدیر سامانه'));
$email = trim((string)($_POST['email'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        Security::verify($_POST['_csrf'] ?? null);
        if (!$ready) {
            throw new RuntimeException('ابتدا همه پیش‌نیازهای بالا را برطرف کنید.');
        }
        if (strlen($name) < 2 || strlen($name) > 100) {
            throw new RuntimeException('نام مدیر باید بین ۲ تا ۱۰۰ نویسه باشد.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
            throw new RuntimeException('نشانی ایمیل معتبر وارد کنید.');
        }
        $password = (string)($_POST['password'] ?? '');
        $confirm = (string)($_POST['password_confirm'] ?? '');
        if (strlen($password) < 10) {
            throw new RuntimeException('گذرواژه باید حداقل ۱۰ نویسه داشته باشد.');
        }
        if (!hash_equals($password, $confirm)) {
            throw new RuntimeException('تکرار گذرواژه با گذرواژه یکسان نیست.');
        }
        if (is_file(HRM_STORAGE . '/installed.lock')) {
            throw new RuntimeException('سامانه قبلاً نصب شده است.');
        }
        $databasePath = HRM_STORAGE . '/hrm.sqlite';
        $pdo = Database::connectPath($databasePath);
        Schema::migrate($pdo);
        $now = date('Y-m-d H:i:s');
        $pdo->beginTransaction();
        $insert = $pdo->prepare('INSERT INTO users(name,email,password_hash,role,active,created_at,updated_at) VALUES(:name,:email,:password,"admin",1,:created,:updated)');
        $insert->execute([
            ':name' => $name,
            ':email' => strtolower($email),
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':created' => $now,
            ':updated' => $now,
        ]);
        $adminId = (int)$pdo->lastInsertId();
        if (isset($_POST['demo_data']) && $_POST['demo_data'] === '1') {
            Schema::seedDemo($pdo, $adminId);
        }
        $pdo->commit();

        $config = [
            'database' => $databasePath,
            'timezone' => 'Asia/Tehran',
            'app_key' => bin2hex(random_bytes(32)),
        ];
        $configContents = "<?php\ndeclare(strict_types=1);\n\nreturn " . var_export($config, true) . ";\n";
        $tmpConfig = HRM_STORAGE . '/config.php.tmp';
        if (file_put_contents($tmpConfig, $configContents, LOCK_EX) === false || !rename($tmpConfig, HRM_STORAGE . '/config.php')) {
            throw new RuntimeException('ذخیره تنظیمات نصب ممکن نشد؛ مجوز نوشتن storage را بررسی کنید.');
        }
        @chmod(HRM_STORAGE . '/config.php', 0640);
        if (file_put_contents(HRM_STORAGE . '/installed.lock', 'installed ' . $now, LOCK_EX) === false) {
            throw new RuntimeException('ساخت قفل نصب انجام نشد؛ دسترسی پوشه storage را بررسی کنید.');
        }
        @chmod(HRM_STORAGE . '/installed.lock', 0640);
        $success = 'نصب با موفقیت انجام شد. در حال انتقال به صفحه ورود…';
        header('Refresh: 2; url=index.php');
    } catch (Throwable $exception) {
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = $exception instanceof RuntimeException ? $exception->getMessage() : 'نصب کامل نشد. مجوز پوشه storage و گزارش خطای PHP را بررسی کنید.';
        if (!($exception instanceof RuntimeException)) {
            error_log('[HRM install] ' . $exception->getMessage());
        }
    }
}
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#101a34">
    <title>نصب هم‌آوا HRM</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="install-body">
<div class="install-shell">
    <div class="install-brand">
        <div class="brand-mark">هـ</div>
        <div><strong>هم‌آوا <span>HRM</span></strong><small>سامانه مدیریت سرمایه انسانی</small></div>
    </div>
    <div class="install-card">
        <div class="eyebrow">راه‌اندازی اولیه</div>
        <h1>شروعی ساده برای مدیریت بهتر</h1>
        <p class="muted">اطلاعات مدیر را وارد کنید. پایگاه داده SQLite و ساختار سامانه به‌صورت خودکار ساخته می‌شود.</p>

        <?php if ($error !== ''): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>
        <?php if ($success !== ''): ?><div class="alert alert-success"><?= h($success) ?></div><?php endif; ?>

        <div class="requirements">
            <?php foreach ($requirements as [$label, $ok, $hint]): ?>
                <div class="requirement <?= $ok ? 'is-ok' : 'is-failed' ?>">
                    <span class="requirement-icon"><?= $ok ? '✓' : '!' ?></span>
                    <div><strong><?= h($label) ?></strong><small><?= $ok ? 'آماده' : h($hint) ?></small></div>
                </div>
            <?php endforeach; ?>
        </div>

        <form method="post" class="form-stack">
            <?= csrf_field() ?>
            <div class="form-grid">
                <label class="field"><span>نام مدیر سامانه</span><input type="text" name="name" maxlength="100" required value="<?= h($name) ?>" autocomplete="name"></label>
                <label class="field"><span>ایمیل ورود</span><input type="email" name="email" maxlength="190" required value="<?= h($email) ?>" autocomplete="email" dir="ltr"></label>
                <label class="field"><span>گذرواژه</span><input type="password" name="password" minlength="10" required autocomplete="new-password"><small>حداقل ۱۰ نویسه</small></label>
                <label class="field"><span>تکرار گذرواژه</span><input type="password" name="password_confirm" minlength="10" required autocomplete="new-password"></label>
            </div>
            <label class="check-field"><input type="checkbox" name="demo_data" value="1"><span><strong>افزودن داده نمایشی</strong><small>چند کارمند و درخواست نمونه برای آشنایی با داشبورد (قابل حذف پس از نصب)</small></span></label>
            <button class="button button-primary button-wide" type="submit" <?= $ready ? '' : 'disabled' ?>>ساخت سامانه و ایجاد مدیر</button>
        </form>
        <div class="install-footnote">پیشنهاد امنیتی: پس از نصب، دسترسی نوشتن پوشه storage را محدود کنید و حتماً HTTPS را فعال نگه دارید.</div>
    </div>
    <div class="install-footer">نسخه <?= h(HRM_VERSION) ?> · نصب آفلاین، بدون وابستگی خارجی</div>
</div>
</body>
</html>
