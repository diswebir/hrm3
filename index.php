<?php
declare(strict_types=1);

use HRM\Core\Access;
use HRM\Core\Audit;
use HRM\Core\Auth;
use HRM\Core\Database;
use HRM\Core\FeatureCatalog;
use HRM\Core\ModuleRegistry;
use HRM\Core\RecordService;
use HRM\Core\Schema;
use HRM\Core\Security;
use HRM\Core\Settings;
use HRM\Core\UploadService;

require __DIR__ . '/app/bootstrap.php';

if (!is_file(HRM_STORAGE . '/installed.lock') || !is_file(HRM_STORAGE . '/config.php')) {
    header('Location: install.php', true, 302);
    exit;
}

try {
    $pdo = Database::connection();
    Schema::migrate($pdo);
} catch (Throwable $exception) {
    http_response_code(500);
    error_log('[HRM database] ' . $exception->getMessage());
    echo '<!doctype html><html lang="fa" dir="rtl"><meta charset="utf-8"><title>خطای پایگاه داده</title><body style="font-family:Tahoma,sans-serif;padding:3rem;direction:rtl"><h1>اتصال به پایگاه داده برقرار نشد</h1><p>افزونه PDO_SQLite و مجوز نوشتن پوشه storage را در cPanel بررسی کنید.</p></body></html>';
    exit;
}

function hrm_safe_page(string $page): string
{
    return preg_match('/^[a-z][a-z0-9_-]{0,50}$/', $page) ? $page : 'dashboard';
}

function hrm_status_field(array $module): ?array
{
    foreach ($module['fields'] as $field) {
        if (($field['name'] ?? '') === 'status') {
            return $field;
        }
    }
    return null;
}

function hrm_employee_name(PDO $pdo, int $id): string
{
    static $cache = [];
    if (array_key_exists($id, $cache)) {
        return $cache[$id];
    }
    $stmt = $pdo->prepare('SELECT first_name,last_name,employee_code FROM employees WHERE id=:id');
    $stmt->execute([':id' => $id]);
    $employee = $stmt->fetch();
    if (!$employee) {
        $cache[$id] = 'کارمند حذف‌شده';
        return $cache[$id];
    }
    $cache[$id] = trim($employee['first_name'] . ' ' . $employee['last_name']) . ' · ' . $employee['employee_code'];
    return $cache[$id];
}

function hrm_module_value(PDO $pdo, array $module, array $row, array $field): string
{
    $key = (string)$field['name'];
    if ($module['entity'] === 'employees') {
        $value = (string)($row[$key] ?? '');
    } else {
        $value = (string)(($row['data_array'] ?? [])[$key] ?? '');
    }
    if ($value === '') {
        return '—';
    }
    if ($field['type'] === 'employee') {
        return hrm_employee_name($pdo, (int)$value);
    }
    if ($field['type'] === 'select') {
        return (string)($field['options'][$value] ?? $value);
    }
    if ($field['type'] === 'file') {
        return 'پیوست موجود';
    }
    return $value;
}

function hrm_redirect_module(string $slug, array $params = []): never
{
    Security::redirect(app_url(['page' => $slug] + $params));
}

function hrm_current_attendance(PDO $pdo, array $user): ?array
{
    $employeeId = (int)($user['employee_id'] ?? 0);
    if ($employeeId < 1) {
        return null;
    }
    $stmt = $pdo->prepare('SELECT * FROM records WHERE module=:module AND created_by=:user ORDER BY id DESC LIMIT 30');
    $stmt->execute([':module' => 'attendance', ':user' => (int)$user['id']]);
    foreach ($stmt->fetchAll() as $record) {
        $data = json_decode((string)$record['data'], true);
        if (is_array($data) && (string)($data['employee'] ?? '') === (string)$employeeId && (string)($data['work_date'] ?? '') === date('Y-m-d')) {
            $record['data_array'] = $data;
            return $record;
        }
    }
    return null;
}

function hrm_check_attendance(PDO $pdo, array $user, string $action): void
{
    if (empty($user['employee_id'])) {
        throw new RuntimeException('حساب شما به پرونده کارمندی متصل نیست. از مدیر سامانه بخواهید اتصال را انجام دهد.');
    }
    $record = hrm_current_attendance($pdo, $user);
    $now = date('Y-m-d H:i:s');
    if ($action === 'checkin') {
        if ($record) {
            throw new RuntimeException('ورود امروز قبلاً ثبت شده است.');
        }
        $data = [
            'title' => 'حضور ' . date('Y-m-d'),
            'employee' => (string)(int)$user['employee_id'],
            'work_date' => date('Y-m-d'),
            'check_in' => date('H:i'),
            'check_out' => '',
            'status' => 'present',
            'note' => '',
        ];
        $stmt = $pdo->prepare('INSERT INTO records(module,title,status,data,created_by,updated_by,created_at,updated_at) VALUES("attendance",:title,"present",:data,:user,:user,:now,:now)');
        $stmt->execute([':title' => $data['title'], ':data' => json_encode($data, JSON_UNESCAPED_UNICODE), ':user' => (int)$user['id'], ':now' => $now]);
        Audit::log($pdo, (int)$user['id'], 'ثبت ورود', 'attendance', (int)$pdo->lastInsertId(), date('Y-m-d H:i'));
        flash('ساعت ورود امروز ثبت شد.');
        return;
    }
    if (!$record) {
        throw new RuntimeException('ابتدا ورود امروز خود را ثبت کنید.');
    }
    $data = $record['data_array'];
    if (!empty($data['check_out'])) {
        throw new RuntimeException('ساعت خروج امروز قبلاً ثبت شده است.');
    }
    $data['check_out'] = date('H:i');
    $pdo->prepare('UPDATE records SET data=:data,updated_by=:user,updated_at=:now WHERE id=:id AND module="attendance"')->execute([
        ':data' => json_encode($data, JSON_UNESCAPED_UNICODE), ':user' => (int)$user['id'], ':now' => $now, ':id' => (int)$record['id'],
    ]);
    Audit::log($pdo, (int)$user['id'], 'ثبت خروج', 'attendance', (int)$record['id'], date('Y-m-d H:i'));
    flash('ساعت خروج امروز ثبت شد.');
}

