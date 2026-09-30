<?php
declare(strict_types=1);

namespace HRM\Core;

use RuntimeException;

final class Security
{
    public static function token(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return '';
        }
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return (string)$_SESSION['_csrf'];
    }

    public static function verify(?string $token): void
    {
        $expected = $_SESSION['_csrf'] ?? '';
        if (!is_string($token) || !is_string($expected) || $expected === '' || !hash_equals($expected, $token)) {
            http_response_code(419);
            throw new RuntimeException('درخواست منقضی یا نامعتبر است. صفحه را تازه‌سازی و دوباره تلاش کنید.');
        }
    }

    public static function redirect(string $url): never
    {
        header('Location: ' . $url, true, 303);
        exit;
    }
}
