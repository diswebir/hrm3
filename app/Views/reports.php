<?php
use HRM\Core\ModuleRegistry;
$maxDepartment = 1;
foreach ($departmentRows as $departmentRow) { $maxDepartment = max($maxDepartment,(int)$departmentRow['total']); }
?>
<div class="page-heading"><div class="page-heading-main"><div class="eyebrow">تحلیل و پایش</div><h1>گزارش‌های مدیریتی</h1><p>خلاصه‌ای از پرونده‌ها و روندهای ثبت‌شده در ماژول‌های سازمان.</p></div><div class="heading-actions"><a class="button button-secondary" href="<?= h(app_url(['page'=>'employees'])) ?>"><?= icon('users') ?> فهرست کارکنان</a></div></div>
<div class="report-grid">
<?php foreach ($reportModules as $report): $module=$report['module']; ?>
    <article class="card report-module"><div class="report-module-top"><span class="module-icon-large"><?= icon($module['icon']) ?></span><strong><?= h($module['name']) ?></strong></div><div class="report-total"><?= number_format($report['count']) ?></div><div class="report-statuses"><?php foreach (array_slice($report['statuses'],0,4) as $item): $statusField=null; foreach ($module['fields'] as $f){if($f['name']==='status'){$statusField=$f;break;}} $label=$statusField['options'][$item['status']]??$item['status']; ?><span><?= h($label) ?>: <?= number_format((int)$item['total']) ?></span><?php endforeach; ?></div></article>
<?php endforeach; ?>
</div>
<div class="card">
    <div class="card-head"><div><h2 class="card-title">ترکیب نیروی انسانی بر اساس واحد</h2><p class="card-subtitle">تعداد کل پرونده‌های کارکنان در هر واحد سازمانی</p></div><a class="text-link" href="<?= h(app_url(['page'=>'employees'])) ?>">مدیریت پرونده‌ها <?= icon('arrow') ?></a></div>
    <div class="card-body">
        <?php if (!$departmentRows): ?><div class="empty-state"><span class="empty-illustration"><?= icon('building') ?></span><strong>داده‌ای برای گزارش وجود ندارد</strong><p>ابتدا کارکنان و واحدهای سازمانی را ثبت کنید.</p></div><?php else: foreach ($departmentRows as $departmentRow): $total=(int)$departmentRow['total']; $width=max(3,round($total/$maxDepartment*100)); ?>
            <div class="report-dept-row"><span class="report-dept-name"><?= h($departmentRow['department']) ?></span><span class="report-bar"><i style="width:<?= $width ?>%"></i></span><strong class="report-dept-value"><?= number_format($total) ?></strong></div>
        <?php endforeach; endif; ?>
    </div>
</div>
<div class="security-note" style="margin-top:15px"><?= icon('shield') ?><span>گزارش‌ها بر اساس داده‌های موجود در سامانه ساخته می‌شوند. برای تحلیل بیرونی می‌توانید از خروجی CSV هر ماژول استفاده کنید.</span></div>