function hrm_make_export(PDO $pdo, array $module, array $user): never
{
    if (!in_array($user['role'] ?? '', ['admin', 'hr', 'manager'], true)) {
        http_response_code(403);
        exit('اجازه دریافت خروجی ندارید.');
    }
    $headers = [];
    foreach ($module['fields'] as $field) {
        $headers[] = (string)$field['label'];
    }
    $filename = 'hrm-' . $module['slug'] . '-' . date('Ymd-His') . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('X-Content-Type-Options: nosniff');
    echo "\xEF\xBB\xBF";
    $out = fopen('php://output', 'wb');
    fputcsv($out, $headers);
    if ($module['entity'] === 'employees') {
        $sql = 'SELECT * FROM employees ORDER BY id DESC';
        $params = [];
        if (($user['role'] ?? '') === 'employee') {
            $sql = 'SELECT * FROM employees WHERE id=:employee_id ORDER BY id DESC';
            $params[':employee_id'] = (int)($user['employee_id'] ?? 0);
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $line = [];
            foreach ($module['fields'] as $field) {
                $line[] = hrm_csv_safe(hrm_module_value($pdo, $module, $row, $field));
            }
            fputcsv($out, $line);
        }
    } else {
        $sql = 'SELECT * FROM records WHERE module=:module';
        $params = [':module' => $module['slug']];
        if (($user['role'] ?? '') === 'employee' && empty($module['employee_read_all'])) {
            $sql .= ' AND created_by=:owner';
            $params[':owner'] = (int)$user['id'];
        }
        $sql .= ' ORDER BY id DESC LIMIT 5000';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        foreach ($stmt->fetchAll() as $row) {
            $data = json_decode((string)$row['data'], true);
            $row['data_array'] = is_array($data) ? $data : [];
            $line = [];
            foreach ($module['fields'] as $field) {
                $line[] = hrm_csv_safe(hrm_module_value($pdo, $module, $row, $field));
            }
            fputcsv($out, $line);
        }
    }
    fclose($out);
    Audit::log($pdo, (int)$user['id'], 'خروجی CSV', $module['slug']);
    exit;
}

function hrm_csv_safe(string $value): string
{
    if (preg_match('/^[\s\x00-\x1F]*[=+\-@]/u', $value)) {
        return "'" . $value;
    }
    return $value;
}

