<?php
declare(strict_types=1);

namespace HRM\Core;

use PDO;
use RuntimeException;

final class RecordService
{
    public static function find(PDO $pdo, array $module, int $id): ?array
    {
        if ($module['entity'] === 'employees') {
            $stmt = $pdo->prepare('SELECT * FROM employees WHERE id=:id LIMIT 1');
            $stmt->execute([':id' => $id]);
            return $stmt->fetch() ?: null;
        }
        $stmt = $pdo->prepare('SELECT * FROM records WHERE id=:id AND module=:module LIMIT 1');
        $stmt->execute([':id' => $id, ':module' => $module['slug']]);
        $record = $stmt->fetch();
        if (!$record) {
            return null;
        }
        $decoded = json_decode((string)$record['data'], true);
        $record['data_array'] = is_array($decoded) ? $decoded : [];
        return $record;
    }

    public static function paginate(PDO $pdo, array $module, string $query, string $status, int $page, int $perPage, ?int $ownerId = null, ?int $employeeId = null): array
    {
        $page = max(1, $page);
        $perPage = min(100, max(10, $perPage));
        $where = [];
        $params = [];
        if ($module['entity'] === 'employees') {
            if ($query !== '') {
                $where[] = '(employee_code LIKE :search OR first_name LIKE :search OR last_name LIKE :search OR email LIKE :search OR department LIKE :search OR position LIKE :search)';
                $params[':search'] = '%' . $query . '%';
            }
            if ($status !== '') {
                $where[] = 'status = :status';
                $params[':status'] = $status;
            }
            if ($employeeId !== null) {
                $where[] = 'id = :employee_id';
                $params[':employee_id'] = $employeeId;
            }
            $clause = $where ? ' WHERE ' . implode(' AND ', $where) : '';
            $count = $pdo->prepare('SELECT COUNT(*) FROM employees' . $clause);
            $count->execute($params);
            $total = (int)$count->fetchColumn();
            $stmt = $pdo->prepare('SELECT * FROM employees' . $clause . ' ORDER BY id DESC LIMIT :limit OFFSET :offset');
        } else {
            $where[] = 'module = :module';
            $params[':module'] = $module['slug'];
            if ($query !== '') {
                $where[] = '(title LIKE :search OR data LIKE :search)';
                $params[':search'] = '%' . $query . '%';
            }
            if ($status !== '') {
                $where[] = 'status = :status';
                $params[':status'] = $status;
            }
            if ($ownerId !== null) {
                $where[] = 'created_by = :owner_id';
                $params[':owner_id'] = $ownerId;
            }
            $clause = ' WHERE ' . implode(' AND ', $where);
            $count = $pdo->prepare('SELECT COUNT(*) FROM records' . $clause);
            $count->execute($params);
            $total = (int)$count->fetchColumn();
            $stmt = $pdo->prepare('SELECT * FROM records' . $clause . ' ORDER BY updated_at DESC, id DESC LIMIT :limit OFFSET :offset');
        }
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        if ($module['entity'] === 'records') {
            foreach ($rows as &$row) {
                $decoded = json_decode((string)$row['data'], true);
                $row['data_array'] = is_array($decoded) ? $decoded : [];
            }
            unset($row);
        }
        return ['rows' => $rows, 'total' => $total, 'page' => $page, 'pages' => max(1, (int)ceil($total / $perPage)), 'per_page' => $perPage];
    }

