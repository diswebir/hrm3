<?php
declare(strict_types=1);

namespace HRM\Core;

use PDO;

final class Auth
{
    private static bool $loaded = false;
    private static ?array $user = null;

    public static function current(PDO $pdo): ?array
    {
        if (self::$loaded) {
            return self::$user;
        }
        self::$loaded = true;
        $id = (int)($_SESSION['user_id'] ?? 0);
        if ($id < 1) {
            return null;
        }
        $stmt = $pdo->prepare('SELECT id,name,email,role,active,employee_id,last_login_at FROM users WHERE id=:id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        if (!$user || (int)$user['active'] !== 1) {
            unset($_SESSION['user_id']);
            return null;
        }
        self::$user = $user;
        return self::$user;
    }

    public static function attempt(PDO $pdo, string $email, string $password): bool
    {
        $lockedUntil = (int)($_SESSION['_login_locked_until'] ?? 0);
        if ($lockedUntil > time()) {
            return false;
        }
        $stmt = $pdo->prepare('SELECT id,name,email,password_hash,role,active,employee_id FROM users WHERE email=:email COLLATE NOCASE LIMIT 1');
        $stmt->execute([':email' => trim($email)]);
        $user = $stmt->fetch();
        if (!$user || (int)$user['active'] !== 1 || !password_verify($password, (string)$user['password_hash'])) {
            $attempts = (int)($_SESSION['_login_attempts'] ?? 0) + 1;
            $_SESSION['_login_attempts'] = $attempts;
            if ($attempts >= 6) {
                $_SESSION['_login_locked_until'] = time() + 600;
                $_SESSION['_login_attempts'] = 0;
            }
            return false;
        }
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        unset($_SESSION['_login_attempts'], $_SESSION['_login_locked_until']);
        $pdo->prepare('UPDATE users SET last_login_at=:now,updated_at=:now WHERE id=:id')->execute([
            ':now' => date('Y-m-d H:i:s'), ':id' => (int)$user['id'],
        ]);
        self::$loaded = false;
        self::$user = null;
        return true;
    }

    public static function logout(): void
    {
        unset($_SESSION['user_id']);
        self::$loaded = true;
        self::$user = null;
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }
}