$page = hrm_safe_page((string)($_GET['page'] ?? 'dashboard'));
$user = Auth::current($pdo);
$postError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');
    try {
        Security::verify($_POST['_csrf'] ?? null);
        if ($action === 'login' && !$user) {
            $ok = Auth::attempt($pdo, (string)($_POST['email'] ?? ''), (string)($_POST['password'] ?? ''));
            if (!$ok) {
                flash('ایمیل یا گذرواژه نادرست است، یا موقتاً محدود شده‌اید.', 'error');
                Security::redirect(app_url(['page' => 'login']));
            }
            $user = Auth::current($pdo);
            if ($user) {
                Audit::log($pdo, (int)$user['id'], 'ورود به سامانه');
            }
            flash('خوش آمدید، ' . (string)($user['name'] ?? ''));
            Security::redirect(app_url(['page' => 'dashboard']));
        }
        if (!$user) {
            Security::redirect(app_url(['page' => 'login']));
        }
        if ($action === 'logout') {
            Audit::log($pdo, (int)$user['id'], 'خروج از سامانه');
            Auth::logout();
            Security::redirect(app_url(['page' => 'login']));
        }
        if ($action === 'save_record') {
            $slug = hrm_safe_page((string)($_POST['module'] ?? ''));
            $module = ModuleRegistry::get($slug);
            if (!$module || !Access::canManageModule($module, $user)) {
                throw new RuntimeException('اجازه ثبت یا ویرایش در این ماژول را ندارید.');
            }
            $id = (int)($_POST['id'] ?? 0);
            $id = $id > 0 ? $id : null;
            if ($id && ($user['role'] ?? '') === 'employee') {
                $existing = RecordService::find($pdo, $module, $id);
                if (!$existing || (int)($existing['created_by'] ?? 0) !== (int)$user['id']) {
                    throw new RuntimeException('اجازه ویرایش این رکورد را ندارید.');
                }
                $status = (string)($existing['status'] ?? '');
                if (!in_array($status, ['pending', 'requested', 'draft'], true)) {
                    throw new RuntimeException('پس از بررسی درخواست، امکان ویرایش آن وجود ندارد.');
                }
            }
            $savedId = RecordService::save($pdo, $module, $_POST, $user, $id);
            flash('اطلاعات با موفقیت ذخیره شد.');
            hrm_redirect_module($slug, ['saved' => $savedId]);
        }
        if ($action === 'delete_record') {
            $slug = hrm_safe_page((string)($_POST['module'] ?? ''));
            $module = ModuleRegistry::get($slug);
            if (!$module || !Access::canManageModule($module, $user)) {
                throw new RuntimeException('اجازه حذف در این ماژول را ندارید.');
            }
            $deleteId = (int)($_POST['id'] ?? 0);
            if (($user['role'] ?? '') === 'employee') {
                $ownRecord = RecordService::find($pdo, $module, $deleteId);
                if (!$ownRecord || (int)($ownRecord['created_by'] ?? 0) !== (int)$user['id'] || !in_array((string)($ownRecord['status'] ?? ''), ['pending','requested','draft'], true)) {
                    throw new RuntimeException('پس از بررسی درخواست، امکان حذف آن وجود ندارد.');
                }
            }
            RecordService::delete($pdo, $module, $deleteId, $user);
            flash('رکورد حذف شد.');
            hrm_redirect_module($slug);
        }
        if ($action === 'update_status') {
            $slug = hrm_safe_page((string)($_POST['module'] ?? ''));
            $module = ModuleRegistry::get($slug);
            if (!$module || !Access::canManageModule($module, $user) || ($user['role'] ?? '') === 'employee') {
                throw new RuntimeException('اجازه تغییر وضعیت این درخواست را ندارید.');
            }
            RecordService::updateStatus($pdo, $module, (int)($_POST['id'] ?? 0), (string)($_POST['status'] ?? ''), $user);
            flash('وضعیت رکورد به‌روزرسانی شد.');
            hrm_redirect_module($slug);
        }
        if ($action === 'attendance') {
            if (!in_array($user['role'] ?? '', ['employee', 'manager', 'hr', 'admin'], true)) {
                throw new RuntimeException('اجازه ثبت تردد ندارید.');
            }
            hrm_check_attendance($pdo, $user, (string)($_POST['kind'] ?? 'checkin'));
            Security::redirect(app_url(['page' => 'dashboard']));
        }
        if ($action === 'create_user') {
            if (!Access::mayManageAdminArea($user)) {
                throw new RuntimeException('فقط مدیر سامانه می‌تواند حساب کاربری بسازد.');
            }
            $name = trim((string)($_POST['name'] ?? ''));
            $email = strtolower(trim((string)($_POST['email'] ?? '')));
            $password = (string)($_POST['password'] ?? '');
            $role = (string)($_POST['role'] ?? 'employee');
            $employeeId = (int)($_POST['employee_id'] ?? 0);
            if (strlen($name) < 2 || strlen($name) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('نام و ایمیل معتبر وارد کنید.');
            }
            if (strlen($password) < 10) {
                throw new RuntimeException('گذرواژه باید حداقل ۱۰ نویسه داشته باشد.');
            }
            if (!in_array($role, ['admin','hr','manager','employee'], true)) {
                throw new RuntimeException('نقش انتخاب‌شده معتبر نیست.');
            }
            if ($employeeId > 0) {
                $check = $pdo->prepare('SELECT id FROM employees WHERE id=:id');
                $check->execute([':id' => $employeeId]);
                if (!$check->fetchColumn()) {
                    throw new RuntimeException('پرونده کارمند انتخاب‌شده وجود ندارد.');
                }
                $linked = $pdo->prepare('SELECT id FROM users WHERE employee_id=:employee LIMIT 1');
                $linked->execute([':employee' => $employeeId]);
                if ($linked->fetchColumn()) {
                    throw new RuntimeException('این پرونده از قبل به یک حساب کاربری متصل است.');
                }
            }
            $now = date('Y-m-d H:i:s');
            $stmt = $pdo->prepare('INSERT INTO users(name,email,password_hash,role,active,employee_id,created_at,updated_at) VALUES(:name,:email,:hash,:role,1,:employee,:now,:now)');
            $stmt->execute([':name' => $name, ':email' => $email, ':hash' => password_hash($password, PASSWORD_DEFAULT), ':role' => $role, ':employee' => $employeeId ?: null, ':now' => $now]);
            $newId = (int)$pdo->lastInsertId();
            Audit::log($pdo, (int)$user['id'], 'ایجاد حساب کاربری', 'users', $newId, $email);
            flash('حساب کاربری ایجاد شد.');
            Security::redirect(app_url(['page' => 'users']));
        }
        if ($action === 'change_password') {
            $currentPassword = (string)($_POST['current_password'] ?? '');
            $newPassword = (string)($_POST['new_password'] ?? '');
            $confirmation = (string)($_POST['password_confirm'] ?? '');
            $passwordStmt = $pdo->prepare('SELECT password_hash FROM users WHERE id=:id AND active=1');
            $passwordStmt->execute([':id' => (int)$user['id']]);
            $storedHash = (string)$passwordStmt->fetchColumn();
            if ($storedHash === '' || !password_verify($currentPassword, $storedHash)) {
                throw new RuntimeException('گذرواژه فعلی صحیح نیست.');
            }
            if (strlen($newPassword) < 10) {
                throw new RuntimeException('گذرواژه جدید باید حداقل ۱۰ نویسه داشته باشد.');
            }
            if (!hash_equals($newPassword, $confirmation)) {
                throw new RuntimeException('تکرار گذرواژه جدید یکسان نیست.');
            }
            $pdo->prepare('UPDATE users SET password_hash=:hash,updated_at=:now WHERE id=:id')->execute([
                ':hash' => password_hash($newPassword, PASSWORD_DEFAULT), ':now' => date('Y-m-d H:i:s'), ':id' => (int)$user['id'],
            ]);
            session_regenerate_id(true);
            Audit::log($pdo, (int)$user['id'], 'تغییر گذرواژه شخصی', 'users');
            flash('گذرواژه شما با موفقیت تغییر کرد.');
            Security::redirect(app_url(['page' => 'profile']));
        }
        if ($action === 'toggle_user') {
            if (!Access::mayManageAdminArea($user)) {
                throw new RuntimeException('فقط مدیر سامانه می‌تواند حساب‌ها را مدیریت کند.');
            }
            $targetId = (int)($_POST['id'] ?? 0);
            if ($targetId === (int)$user['id']) {
                throw new RuntimeException('نمی‌توانید حساب فعال خودتان را غیرفعال کنید.');
            }
            $find = $pdo->prepare('SELECT active,email FROM users WHERE id=:id');
            $find->execute([':id' => $targetId]);
            $target = $find->fetch();
            if (!$target) {
                throw new RuntimeException('حساب کاربری پیدا نشد.');
            }
            $newActive = (int)$target['active'] === 1 ? 0 : 1;
            $pdo->prepare('UPDATE users SET active=:active,updated_at=:now WHERE id=:id')->execute([':active' => $newActive, ':now' => date('Y-m-d H:i:s'), ':id' => $targetId]);
            Audit::log($pdo, (int)$user['id'], $newActive ? 'فعال‌سازی حساب' : 'غیرفعال‌سازی حساب', 'users', $targetId, (string)$target['email']);
            flash($newActive ? 'حساب فعال شد.' : 'حساب غیرفعال شد.');
            Security::redirect(app_url(['page' => 'users']));
        }
        if ($action === 'save_settings') {
            if (!Access::mayManageAdminArea($user)) {
                throw new RuntimeException('فقط مدیر سامانه می‌تواند تنظیمات را تغییر دهد.');
            }
            $orgEmail = trim((string)($_POST['org_email'] ?? ''));
            if ($orgEmail !== '' && !filter_var($orgEmail, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('ایمیل سازمان معتبر نیست.');
            }
            Settings::save($pdo, $_POST);
            Audit::log($pdo, (int)$user['id'], 'به‌روزرسانی تنظیمات سامانه', 'settings');
            flash('تنظیمات سازمان ذخیره شد.');
            Security::redirect(app_url(['page' => 'settings']));
        }
        if ($action === 'backup') {
            if (!Access::mayManageAdminArea($user)) {
                throw new RuntimeException('فقط مدیر سامانه می‌تواند نسخه پشتیبان دریافت کند.');
            }
            $pdo->query('PRAGMA optimize');
            Audit::log($pdo, (int)$user['id'], 'دریافت نسخه پشتیبان', 'system');
            $path = (string)(hrm_config()['database'] ?? (HRM_STORAGE . '/hrm.sqlite'));
            if (!is_file($path)) {
                throw new RuntimeException('فایل پایگاه داده پیدا نشد.');
            }
            header('Content-Type: application/vnd.sqlite3');
            header('Content-Disposition: attachment; filename="hamava-hrm-backup-' . date('Ymd-His') . '.sqlite"');
            header('Content-Length: ' . filesize($path));
            header('X-Content-Type-Options: nosniff');
            readfile($path);
            exit;
        }
        flash('عملیات شناخته‌شده نیست.', 'error');
    } catch (Throwable $exception) {
        if ($exception instanceof RuntimeException) {
            $postError = $exception->getMessage();
        } else {
            error_log('[HRM action] ' . $exception->getMessage());
            $postError = 'انجام عملیات ممکن نشد. اطلاعات ورودی و مجوزهای حساب را بررسی کنید.';
        }
        flash($postError, 'error');
        $slug = hrm_safe_page((string)($_POST['module'] ?? $page));
        $redirectPage = ModuleRegistry::get($slug) ? $slug : $page;
        Security::redirect(app_url(['page' => $redirectPage]));
    }
}

