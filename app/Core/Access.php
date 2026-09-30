<?php
declare(strict_types=1);

namespace HRM\Core;

final class Access
{
    private const EMPLOYEE_MODULES = ['employees', 'attendance', 'leave', 'expenses', 'travel', 'announcements', 'training', 'tasks'];
    private const EMPLOYEE_SELF_SERVICE = ['leave', 'expenses', 'travel'];
    private const SENSITIVE_MODULES = ['payroll', 'documents', 'disciplinary', 'compensation', 'contracts'];

    public static function isAdmin(array $user): bool
    {
        return ($user['role'] ?? '') === 'admin';
    }

    public static function canViewModule(array $module, array $user): bool
    {
        $role = (string)($user['role'] ?? '');
        if ($role === 'admin' || $role === 'hr') {
            return true;
        }
        if (self::isSensitive($module)) {
            return false;
        }
        if ($role === 'manager') {
            return true;
        }
        return $role === 'employee' && (in_array($module['slug'], self::EMPLOYEE_MODULES, true) || !empty($module['employee_view']) || !empty($module['employee_manage']));
    }

    public static function canManageModule(array $module, array $user): bool
    {
        $role = (string)($user['role'] ?? '');
        if ($role === 'admin' || $role === 'hr') {
            return true;
        }
        if (self::isSensitive($module)) {
            return false;
        }
        if ($role === 'manager') {
            return true;
        }
        return $role === 'employee' && (in_array($module['slug'], self::EMPLOYEE_SELF_SERVICE, true) || !empty($module['employee_manage']));
    }

    public static function isSensitive(array $module): bool
    {
        return in_array($module['slug'], self::SENSITIVE_MODULES, true) || (bool)($module['sensitive'] ?? false);
    }

    public static function isSelfService(array $module, array $user): bool
    {
        return ($user['role'] ?? '') === 'employee' && (in_array($module['slug'], self::EMPLOYEE_SELF_SERVICE, true) || !empty($module['employee_manage']));
    }

    public static function mayReadOwnRecords(array $module, array $user): bool
    {
        return ($user['role'] ?? '') === 'employee' && $module['slug'] !== 'announcements';
    }

    public static function mayManageAdminArea(array $user): bool
    {
        return ($user['role'] ?? '') === 'admin';
    }
}
