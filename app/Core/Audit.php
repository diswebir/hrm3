<?php
declare(strict_types=1);

namespace HRM\Core;

use PDO;

final class Audit
{
    public static function log(PDO $pdo, ?int $userId, string $action, string $module = '', ?int $recordId = null, string $details = ''): void
    {
        $stmt = $pdo->prepare('INSERT INTO audit_logs(user_id,action,module,record_id,details,ip_address,created_at) VALUES(:user,:action,:module,:record,:details,:ip,:created)');
        $stmt->execute([
            ':user' => $userId,
            ':action' => \utf8_substr($action, 0, 80),
            ':module' => \utf8_substr($module, 0, 60),
            ':record' => $recordId,
            ':details' => \utf8_substr($details, 0, 500),
            ':ip' => \utf8_substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 48),
            ':created' => date('Y-m-d H:i:s'),
        ]);
    }
}