$user = Auth::current($pdo);
if (!$user) {
    if ($page !== 'login') {
        Security::redirect(app_url(['page' => 'login']));
    }
    render_view('login', ['currentUser' => null], true);
    exit;
}
if ($page === 'login') {
    Security::redirect(app_url(['page' => 'dashboard']));
}

if ($page === 'export') {
    $slug = hrm_safe_page((string)($_GET['module'] ?? ''));
    $module = ModuleRegistry::get($slug);
    if (!$module || !Access::canViewModule($module, $user)) {
        http_response_code(403);
        exit('ماژول موردنظر در دسترس نیست.');
    }
    hrm_make_export($pdo, $module, $user);
}

if ($page === 'download') {
    $slug = hrm_safe_page((string)($_GET['module'] ?? ''));
    $fieldName = hrm_safe_page((string)($_GET['field'] ?? ''));
    $module = ModuleRegistry::get($slug);
    $id = (int)($_GET['id'] ?? 0);
    if (!$module || !Access::canViewModule($module, $user)) {
        http_response_code(403);
        exit('دسترسی مجاز نیست.');
    }
    $field = null;
    foreach ($module['fields'] as $candidate) {
        if (($candidate['name'] ?? '') === $fieldName && ($candidate['type'] ?? '') === 'file') {
            $field = $candidate;
            break;
        }
    }
    $record = $field ? RecordService::find($pdo, $module, $id) : null;
    if (!$record) {
        http_response_code(404);
        exit('فایل پیدا نشد.');
    }
    if (($user['role'] ?? '') === 'employee' && $module['entity'] === 'records' && (int)($record['created_by'] ?? 0) !== (int)$user['id']) {
        http_response_code(403);
        exit('دسترسی مجاز نیست.');
    }
    $storedName = (string)(($module['entity'] === 'employees' ? $record : $record['data_array'])[$fieldName] ?? '');
    $filePath = UploadService::path($storedName);
    if (!$filePath) {
        http_response_code(404);
        exit('فایل پیدا نشد.');
    }
    Audit::log($pdo, (int)$user['id'], 'دریافت فایل پیوست', $slug, $id);
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="document.' . h(pathinfo($storedName, PATHINFO_EXTENSION)) . '"');
    header('Content-Length: ' . filesize($filePath));
    header('X-Content-Type-Options: nosniff');
    readfile($filePath);
    exit;
}

