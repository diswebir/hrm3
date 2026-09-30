<?php
declare(strict_types=1);

namespace HRM\Core;

use RuntimeException;

final class ModuleRegistry
{
    private static ?array $modules = null;

    public static function all(): array
    {
        if (self::$modules !== null) {
            return self::$modules;
        }
        $modules = [];
        foreach (glob(HRM_APP . '/Modules/*/module.php') ?: [] as $file) {
            try {
                $manifest = require $file;
                if (!is_array($manifest)) {
                    continue;
                }
                $slug = (string)($manifest['slug'] ?? '');
                if (!preg_match('/^[a-z][a-z0-9_-]{1,40}$/', $slug) || basename(dirname($file)) !== $slug) {
                    continue;
                }
                $manifest['name'] = (string)($manifest['name'] ?? $slug);
                $manifest['description'] = (string)($manifest['description'] ?? '');
                $manifest['group'] = (string)($manifest['group'] ?? 'سایر');
                $manifest['icon'] = (string)($manifest['icon'] ?? 'file');
                $manifest['entity'] = (string)($manifest['entity'] ?? 'records');
                $manifest['fields'] = is_array($manifest['fields'] ?? null) ? $manifest['fields'] : [];
                $manifest['columns'] = is_array($manifest['columns'] ?? null) ? $manifest['columns'] : [];
                $manifest['_directory'] = dirname($file);
                self::validateFields($manifest);
                $modules[$slug] = $manifest;
            } catch (\Throwable $exception) {
                error_log('[HRM module] Manifest skipped: ' . basename(dirname($file)) . ' — ' . $exception->getMessage());
            }
        }
        uasort($modules, static fn(array $a, array $b): int => ((int)($a['order'] ?? 100)) <=> ((int)($b['order'] ?? 100)));
        self::$modules = $modules;
        return $modules;
    }

    public static function get(string $slug): ?array
    {
        return self::all()[$slug] ?? null;
    }

    public static function groups(): array
    {
        $groups = [];
        foreach (self::all() as $module) {
            if (($module['nav'] ?? true) === false) {
                continue;
            }
            $groups[$module['group']][] = $module;
        }
        return $groups;
    }

    public static function optionLabel(array $field, mixed $value): string
    {
        $key = (string)$value;
        if ($field['type'] === 'employee') {
            return $key;
        }
        $options = $field['options'] ?? [];
        return (string)($options[$key] ?? ($key !== '' ? $key : '—'));
    }

    private static function validateFields(array $manifest): void
    {
        $allowedTypes = ['text', 'email', 'tel', 'date', 'month', 'time', 'number', 'select', 'textarea', 'file', 'employee'];
        $seen = [];
        foreach ($manifest['fields'] as $field) {
            if (!is_array($field) || empty($field['name']) || !preg_match('/^[a-z][a-z0-9_]{0,48}$/', (string)$field['name'])) {
                throw new RuntimeException('Invalid field declaration in module: ' . ($manifest['slug'] ?? 'unknown'));
            }
            if (isset($seen[$field['name']])) {
                throw new RuntimeException('Duplicate field in module: ' . ($manifest['slug'] ?? 'unknown'));
            }
            if (!in_array((string)($field['type'] ?? 'text'), $allowedTypes, true)) {
                throw new RuntimeException('Unsupported field type in module: ' . ($manifest['slug'] ?? 'unknown'));
            }
            if (($field['type'] ?? '') === 'select' && !is_array($field['options'] ?? null)) {
                throw new RuntimeException('Select fields require an options array in module: ' . ($manifest['slug'] ?? 'unknown'));
            }
            $seen[$field['name']] = true;
        }
        $primary = (string)($manifest['primary'] ?? 'title');
        if (!isset($seen[$primary])) {
            throw new RuntimeException('The primary field is missing in module: ' . ($manifest['slug'] ?? 'unknown'));
        }
        foreach ($manifest['columns'] as $column) {
            if (!isset($seen[(string)$column])) {
                throw new RuntimeException('A list column is not defined in module: ' . ($manifest['slug'] ?? 'unknown'));
            }
        }
        if (!in_array($manifest['entity'], ['records', 'employees'], true)) {
            throw new RuntimeException('Unsupported module entity: ' . ($manifest['slug'] ?? 'unknown'));
        }
    }
}
