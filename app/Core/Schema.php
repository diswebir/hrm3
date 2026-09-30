<?php
declare(strict_types=1);

namespace HRM\Core;

use PDO;

final class Schema
{
    public static function migrate(PDO $pdo): void
    {
        $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS employees (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    employee_code TEXT NOT NULL UNIQUE,
    first_name TEXT NOT NULL,
    last_name TEXT NOT NULL,
    email TEXT NOT NULL DEFAULT '',
    phone TEXT NOT NULL DEFAULT '',
    department TEXT NOT NULL DEFAULT '',
    position TEXT NOT NULL DEFAULT '',
    manager TEXT NOT NULL DEFAULT '',
    hire_date TEXT NOT NULL DEFAULT '',
    employment_type TEXT NOT NULL DEFAULT 'full_time',
    status TEXT NOT NULL DEFAULT 'active',
    location TEXT NOT NULL DEFAULT '',
    notes TEXT NOT NULL DEFAULT '',
    user_id INTEGER NULL,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_employees_status ON employees(status);
CREATE INDEX IF NOT EXISTS idx_employees_department ON employees(department);

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE COLLATE NOCASE,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'employee' CHECK(role IN ('admin','hr','manager','employee')),
    active INTEGER NOT NULL DEFAULT 1,
    employee_id INTEGER NULL,
    last_login_at TEXT NULL,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role, active);
CREATE UNIQUE INDEX IF NOT EXISTS idx_users_employee_unique ON users(employee_id) WHERE employee_id IS NOT NULL;

CREATE TABLE IF NOT EXISTS records (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    module TEXT NOT NULL,
    title TEXT NOT NULL DEFAULT '',
    status TEXT NOT NULL DEFAULT '',
    data TEXT NOT NULL DEFAULT '{}',
    created_by INTEGER NULL,
    updated_by INTEGER NULL,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_records_module_created ON records(module, created_at DESC);
CREATE INDEX IF NOT EXISTS idx_records_module_status ON records(module, status);
CREATE INDEX IF NOT EXISTS idx_records_created_by ON records(created_by);

CREATE TABLE IF NOT EXISTS settings (
    setting_key TEXT PRIMARY KEY,
    setting_value TEXT NOT NULL DEFAULT ''
);

CREATE TABLE IF NOT EXISTS audit_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NULL,
    action TEXT NOT NULL,
    module TEXT NOT NULL DEFAULT '',
    record_id INTEGER NULL,
    details TEXT NOT NULL DEFAULT '',
    ip_address TEXT NOT NULL DEFAULT '',
    created_at TEXT NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_audit_created ON audit_logs(created_at DESC);
SQL);
        $pdo->exec('PRAGMA user_version = 1');
        self::seedDefaults($pdo);
    }

    public static function seedDefaults(PDO $pdo): void
    {
        $insert = $pdo->prepare('INSERT OR IGNORE INTO settings(setting_key, setting_value) VALUES(:key, :value)');
        $defaults = [
            'org_name' => 'هم‌آوا | سامانه منابع انسانی',
            'org_phone' => '',
            'org_email' => '',
            'currency' => 'تومان',
            'timezone' => 'Asia/Tehran',
            'work_week' => 'شنبه تا چهارشنبه',
            'fiscal_year_start' => '01-01',
        ];
        foreach ($defaults as $key => $value) {
            $insert->execute([':key' => $key, ':value' => $value]);
        }
    }

    public static function seedDemo(PDO $pdo, int $adminId): void
    {
        $now = date('Y-m-d H:i:s');
        $employees = [
            ['HR-1001', 'آرمان', 'نادری', 'arman@example.com', '09120000001', 'فناوری اطلاعات', 'مدیر محصول', '2021-04-12', 'active', 'تهران'],
            ['HR-1002', 'نگار', 'قاسمی', 'negar@example.com', '09120000002', 'منابع انسانی', 'کارشناس جذب', '2022-01-20', 'active', 'تهران'],
            ['HR-1003', 'پرهام', 'کریمی', 'parham@example.com', '09120000003', 'مالی', 'تحلیلگر مالی', '2022-08-01', 'active', 'اصفهان'],
            ['HR-1004', 'سارا', 'محمدی', 'sara@example.com', '09120000004', 'فروش', 'مدیر فروش', '2020-11-09', 'on_leave', 'تهران'],
            ['HR-1005', 'امیرعلی', 'رضایی', 'amir@example.com', '09120000005', 'فناوری اطلاعات', 'توسعه‌دهنده', '2023-03-14', 'active', 'شیراز'],
            ['HR-1006', 'آوا', 'صادقی', 'ava@example.com', '09120000006', 'بازاریابی', 'طراح محتوا', '2024-02-05', 'active', 'تهران'],
        ];
        $employeeInsert = $pdo->prepare('INSERT INTO employees(employee_code,first_name,last_name,email,phone,department,position,hire_date,status,location,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)');
        foreach ($employees as $employee) {
            $employeeInsert->execute([...$employee, $now, $now]);
        }
        $records = [
            ['leave', 'درخواست مرخصی سالانه', 'pending', ['title' => 'درخواست مرخصی سالانه', 'employee' => '2', 'leave_type' => 'annual', 'start_date' => date('Y-m-d', strtotime('+4 days')), 'end_date' => date('Y-m-d', strtotime('+6 days')), 'days' => '3', 'reason' => 'امور شخصی', 'status' => 'pending']],
            ['leave', 'مرخصی سارا محمدی', 'approved', ['title' => 'مرخصی سارا محمدی', 'employee' => '4', 'leave_type' => 'annual', 'start_date' => date('Y-m-d', strtotime('-8 days')), 'end_date' => date('Y-m-d', strtotime('-7 days')), 'days' => '2', 'reason' => 'استراحت', 'status' => 'approved']],
            ['tasks', 'تکمیل برنامه ورود همکاران جدید', 'in_progress', ['title' => 'تکمیل برنامه ورود همکاران جدید', 'assigned_to' => '2', 'priority' => 'high', 'due_date' => date('Y-m-d', strtotime('+5 days')), 'description' => 'بازبینی چک‌لیست روز اول و دسترسی‌ها', 'status' => 'in_progress']],
            ['announcements', 'به‌روزرسانی ساعت کاری تابستان', 'published', ['title' => 'به‌روزرسانی ساعت کاری تابستان', 'audience' => 'all', 'published_on' => date('Y-m-d'), 'body' => 'لطفاً برنامه کاری به‌روزشده را در پنل مطالعه کنید.', 'status' => 'published']],
        ];
        $recordInsert = $pdo->prepare('INSERT INTO records(module,title,status,data,created_by,updated_by,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?)');
        foreach ($records as [$module, $title, $status, $data]) {
            $recordInsert->execute([$module, $title, $status, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $adminId, $adminId, $now, $now]);
        }
        $attendance = ['title' => 'حضور امروز', 'employee' => '1', 'work_date' => date('Y-m-d'), 'check_in' => '08:42', 'check_out' => '', 'status' => 'present', 'note' => ''];
        $recordInsert->execute(['attendance', $attendance['title'], $attendance['status'], json_encode($attendance, JSON_UNESCAPED_UNICODE), $adminId, $adminId, $now, $now]);
    }
}