$settings = Settings::all($pdo);
$orgName = $settings['org_name'] ?? 'هم‌آوا HRM';
$common = ['currentUser' => $user, 'orgName' => $orgName, 'settings' => $settings];

if ($page === 'dashboard') {
    $activeEmployees = (int)$pdo->query("SELECT COUNT(*) FROM employees WHERE status='active'")->fetchColumn();
    $pendingStmt = $pdo->prepare("SELECT COUNT(*) FROM records WHERE module='leave' AND status='pending'" . (($user['role'] ?? '') === 'employee' ? ' AND created_by=' . (int)$user['id'] : ''));
    $pendingStmt->execute();
    $pendingLeaves = (int)$pendingStmt->fetchColumn();
    $attendanceSql = "SELECT status,data FROM records WHERE module='attendance' AND updated_at >= :today";
    $attendanceStmt = $pdo->prepare($attendanceSql . (($user['role'] ?? '') === 'employee' ? ' AND created_by=:owner' : ''));
    $attendanceParams = [':today' => date('Y-m-d') . ' 00:00:00'];
    if (($user['role'] ?? '') === 'employee') { $attendanceParams[':owner'] = (int)$user['id']; }
    $attendanceStmt->execute($attendanceParams);
    $attendanceRows = $attendanceStmt->fetchAll();
    $todayAttendanceCount = 0;
    $todayStatusCounts = ['present' => 0, 'remote' => 0, 'late' => 0, 'absent' => 0];
    foreach ($attendanceRows as $attendanceRow) {
        $attendanceData = json_decode((string)$attendanceRow['data'], true);
        if (!is_array($attendanceData) || ($attendanceData['work_date'] ?? '') !== date('Y-m-d')) {
            continue;
        }
        $todayAttendanceCount++;
        $state = (string)($attendanceData['status'] ?? $attendanceRow['status']);
        if (isset($todayStatusCounts[$state])) {
            $todayStatusCounts[$state]++;
        }
    }
    $recentEmployees = $pdo->query('SELECT * FROM employees ORDER BY id DESC LIMIT 5')->fetchAll();
    if (($user['role'] ?? '') === 'employee') {
        $recentEmployees = [];
        if (!empty($user['employee_id'])) {
            $self = $pdo->prepare('SELECT * FROM employees WHERE id=:id');
            $self->execute([':id' => (int)$user['employee_id']]);
            $recentEmployees = $self->fetchAll();
        }
    }
    $recentAudit = [];
    if (in_array($user['role'] ?? '', ['admin','hr'], true)) {
        $recentAudit = $pdo->query('SELECT a.*,u.name AS user_name FROM audit_logs a LEFT JOIN users u ON u.id=a.user_id ORDER BY a.id DESC LIMIT 7')->fetchAll();
    }
    $todayRecord = hrm_current_attendance($pdo, $user);
    $payrollTotal = null;
    if (in_array($user['role'] ?? '', ['admin','hr'], true)) {
        $payrollRows = $pdo->query("SELECT data FROM records WHERE module='payroll' AND status='paid'")->fetchAll();
        $payrollTotal = 0.0;
        foreach ($payrollRows as $payrollRow) {
            $data = json_decode((string)$payrollRow['data'], true);
            $payrollTotal += (float)($data['net_salary'] ?? 0);
        }
    }
    $taskSql = "SELECT COUNT(*) FROM records WHERE module='tasks' AND status IN ('todo','in_progress','blocked')";
    $taskStmt = $pdo->prepare($taskSql . (($user['role'] ?? '') === 'employee' ? ' AND created_by=:owner' : ''));
    $taskParams = ($user['role'] ?? '') === 'employee' ? [':owner' => (int)$user['id']] : [];
    $taskStmt->execute($taskParams);
    $openTasks = (int)$taskStmt->fetchColumn();
    $monthlyHires = [];
    for ($offset = 5; $offset >= 0; $offset--) {
        $month = date('Y-m', strtotime('-' . $offset . ' months'));
        $hireStmt = $pdo->prepare('SELECT COUNT(*) FROM employees WHERE hire_date LIKE :month');
        $hireStmt->execute([':month' => $month . '%']);
        $monthlyHires[] = ['label' => $month, 'count' => (int)$hireStmt->fetchColumn()];
    }
    render_view('dashboard', $common + [
        'pageTitle' => 'نمای کلی', 'activePage' => 'dashboard', 'activeEmployees' => $activeEmployees,
        'pendingLeaves' => $pendingLeaves, 'todayAttendanceCount' => $todayAttendanceCount,
        'todayStatusCounts' => $todayStatusCounts, 'recentEmployees' => $recentEmployees,
        'recentAudit' => $recentAudit, 'todayRecord' => $todayRecord, 'payrollTotal' => $payrollTotal,
        'openTasks' => $openTasks, 'monthlyHires' => $monthlyHires,
    ]);
    exit;
}

