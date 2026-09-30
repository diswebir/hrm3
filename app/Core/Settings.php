<?php
declare(strict_types=1);

namespace HRM\Core;

use PDO;

final class Settings
{
    public static function all(PDO $pdo): array
    {
        $values = [];
        foreach ($pdo->query('SELECT setting_key,setting_value FROM settings') as $row) {
            $values[(string)$row['setting_key']] = (string)$row['setting_value'];
        }
        return $values;
    }

    public static function get(PDO $pdo, string $key, string $default = ''): string
    {
        $stmt = $pdo->prepare('SELECT setting_value FROM settings WHERE setting_key=:key LIMIT 1');
        $stmt->execute([':key' => $key]);
        $value = $stmt->fetchColumn();
        return $value === false ? $default : (string)$value;
    }

    public static function save(PDO $pdo, array $values): void
    {
        $allowed = ['org_name','org_phone','org_email','currency','work_week','fiscal_year_start'];
        $stmt = $pdo->prepare('INSERT INTO settings(setting_key,setting_value) VALUES(:key,:value) ON CONFLICT(setting_key) DO UPDATE SET setting_value=excluded.setting_value');
        foreach ($allowed as $key) {
            if (array_key_exists($key, $values)) {
                $value = trim((string)$values[$key]);
                if (strlen($value) > 240) {
                    $value = substr($value, 0, 240);
                }
                $stmt->execute([':key' => $key, ':value' => $value]);
            }
        }
    }
}
