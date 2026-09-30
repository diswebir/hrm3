<?php
declare(strict_types=1);

if (!defined('HRM_ROOT')) {
    define('HRM_ROOT', dirname(__DIR__));
    define('HRM_APP', HRM_ROOT . '/app');
    define('HRM_STORAGE', HRM_ROOT . '/storage');
    define('HRM_VERSION', '1.0.0');
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'HRM\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $path = HRM_APP . '/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($path)) {
        require_once $path;
    }
});

$configFile = HRM_STORAGE . '/config.php';
$config = is_file($configFile) ? require $configFile : [];
if (!is_array($config)) {
    $config = [];
}
$timezone = (string)($config['timezone'] ?? 'Asia/Tehran');
if (!in_array($timezone, timezone_identifiers_list(), true)) {
    $timezone = 'Asia/Tehran';
}
date_default_timezone_set($timezone);

if (PHP_SAPI !== 'cli') {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

if (PHP_SAPI !== 'cli' && session_status() !== PHP_SESSION_ACTIVE) {
    $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_name('HRMSESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    session_start();
}

function hrm_config(): array
{
    static $config = null;
    if ($config === null) {
        $file = HRM_STORAGE . '/config.php';
        $value = is_file($file) ? require $file : [];
        $config = is_array($value) ? $value : [];
    }
    return $config;
}

function h(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function db(): PDO
{
    return \HRM\Core\Database::connection();
}

function app_url(array $params = []): string
{
    return 'index.php' . ($params ? '?' . http_build_query($params) : '');
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . h(\HRM\Core\Security::token()) . '">';
}

function flash(string $message, string $type = 'success'): void
{
    if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['_flash'][] = ['message' => $message, 'type' => $type];
    }
}

function take_flashes(): array
{
    if (PHP_SAPI === 'cli' || session_status() !== PHP_SESSION_ACTIVE) {
        return [];
    }
    $messages = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return is_array($messages) ? $messages : [];
}

function render_view(string $view, array $data = [], bool $public = false): void
{
    $viewFile = HRM_APP . '/Views/' . $view . '.php';
    if (!is_file($viewFile)) {
        throw new RuntimeException('View not found: ' . $view);
    }
    extract($data, EXTR_SKIP);
    ob_start();
    require $viewFile;
    $content = (string)ob_get_clean();
    if ($public) {
        require HRM_APP . '/Views/public-layout.php';
        return;
    }
    require HRM_APP . '/Views/layout.php';
}

function role_label(string $role): string
{
    return [
        'admin' => 'مدیر سامانه',
        'hr' => 'کارشناس منابع انسانی',
        'manager' => 'مدیر واحد',
        'employee' => 'کارمند',
    ][$role] ?? 'کاربر';
}

function utf8_substr(string $value, int $start, ?int $length = null): string
{
    if (function_exists('mb_substr')) {
        return $length === null ? mb_substr($value, $start, null, 'UTF-8') : mb_substr($value, $start, $length, 'UTF-8');
    }
    if (preg_match_all('/./us', $value, $matches)) {
        $slice = array_slice($matches[0], $start, $length);
        return implode('', $slice);
    }
    return $length === null ? substr($value, $start) : substr($value, $start, $length);
}

function normalize_digits(string $value): string
{
    return strtr($value, [
        '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
        '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
    ]);
}

function initials(string $name): string
{
    $parts = preg_split('/\s+/u', trim($name)) ?: [];
    $value = '';
    foreach (array_slice($parts, 0, 2) as $part) {
        $value .= utf8_substr($part, 0, 1);
    }
    return $value !== '' ? $value : 'HR';
}

function money_fmt(mixed $value, string $currency = 'تومان'): string
{
    return number_format((float)$value, 0, '.', ',') . ' ' . $currency;
}

function date_fmt(?string $value): string
{
    if (!$value) {
        return '—';
    }
    $timestamp = strtotime($value);
    return $timestamp ? date('Y/m/d', $timestamp) : h($value);
}

function icon(string $name, string $class = ''): string
{
    $paths = [
        'grid' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'building' => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 21V9h6v12M7 7h.01M17 7h.01M7 11h.01M17 11h.01"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/>',
        'wallet' => '<rect x="3" y="5" width="18" height="15" rx="2"/><path d="M3 9h18M16 14h2"/><path d="M3 5l3-2h12"/>',
        'briefcase' => '<rect x="3" y="7" width="18" height="14" rx="2"/><path d="M8 7V4h8v3M3 12h18M10 12v2h4v-2"/>',
        'chart' => '<path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-5 5"/><path d="M15 9h4v4"/>',
        'book' => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>',
        'file' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h8"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="m19.4 15 .1.1 1.4 1.1-1.4 2.4-1.7-.7a8 8 0 0 1-1.7 1l-.3 1.8h-2.8l-.3-1.8a8 8 0 0 1-1.7-1l-1.7.7-1.4-2.4 1.4-1.1a8 8 0 0 1 0-2l-1.4-1.1 1.4-2.4 1.7.7a8 8 0 0 1 1.7-1l.3-1.8h2.8l.3 1.8a8 8 0 0 1 1.7 1l1.7-.7 1.4 2.4-1.4 1.1a8 8 0 0 1 0 2Z" transform="translate(-2 -2)"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'arrow' => '<path d="M19 12H5M12 19l-7-7 7-7"/>',
        'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5M12 15V3"/>',
        'shield' => '<path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"/><path d="m9 12 2 2 4-4"/>',
        'spark' => '<path d="m12 3 1.9 5.8L20 11l-6.1 2.2L12 19l-1.9-5.8L4 11l6.1-2.2L12 3Z"/><path d="m19 14 1 2 2 1-2 1-1 2-1-2-2-1 2-1 1-2Z"/>',
        'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5M21 12H9"/>',
        'edit' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"/>',
        'trash' => '<path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v5M14 11v5"/>',
        'eye' => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
        'dots' => '<circle cx="5" cy="12" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/>',
        'check' => '<path d="m5 12 4 4L19 6"/>',
        'close' => '<path d="m18 6-12 12M6 6l12 12"/>',
    ];
    $path = $paths[$name] ?? $paths['file'];
    return '<svg class="icon ' . h($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $path . '</svg>';
}