if ($page === 'reports') {
    if (!in_array($user['role'] ?? '', ['admin','hr','manager'], true)) {
        http_response_code(403);
        render_view('error', $common + ['pageTitle' => 'دسترسی محدود', 'activePage' => 'reports', 'errorTitle' => 'گزارش‌های مدیریتی در دسترس نیست.', 'errorText' => 'برای مشاهده این بخش با مدیر سامانه تماس بگیرید.']);
        exit;
    }
    $reportModules = [];
    foreach (ModuleRegistry::all() as $module) {
        if (!Access::canViewModule($module, $user)) {
            continue;
        }
        if ($module['entity'] === 'employees') {
            $count = (int)$pdo->query('SELECT COUNT(*) FROM employees')->fetchColumn();
            $reportModules[] = ['module' => $module, 'count' => $count, 'statuses' => []];
            continue;
        }
        $stmt = $pdo->prepare('SELECT status,COUNT(*) AS total FROM records WHERE module=:module GROUP BY status ORDER BY total DESC');
        $stmt->execute([':module' => $module['slug']]);
        $statuses = $stmt->fetchAll();
        $count = array_sum(array_map(static fn(array $row): int => (int)$row['total'], $statuses));
        $reportModules[] = ['module' => $module, 'count' => $count, 'statuses' => $statuses];
    }
    $departmentRows = $pdo->query("SELECT COALESCE(NULLIF(department,''),'بدون واحد') AS department,COUNT(*) AS total FROM employees GROUP BY department ORDER BY total DESC,department ASC")->fetchAll();
    render_view('reports', $common + ['pageTitle' => 'گزارش‌ها', 'activePage' => 'reports', 'reportModules' => $reportModules, 'departmentRows' => $departmentRows]);
    exit;
}

if ($page === 'about') {
    render_view('about', $common + ['pageTitle' => 'درباره سامانه', 'activePage' => 'about', 'featureGroups' => FeatureCatalog::groups()]);
    exit;
}

if ($page === 'profile') {
    $profileStmt = $pdo->prepare('SELECT u.name,u.email,u.role,u.created_at,u.last_login_at,e.employee_code,e.first_name,e.last_name,e.department,e.position FROM users u LEFT JOIN employees e ON e.id=u.employee_id WHERE u.id=:id');
    $profileStmt->execute([':id' => (int)$user['id']]);
    $profile = $profileStmt->fetch() ?: [];
    render_view('profile', $common + ['pageTitle' => 'حساب کاربری', 'activePage' => 'profile', 'profile' => $profile]);
    exit;
}

if ($page === 'users') {
    if (!Access::mayManageAdminArea($user)) {
        http_response_code(403);
        render_view('error', $common + ['pageTitle' => 'دسترسی محدود', 'activePage' => 'users', 'errorTitle' => 'این بخش فقط برای مدیر سامانه است.', 'errorText' => 'برای مدیریت کاربران با مدیر اصلی سامانه تماس بگیرید.']);
        exit;
    }
    $users = $pdo->query('SELECT u.id,u.name,u.email,u.role,u.active,u.employee_id,u.created_at,u.last_login_at,e.first_name,e.last_name,e.employee_code FROM users u LEFT JOIN employees e ON e.id=u.employee_id ORDER BY u.id DESC')->fetchAll();
    $employees = $pdo->query('SELECT id,employee_code,first_name,last_name FROM employees ORDER BY first_name,last_name')->fetchAll();
    render_view('users', $common + ['pageTitle' => 'کاربران و دسترسی‌ها', 'activePage' => 'users', 'users' => $users, 'employees' => $employees]);
    exit;
}

