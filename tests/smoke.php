<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use HRM\Core\Access;
use HRM\Core\Database;
use HRM\Core\FeatureCatalog;
use HRM\Core\ModuleRegistry;
use HRM\Core\RecordService;
use HRM\Core\Schema;

function expect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$path = tempnam(sys_get_temp_dir(), 'hamava-hrm-test-');
if ($path === false) {
    throw new RuntimeException('Unable to create temporary SQLite file.');
}
try {
    $pdo = Database::connectPath($path);
    Schema::migrate($pdo);
    $modules = ModuleRegistry::all();
    expect(count($modules) >= 25, 'Expected at least 25 built-in modules.');

    $now = date('Y-m-d H:i:s');
    $pdo->prepare('INSERT INTO users(name,email,password_hash,role,active,created_at,updated_at) VALUES(?,?,?,?,?,?,?)')
        ->execute(['Smoke Admin', 'smoke@example.test', password_hash('test-only-password', PASSWORD_DEFAULT), 'admin', 1, $now, $now]);
    $user = ['id' => (int)$pdo->lastInsertId(), 'role' => 'admin', 'employee_id' => null, 'name' => 'Smoke Admin'];

    $employeeId = RecordService::save($pdo, $modules['employees'], [
        'employee_code' => 'TEST-001', 'first_name' => 'آزمایشی', 'last_name' => 'کارمند',
        'email' => 'employee@example.test', 'phone' => '09120000000', 'department' => 'فناوری',
        'position' => 'توسعه‌دهنده', 'manager' => '', 'hire_date' => '2026-01-05',
        'employment_type' => 'full_time', 'status' => 'active', 'location' => 'تهران', 'notes' => '',
    ], $user);

    $genericCount = 0;
    foreach ($modules as $module) {
        if ($module['entity'] === 'employees') {
            continue;
        }
        $input = [];
        foreach ($module['fields'] as $field) {
            $name = $field['name'];
            switch ($field['type']) {
                case 'file':
                    $input[$name] = '';
                    break;
                case 'employee':
                    $input[$name] = (string)$employeeId;
                    break;
                case 'select':
                    $input[$name] = (string)(array_key_first($field['options'] ?? []) ?? '');
                    break;
                case 'email':
                    $input[$name] = 'candidate@example.test';
                    break;
                case 'date':
                    $input[$name] = '2026-09-30';
                    break;
                case 'month':
                    $input[$name] = '2026-09';
                    break;
                case 'time':
                    $input[$name] = '09:00';
                    break;
                case 'number':
                    $input[$name] = '2';
                    break;
                case 'textarea':
                    $input[$name] = 'Smoke test';
                    break;
                default:
                    $input[$name] = $name === 'title' ? 'نمونه ' . $module['name'] : 'نمونه';
            }
        }
        $id = RecordService::save($pdo, $module, $input, $user);
        expect(RecordService::find($pdo, $module, $id) !== null, 'Record read failed: ' . $module['slug']);
        $input[$module['primary'] ?? 'title'] = 'ویرایش‌شده ' . $module['name'];
        RecordService::save($pdo, $module, $input, $user, $id);
        $updated = RecordService::find($pdo, $module, $id);
        expect(($updated['data_array'][$module['primary'] ?? 'title'] ?? '') === $input[$module['primary'] ?? 'title'], 'Record update failed: ' . $module['slug']);
        $secondId = RecordService::save($pdo, $module, $input, $user);
        RecordService::delete($pdo, $module, $secondId, $user);
        expect(RecordService::paginate($pdo, $module, '', '', 1, 20)['total'] === 1, 'Record list/delete failed: ' . $module['slug']);
        $genericCount++;
    }

    $featureCount = 0;
    foreach (FeatureCatalog::groups() as $group) {
        $featureCount += count($group['items']);
    }
    expect($featureCount >= 100, 'About page must document at least 100 features.');
    expect($genericCount === count($modules) - 1, 'Each generic module should have passed a CRUD smoke test.');
    expect(Access::canViewModule($modules['payroll'], ['role' => 'admin']), 'Admin should access payroll.');
    expect(!Access::canViewModule($modules['payroll'], ['role' => 'manager']), 'Manager must not access payroll.');
    expect(Access::canManageModule($modules['leave'], ['role' => 'employee']), 'Employee self-service should support leave requests.');

    echo 'OK: ' . count($modules) . ' modules; ' . $genericCount . ' generic module CRUD checks; ' . $featureCount . ' documented features.' . PHP_EOL;
    $pdo = null;
} finally {
    @unlink($path);
    @unlink($path . '-journal');
}