    public static function save(PDO $pdo, array $module, array $input, array $user, ?int $id = null): int
    {
        $old = $id ? self::find($pdo, $module, $id) : null;
        if ($id && !$old) {
            throw new RuntimeException('رکورد موردنظر پیدا نشد.');
        }
        if ($module['entity'] === 'employees') {
            return self::saveEmployee($pdo, $module, $input, $user, $id, $old);
        }
        $oldData = $old['data_array'] ?? [];
        $values = self::validateFields($pdo, $module, $input, $oldData);
        if (isset($values['net_salary']) && $module['slug'] === 'payroll' && trim((string)$values['net_salary']) === '') {
            $values['net_salary'] = (string)(max(0, (float)($values['base_salary'] ?? 0) + (float)($values['allowances'] ?? 0) - (float)($values['deductions'] ?? 0)));
        }
        if (Access::isSelfService($module, $user)) {
            if (empty($user['employee_id'])) {
                throw new RuntimeException('حساب شما هنوز به پرونده کارمندی متصل نشده است. با مدیر سامانه تماس بگیرید.');
            }
            foreach ($module['fields'] as $field) {
                if (($field['type'] ?? '') === 'employee') {
                    $values[$field['name']] = (string)(int)$user['employee_id'];
                }
            }
            if (isset($values['status']) && in_array($module['slug'], ['leave', 'expenses', 'travel'], true)) {
                $initialStatus = $module['slug'] === 'travel' ? 'requested' : 'pending';
                $values['status'] = $id ? (string)($oldData['status'] ?? $initialStatus) : $initialStatus;
            }
        }
        $primary = (string)($module['primary'] ?? 'title');
        $title = trim((string)($values[$primary] ?? ''));
        if ($title === '') {
            throw new RuntimeException('عنوان اصلی رکورد را وارد کنید.');
        }
        $status = (string)($values['status'] ?? '');
        $json = json_encode($values, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        if (!is_string($json)) {
            throw new RuntimeException('ذخیره داده‌های فرم ممکن نیست.');
        }
        $now = date('Y-m-d H:i:s');
        if ($id) {
            $ownerCheck = $pdo->prepare('SELECT created_by FROM records WHERE id=:id AND module=:module');
            $ownerCheck->execute([':id' => $id, ':module' => $module['slug']]);
            $owner = $ownerCheck->fetchColumn();
            if (($user['role'] ?? '') === 'employee' && (int)$owner !== (int)$user['id']) {
                throw new RuntimeException('اجازه ویرایش این رکورد را ندارید.');
            }
            $stmt = $pdo->prepare('UPDATE records SET title=:title,status=:status,data=:data,updated_by=:user,updated_at=:now WHERE id=:id AND module=:module');
            $stmt->execute([':title' => $title, ':status' => $status, ':data' => $json, ':user' => (int)$user['id'], ':now' => $now, ':id' => $id, ':module' => $module['slug']]);
            self::removeReplacedFiles($module, $oldData, $values);
            Audit::log($pdo, (int)$user['id'], 'ویرایش رکورد', $module['slug'], $id, $title);
            return $id;
        }
        $stmt = $pdo->prepare('INSERT INTO records(module,title,status,data,created_by,updated_by,created_at,updated_at) VALUES(:module,:title,:status,:data,:user,:user,:now,:now)');
        $stmt->execute([':module' => $module['slug'], ':title' => $title, ':status' => $status, ':data' => $json, ':user' => (int)$user['id'], ':now' => $now]);
        $newId = (int)$pdo->lastInsertId();
        Audit::log($pdo, (int)$user['id'], 'ایجاد رکورد', $module['slug'], $newId, $title);
        return $newId;
    }

    public static function updateStatus(PDO $pdo, array $module, int $id, string $status, array $user): void
    {
        if ($module['entity'] !== 'records') {
            throw new RuntimeException('تغییر وضعیت مستقیم برای این ماژول پشتیبانی نمی‌شود.');
        }
        $record = self::find($pdo, $module, $id);
        if (!$record) {
            throw new RuntimeException('رکورد موردنظر پیدا نشد.');
        }
        $statusField = null;
        foreach ($module['fields'] as $field) {
            if (($field['name'] ?? '') === 'status') {
                $statusField = $field;
                break;
            }
        }
        if (!$statusField || !array_key_exists($status, $statusField['options'] ?? [])) {
            throw new RuntimeException('وضعیت انتخاب‌شده معتبر نیست.');
        }
        $owner = $pdo->prepare('SELECT created_by FROM records WHERE id=:id');
        $owner->execute([':id' => $id]);
        if (($user['role'] ?? '') === 'employee' && (int)$owner->fetchColumn() !== (int)$user['id']) {
            throw new RuntimeException('اجازه تغییر این رکورد را ندارید.');
        }
        $data = $record['data_array'];
        $data['status'] = $status;
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $pdo->prepare('UPDATE records SET status=:status,data=:data,updated_by=:user,updated_at=:now WHERE id=:id AND module=:module')->execute([
            ':status' => $status, ':data' => $json, ':user' => (int)$user['id'], ':now' => date('Y-m-d H:i:s'), ':id' => $id, ':module' => $module['slug'],
        ]);
        Audit::log($pdo, (int)$user['id'], 'تغییر وضعیت', $module['slug'], $id, $status);
    }

    public static function delete(PDO $pdo, array $module, int $id, array $user): void
    {
        if ($module['entity'] === 'employees') {
            $stmt = $pdo->prepare('SELECT first_name,last_name FROM employees WHERE id=:id');
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();
            if (!$row) {
                throw new RuntimeException('کارمند موردنظر پیدا نشد.');
            }
            $pdo->prepare('UPDATE users SET employee_id=NULL WHERE employee_id=:id')->execute([':id' => $id]);
            $pdo->prepare('DELETE FROM employees WHERE id=:id')->execute([':id' => $id]);
            Audit::log($pdo, (int)$user['id'], 'حذف پرونده کارمند', 'employees', $id, $row['first_name'] . ' ' . $row['last_name']);
            return;
        }
        $record = self::find($pdo, $module, $id);
        if (!$record) {
            throw new RuntimeException('رکورد موردنظر پیدا نشد.');
        }
        if (($user['role'] ?? '') === 'employee' && (int)$record['created_by'] !== (int)$user['id']) {
            throw new RuntimeException('اجازه حذف این رکورد را ندارید.');
        }
        $pdo->prepare('DELETE FROM records WHERE id=:id AND module=:module')->execute([':id' => $id, ':module' => $module['slug']]);
        self::removeReplacedFiles($module, $record['data_array'], []);
        Audit::log($pdo, (int)$user['id'], 'حذف رکورد', $module['slug'], $id, (string)$record['title']);
    }

    private static function saveEmployee(PDO $pdo, array $module, array $input, array $user, ?int $id, ?array $old): int
    {
        $values = self::validateFields($pdo, $module, $input, $old ?? []);
        if (($user['role'] ?? '') === 'employee') {
            if (!$id || (int)($user['employee_id'] ?? 0) !== $id) {
                throw new RuntimeException('شما فقط می‌توانید پرونده کاربری متصل به حسابتان را مشاهده کنید.');
            }
            foreach (['employee_code','department','position','manager','employment_type','status','hire_date'] as $protected) {
                $values[$protected] = $old[$protected] ?? ($values[$protected] ?? '');
            }
        }
        $values['employee_code'] = trim((string)($values['employee_code'] ?? ''));
        $values['first_name'] = trim((string)($values['first_name'] ?? ''));
        $values['last_name'] = trim((string)($values['last_name'] ?? ''));
        if ($values['employee_code'] === '' || $values['first_name'] === '' || $values['last_name'] === '') {
            throw new RuntimeException('کد پرسنلی، نام و نام خانوادگی الزامی هستند.');
        }
        $now = date('Y-m-d H:i:s');
        $columns = ['employee_code','first_name','last_name','email','phone','department','position','manager','hire_date','employment_type','status','location','notes'];
        if ($id) {
            $set = implode(',', array_map(static fn(string $column): string => $column . '=:' . $column, $columns));
            $stmt = $pdo->prepare('UPDATE employees SET ' . $set . ',updated_at=:updated_at WHERE id=:id');
            foreach ($columns as $column) {
                $stmt->bindValue(':' . $column, (string)($values[$column] ?? ''));
            }
            $stmt->bindValue(':updated_at', $now);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            Audit::log($pdo, (int)$user['id'], 'ویرایش پرونده کارمند', 'employees', $id, $values['first_name'] . ' ' . $values['last_name']);
            return $id;
        }
        $params = [];
        foreach ($columns as $column) {
            $params[':' . $column] = (string)($values[$column] ?? '');
        }
        $params[':created_at'] = $now;
        $params[':updated_at'] = $now;
        $placeholders = implode(',', array_map(static fn(string $column): string => ':' . $column, $columns));
        $stmt = $pdo->prepare('INSERT INTO employees(' . implode(',', $columns) . ',created_at,updated_at) VALUES(' . $placeholders . ',:created_at,:updated_at)');
        $stmt->execute($params);
        $newId = (int)$pdo->lastInsertId();
        Audit::log($pdo, (int)$user['id'], 'ایجاد پرونده کارمند', 'employees', $newId, $values['first_name'] . ' ' . $values['last_name']);
        return $newId;
    }

    private static function validateFields(PDO $pdo, array $module, array $input, array $old): array
    {
        $values = [];
        $employeeMap = null;
        foreach ($module['fields'] as $field) {
            $name = (string)$field['name'];
            $type = (string)$field['type'];
            $raw = $input[$name] ?? ($field['default'] ?? ($old[$name] ?? ''));
            if ($type === 'file') {
                $oldFile = (string)($old[$name] ?? '');
                $values[$name] = UploadService::save($_FILES[$name] ?? [], $oldFile);
                continue;
            }
            $value = is_scalar($raw) ? trim((string)$raw) : '';
            $value = normalize_digits($value);
            if (($field['required'] ?? false) && $value === '') {
                throw new RuntimeException('فیلد «' . $field['label'] . '» الزامی است.');
            }
            if ($value === '') {
                $values[$name] = '';
                continue;
            }
            if ($type === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('ایمیل واردشده در فیلد «' . $field['label'] . '» معتبر نیست.');
            }
            if ($type === 'select' && !array_key_exists($value, $field['options'] ?? [])) {
                throw new RuntimeException('گزینه انتخاب‌شده برای فیلد «' . $field['label'] . '» معتبر نیست.');
            }
            if ($type === 'employee') {
                if (!ctype_digit($value)) {
                    throw new RuntimeException('کارمند انتخاب‌شده معتبر نیست.');
                }
                if ($employeeMap === null) {
                    $employeeMap = [];
                    foreach ($pdo->query("SELECT id,first_name,last_name,employee_code FROM employees ORDER BY first_name,last_name") as $employee) {
                        $employeeMap[(string)$employee['id']] = $employee['first_name'] . ' ' . $employee['last_name'];
                    }
                }
                if (!isset($employeeMap[$value])) {
                    throw new RuntimeException('کارمند انتخاب‌شده در سامانه وجود ندارد.');
                }
            }
            if ($type === 'number') {
                $numeric = str_replace([',', '٬', ' '], '', $value);
                if (!is_numeric($numeric) || (float)$numeric < 0) {
                    throw new RuntimeException('مقدار فیلد «' . $field['label'] . '» باید عددی و غیرمنفی باشد.');
                }
                $value = (string)(0 + $numeric);
            }
            if ($type === 'date' && !self::validDate($value, 'Y-m-d')) {
                throw new RuntimeException('تاریخ فیلد «' . $field['label'] . '» معتبر نیست.');
            }
            if ($type === 'month' && !self::validDate($value, 'Y-m')) {
                throw new RuntimeException('ماه فیلد «' . $field['label'] . '» معتبر نیست.');
            }
            if ($type === 'time' && !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $value)) {
                throw new RuntimeException('ساعت فیلد «' . $field['label'] . '» معتبر نیست.');
            }
            if (in_array($type, ['text','email','tel','select','employee','date','month','time'], true) && strlen($value) > 255) {
                throw new RuntimeException('مقدار فیلد «' . $field['label'] . '» بیش از حد طولانی است.');
            }
            if ($type === 'textarea' && strlen($value) > 8000) {
                throw new RuntimeException('متن فیلد «' . $field['label'] . '» بیش از حد طولانی است.');
            }
            $values[$name] = $value;
        }
        return $values;
    }

    private static function removeReplacedFiles(array $module, array $oldValues, array $newValues): void
    {
        foreach ($module['fields'] as $field) {
            if (($field['type'] ?? '') !== 'file') {
                continue;
            }
            $key = (string)$field['name'];
            $oldFile = (string)($oldValues[$key] ?? '');
            $newFile = (string)($newValues[$key] ?? '');
            if ($oldFile !== '' && $oldFile !== $newFile) {
                UploadService::delete($oldFile);
            }
        }
    }

    private static function validDate(string $value, string $format): bool
    {
        $date = \DateTimeImmutable::createFromFormat('!' . $format, $value);
        return $date instanceof \DateTimeImmutable && $date->format($format) === $value;
    }
}