if ($page === 'settings') {
    if (!Access::mayManageAdminArea($user)) {
        http_response_code(403);
        render_view('error', $common + ['pageTitle' => 'دسترسی محدود', 'activePage' => 'settings', 'errorTitle' => 'این بخش فقط برای مدیر سامانه است.', 'errorText' => 'برای تغییر تنظیمات سازمان با مدیر اصلی تماس بگیرید.']);
        exit;
    }
    render_view('settings', $common + ['pageTitle' => 'تنظیمات سامانه', 'activePage' => 'settings']);
    exit;
}

if ($page === 'audit') {
    if (!in_array($user['role'] ?? '', ['admin','hr'], true)) {
        http_response_code(403);
        render_view('error', $common + ['pageTitle' => 'دسترسی محدود', 'activePage' => 'audit', 'errorTitle' => 'گزارش رویدادها در دسترس نیست.', 'errorText' => 'این گزارش فقط برای مدیر و منابع انسانی نمایش داده می‌شود.']);
        exit;
    }
    $auditRows = $pdo->query('SELECT a.*,u.name AS user_name FROM audit_logs a LEFT JOIN users u ON u.id=a.user_id ORDER BY a.id DESC LIMIT 150')->fetchAll();
    render_view('audit', $common + ['pageTitle' => 'گزارش رویدادها', 'activePage' => 'audit', 'auditRows' => $auditRows]);
    exit;
}

if ($page === 'search') {
    $query = trim((string)($_GET['q'] ?? ''));
    $results = [];
    if (strlen($query) >= 2) {
        $employeeSql = 'SELECT * FROM employees WHERE (employee_code LIKE :q OR first_name LIKE :q OR last_name LIKE :q OR email LIKE :q OR department LIKE :q)';
        $params = [':q' => '%' . $query . '%'];
        if (($user['role'] ?? '') === 'employee') {
            $employeeSql .= ' AND id=:employee_id';
            $params[':employee_id'] = (int)($user['employee_id'] ?? 0);
        }
        $stmt = $pdo->prepare($employeeSql . ' ORDER BY id DESC LIMIT 10');
        $stmt->execute($params);
        foreach ($stmt->fetchAll() as $employee) {
            $employeeUrl = ($user['role'] ?? '') === 'employee' ? app_url(['page' => 'employees']) : app_url(['page' => 'employees', 'action' => 'edit', 'id' => $employee['id']]);
            $results[] = ['type' => 'employee', 'title' => $employee['first_name'] . ' ' . $employee['last_name'], 'subtitle' => $employee['employee_code'] . ' · ' . $employee['department'], 'url' => $employeeUrl];
        }
        foreach (ModuleRegistry::all() as $module) {
            if ($module['entity'] !== 'records' || !Access::canViewModule($module, $user)) {
                continue;
            }
            $sql = 'SELECT id,title,created_by FROM records WHERE module=:module AND (title LIKE :q OR data LIKE :q)';
            $params = [':module' => $module['slug'], ':q' => '%' . $query . '%'];
            if (($user['role'] ?? '') === 'employee' && empty($module['employee_read_all'])) {
                $sql .= ' AND created_by=:owner';
                $params[':owner'] = (int)$user['id'];
            }
            $stmt = $pdo->prepare($sql . ' ORDER BY id DESC LIMIT 5');
            $stmt->execute($params);
            foreach ($stmt->fetchAll() as $record) {
                $results[] = ['type' => $module['name'], 'title' => $record['title'], 'subtitle' => 'رکورد در ' . $module['name'], 'url' => (($user['role'] ?? '') === 'employee' ? app_url(['page' => $module['slug']]) : app_url(['page' => $module['slug'], 'action' => 'edit', 'id' => $record['id']]))];
            }
        }
    }
    render_view('search', $common + ['pageTitle' => 'جست‌وجو', 'activePage' => 'search', 'query' => $query, 'results' => $results]);
    exit;
}

