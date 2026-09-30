<?php
$flashes = take_flashes();
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#101a34">
    <title><?= h($pageTitle ?? 'ورود') ?> · هم‌آوا HRM</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="auth-body">
<?php if ($flashes): ?><div class="toast-stack"><?php foreach ($flashes as $item): ?><div class="toast <?= h($item['type']) ?>"><?= h($item['message']) ?></div><?php endforeach; ?></div><?php endif; ?>
<?= $content ?>
<script src="assets/js/app.js" defer></script>
</body>
</html>
