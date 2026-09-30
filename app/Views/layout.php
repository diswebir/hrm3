<?php
use HRM\Core\Access;
use HRM\Core\ModuleRegistry;
$flashes = take_flashes();
$currentUser = $currentUser ?? [];
$orgName = $orgName ?? 'هم‌آوا HRM';
$activePage = $activePage ?? '';
$moduleGroups = ModuleRegistry::groups();
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#101a34">
    <title><?= h($pageTitle ?? 'سامانه') ?> · <?= h($orgName) ?></title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="app-body">
<div class="app-shell">
    <div class="sidebar-overlay" data-sidebar-close></div>
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="<?= h(app_url(['page' => 'dashboard'])) ?>">
            <span class="brand-mark">هـ</span>
            <span class="brand-copy"><strong>هم‌آوا <em>HRM</em></strong><small>سامانه سرمایه انسانی</small></span>
            <button class="sidebar-close" type="button" data-sidebar-close aria-label="بستن منو"><?= icon('close') ?></button>
        </a>
        <div class="sidebar-scroll">
            <div class="nav-caption">فضای کاری</div>
            <nav class="main-nav" aria-label="منوی اصلی">
                <a class="nav-link <?= $activePage === 'dashboard' ? 'active' : '' ?>" href="<?= h(app_url(['page' => 'dashboard'])) ?>"><?= icon('grid') ?><span>نمای کلی</span><span class="nav-indicator"></span></a>
                <?php foreach ($moduleGroups as $group => $modules): ?>
                    <?php $visible = array_filter($modules, static fn(array $module): bool => Access::canViewModule($module, $currentUser)); if (!$visible) continue; ?>
                    <div class="nav-caption"><?= h($group) ?></div>
                    <?php foreach ($visible as $navModule): ?>
                        <a class="nav-link <?= $activePage === $navModule['slug'] ? 'active' : '' ?>" href="<?= h(app_url(['page' => $navModule['slug']])) ?>">
                            <?= icon($navModule['icon']) ?><span><?= h($navModule['name']) ?></span><span class="nav-indicator"></span>
                        </a>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </nav>
            <div class="nav-caption nav-caption-bottom">مدیریت سامانه</div>
            <nav class="main-nav" aria-label="مدیریت سامانه">
                <?php if (in_array($currentUser['role'] ?? '', ['admin','hr'], true)): ?>
                    <a class="nav-link <?= $activePage === 'reports' ? 'active' : '' ?>" href="<?= h(app_url(['page' => 'reports'])) ?>"><?= icon('chart') ?><span>گزارش‌ها</span><span class="nav-indicator"></span></a>
                    <a class="nav-link <?= $activePage === 'audit' ? 'active' : '' ?>" href="<?= h(app_url(['page' => 'audit'])) ?>"><?= icon('shield') ?><span>گزارش رویدادها</span><span class="nav-indicator"></span></a>
                <?php elseif (($currentUser['role'] ?? '') === 'manager'): ?>
                    <a class="nav-link <?= $activePage === 'reports' ? 'active' : '' ?>" href="<?= h(app_url(['page' => 'reports'])) ?>"><?= icon('chart') ?><span>گزارش‌ها</span><span class="nav-indicator"></span></a>
                <?php endif; ?>
                <?php if (Access::mayManageAdminArea($currentUser)): ?>
                    <a class="nav-link <?= $activePage === 'users' ? 'active' : '' ?>" href="<?= h(app_url(['page' => 'users'])) ?>"><?= icon('users') ?><span>کاربران و دسترسی‌ها</span><span class="nav-indicator"></span></a>
                    <a class="nav-link <?= $activePage === 'settings' ? 'active' : '' ?>" href="<?= h(app_url(['page' => 'settings'])) ?>"><?= icon('settings') ?><span>تنظیمات سامانه</span><span class="nav-indicator"></span></a>
                <?php endif; ?>
                <a class="nav-link <?= $activePage === 'profile' ? 'active' : '' ?>" href="<?= h(app_url(['page' => 'profile'])) ?>"><?= icon('users') ?><span>حساب کاربری من</span><span class="nav-indicator"></span></a>
                <a class="nav-link <?= $activePage === 'about' ? 'active' : '' ?>" href="<?= h(app_url(['page' => 'about'])) ?>"><?= icon('spark') ?><span>درباره سامانه</span><span class="nav-indicator"></span></a>
            </nav>
        </div>
        <div class="sidebar-bottom">
            <div class="sidebar-version"><span class="status-pulse"></span><span>سامانه فعال</span><small>v<?= h(HRM_VERSION) ?></small></div>
            <div class="sidebar-user">
                <span class="avatar avatar-small"><?= h(initials((string)($currentUser['name'] ?? 'HR'))) ?></span>
                <div class="sidebar-user-meta"><strong><?= h($currentUser['name'] ?? '') ?></strong><small><?= h(role_label((string)($currentUser['role'] ?? ''))) ?></small></div>
                <form method="post" class="logout-form" action="<?= h(app_url(['page' => 'dashboard'])) ?>" title="خروج">
                    <input type="hidden" name="action" value="logout"><?= csrf_field() ?><button class="icon-button logout-button" type="submit" aria-label="خروج"><?= icon('logout') ?></button>
                </form>
            </div>
        </div>
    </aside>

    <main class="main-area">
        <header class="topbar">
            <div class="topbar-right">
                <button class="icon-button menu-toggle" type="button" data-sidebar-toggle aria-label="بازکردن منو"><?= icon('grid') ?></button>
                <div class="breadcrumb"><span>هم‌آوا</span><i>/</i><strong><?= h($pageTitle ?? '') ?></strong></div>
            </div>
            <div class="topbar-left">
                <form class="global-search" action="index.php" method="get" role="search">
                    <input type="hidden" name="page" value="search">
                    <span class="search-icon"><?= icon('search') ?></span>
                    <input name="q" type="search" placeholder="جست‌وجوی کارکنان و پرونده‌ها…" value="<?= h(($activePage ?? '') === 'search' ? ($query ?? '') : '') ?>" aria-label="جست‌وجو">
                    <kbd>/</kbd>
                </form>
                <div class="topbar-date"><?= h(date('Y/m/d')) ?></div>
                <span class="avatar avatar-top" title="<?= h($currentUser['name'] ?? '') ?>"><?= h(initials((string)($currentUser['name'] ?? 'HR'))) ?></span>
            </div>
        </header>
        <section class="page-content">
            <?php if ($flashes): ?><div class="toast-stack app-toasts"><?php foreach ($flashes as $item): ?><div class="toast <?= h($item['type']) ?>"><span><?= $item['type'] === 'error' ? '!' : '✓' ?></span><?= h($item['message']) ?><button type="button" data-dismiss aria-label="بستن">×</button></div><?php endforeach; ?></div><?php endif; ?>
            <?= $content ?>
        </section>
        <footer class="app-footer"><span>© <?= date('Y') ?> <?= h($orgName) ?></span><span>هم‌آوا HRM · نسخه <?= h(HRM_VERSION) ?></span></footer>
    </main>
</div>
<script src="assets/js/app.js" defer></script>
</body>
</html>