$module = ModuleRegistry::get($page);
if ($module) {
    if (!Access::canViewModule($module, $user)) {
        http_response_code(403);
        render_view('error', $common + ['pageTitle' => 'دسترسی محدود', 'activePage' => $page, 'errorTitle' => 'به این ماژول دسترسی ندارید.', 'errorText' => 'سطح دسترسی حساب شما امکان مشاهده این اطلاعات را نمی‌دهد.']);
        exit;
    }
    $action = (string)($_GET['action'] ?? '');
    $canManage = Access::canManageModule($module, $user);
    $recordId = (int)($_GET['id'] ?? 0);
    $editRecord = null;
    if ($action === 'new' || $action === 'edit') {
        if (!$canManage) {
            http_response_code(403);
            render_view('error', $common + ['pageTitle' => 'دسترسی محدود', 'activePage' => $page, 'errorTitle' => 'اجازه ثبت اطلاعات ندارید.', 'errorText' => 'برای این عملیات باید دسترسی مدیریتی داشته باشید.']);
            exit;
        }
        if ($action === 'edit') {
            $editRecord = RecordService::find($pdo, $module, $recordId);
            if (!$editRecord) {
                http_response_code(404);
                render_view('error', $common + ['pageTitle' => 'رکورد پیدا نشد', 'activePage' => $page, 'errorTitle' => 'رکورد موردنظر وجود ندارد.', 'errorText' => 'ممکن است رکورد حذف شده باشد.']);
                exit;
            }
            if (($user['role'] ?? '') === 'employee') {
                $owns = $module['entity'] === 'employees'
                    ? (int)$recordId === (int)($user['employee_id'] ?? 0)
                    : (int)($editRecord['created_by'] ?? 0) === (int)$user['id'];
                if (!$owns || !Access::isSelfService($module, $user)) {
                    http_response_code(403);
                    render_view('error', $common + ['pageTitle' => 'دسترسی محدود', 'activePage' => $page, 'errorTitle' => 'اجازه ویرایش این رکورد را ندارید.', 'errorText' => 'فقط رکوردهای شخصی مجاز قابل ویرایش هستند.']);
                    exit;
                }
                if ($module['entity'] === 'records' && !in_array((string)($editRecord['status'] ?? ''), ['pending','requested','draft'], true)) {
                    http_response_code(403);
                    render_view('error', $common + ['pageTitle' => 'رکورد قفل‌شده', 'activePage' => $page, 'errorTitle' => 'این درخواست پس از بررسی قفل شده است.', 'errorText' => 'برای جلوگیری از تغییر سابقه، رکوردهای بررسی‌شده قابل ویرایش نیستند.']);
                    exit;
                }
            }
        }
        $employeeOptions = $pdo->prepare('SELECT id,employee_code,first_name,last_name FROM employees WHERE status<>:inactive ORDER BY first_name,last_name');
        $employeeOptions->execute([':inactive' => 'inactive']);
        $employeeOptions = $employeeOptions->fetchAll();
        $recordValues = $module['entity'] === 'employees'
            ? ($editRecord ?? [])
            : ($editRecord['data_array'] ?? []);
        render_view('module-form', $common + [
            'pageTitle' => ($action === 'edit' ? 'ویرایش' : 'ثبت') . ' · ' . $module['name'],
            'activePage' => $page, 'module' => $module, 'editRecord' => $editRecord,
            'recordValues' => $recordValues, 'employeeOptions' => $employeeOptions,
            'formAction' => $action,
        ]);
        exit;
    }
    if ($action === 'delete' && $canManage && $recordId > 0) {
        $deleteRecord = RecordService::find($pdo, $module, $recordId);
        if ($deleteRecord) {
            if (($user['role'] ?? '') === 'employee' && ((int)($deleteRecord['created_by'] ?? 0) !== (int)$user['id'] || !in_array((string)($deleteRecord['status'] ?? ''), ['pending','requested','draft'], true))) {
                http_response_code(403);
                render_view('error', $common + ['pageTitle' => 'دسترسی محدود', 'activePage' => $page, 'errorTitle' => 'این درخواست قابل حذف نیست.', 'errorText' => 'فقط درخواست شخصیِ در انتظار بررسی قابل حذف است.']);
                exit;
            }
            render_view('module-delete', $common + ['pageTitle' => 'حذف رکورد', 'activePage' => $page, 'module' => $module, 'deleteRecord' => $deleteRecord]);
            exit;
        }
    }
    $query = trim((string)($_GET['q'] ?? ''));
    $statusFilter = trim((string)($_GET['status'] ?? ''));
    $currentPage = max(1, (int)($_GET['p'] ?? 1));
    $ownerId = null;
    $employeeId = null;
    if (($user['role'] ?? '') === 'employee') {
        if ($module['entity'] === 'employees') {
            $employeeId = (int)($user['employee_id'] ?? 0);
        } elseif (empty($module['employee_read_all']) && $page !== 'announcements') {
            $ownerId = (int)$user['id'];
        }
    }
    $listing = RecordService::paginate($pdo, $module, $query, $statusFilter, $currentPage, 20, $ownerId, $employeeId);
    $titleField = null;
    foreach ($module['fields'] as $field) {
        if (($field['name'] ?? '') === ($module['primary'] ?? 'title')) {
            $titleField = $field;
            break;
        }
    }
    $statusField = hrm_status_field($module);
    render_view('module-list', $common + [
        'pageTitle' => $module['name'], 'activePage' => $page, 'module' => $module,
        'listing' => $listing, 'query' => $query, 'statusFilter' => $statusFilter,
        'canManage' => $canManage, 'titleField' => $titleField, 'statusField' => $statusField,
    ]);
    exit;
}

http_response_code(404);
render_view('error', $common + ['pageTitle' => 'صفحه پیدا نشد', 'activePage' => '', 'errorTitle' => 'این صفحه وجود ندارد.', 'errorText' => 'از منوی کناری برای رفتن به بخش‌های سامانه استفاده کنید.']);
