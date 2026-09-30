<?php
declare(strict_types=1);

namespace HRM\Core;

use PDO;
use RuntimeException;

final class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }
        $config = \hrm_config();
        $path = (string)($config['database'] ?? (HRM_STORAGE . '/hrm.sqlite'));
        self::$pdo = self::connectPath($path);
        return self::$pdo;
    }

    public static function connectPath(string $path): PDO
    {
        if (!extension_loaded('pdo_sqlite')) {
            throw new RuntimeException('افزونه PDO_SQLite روی این سرور فعال نیست.');
        }
        $directory = dirname($path);
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new RuntimeException('پوشه پایگاه داده قابل ایجاد نیست.');
        }
        $pdo = new PDO('sqlite:' . $path, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA busy_timeout = 5000');
        // DELETE mode is the most compatible choice for shared hosting filesystems.
        $pdo->exec('PRAGMA journal_mode = DELETE');
        @chmod($path, 0660);
        return $pdo;
    }
}
