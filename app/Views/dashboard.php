<?php
use HRM\Core\Access;
use HRM\Core\ModuleRegistry;
$monthlyHires = $monthlyHires ?? [];
$chartValues = array_map(static fn(array $item): int => (int)$item['count'], $monthlyHires);
$maxChart = max(1, ...$chartValues);
$points = [];
$chartWidth = 620;
$chartHeight = 155;
$chartTop = 14;
$chartBottom = 127;
$countPoints = max(1, count($monthlyHires));
foreach ($monthlyHires as $index => $item) {
    $x = 45 + ($index * (520 / max(1, $countPoints - 1)));
    $y = $chartBottom - ((int)$item['count'] / $maxChart) * 92;
    $points[] = round($x, 1) . ',' . round($y, 1);
}
$linePath = $points ? 'M ' . implode(' L ', $points) : '';
$areaPath = $points ? $linePath . ' L ' . end($points) . ',' . $chartBottom . ' L ' . explode(',', $points[0])[0] . ',' . $chartBottom . ' Z' : '';
$employeeName = (string)($currentUser['name'] ?? 'همکار عزیز');
$todayData = $todayRecord['data_array'] ?? [];
$hasCheckIn = !empty($todayData['check_in']);
$hasCheckOut = !empty($todayData['check_out']);
$currency = $settings['currency'] ?? 'تومان';
?>
<section class="dashboard-welcome">
    <div class="welcome-copy">
        <div class="eyebrow">فضای کاری شما · <?= h(date('Y/m/d')) ?></div>
        <h1>سلام <?= h($employeeName) ?>، خوش آمدید</h1>
        <p>نمای کلی امروز سازمان را ببینید و کارهای منابع انسانی را از همین‌جا پیگیری کنید.</p>
    </div>
    <div class="welcome-actions">
        <?php if (Access::canManageModule(ModuleRegistry::get('employees') ?? [], $currentUser)): ?>
            <a class="button button-white" href="<?= h(app_url(['page' => 'employees','action' => 'new'])) ?>"><?= icon('plus') ?> افزودن همکار</a>
        <?php endif; ?>
        <?php if (in_array($currentUser['role'] ?? '', ['admin','hr','manager'], true)): ?><a class="button button-outline-light" href="<?= h(app_url(['page' => 'reports'])) ?>"><?= icon('chart') ?> گزارش‌ها</a><?php endif; ?>
    </div>
    <div class="welcome-decor"><?= icon('users') ?></div>
</section>

<div class="stats-grid">
    <article class="card stat-card">
        <span class="stat-icon tone-teal"><?= icon('users') ?></span><div class="stat-info"><span class="stat-label">همکاران فعال</span><strong class="stat-value"><?= number_format($activeEmployees) ?></strong><span class="stat-note"><strong>پرونده فعال</strong> در سازمان</span></div><span class="stat-corner"></span>
    </article>
    <article class="card stat-card">
        <span class="stat-icon tone-orange"><?= icon('calendar') ?></span><div class="stat-info"><span class="stat-label">درخواست مرخصی باز</span><strong class="stat-value"><?= number_format($pendingLeaves) ?></strong><span class="stat-note">نیازمند بررسی و پیگیری</span></div><span class="stat-corner"></span>
    </article>
    <article class="card stat-card">
        <span class="stat-icon tone-blue"><?= icon('clock') ?></span><div class="stat-info"><span class="stat-label">ثبت حضور امروز</span><strong class="stat-value"><?= number_format($todayAttendanceCount) ?></strong><span class="stat-note">مورد حضور ثبت‌شده</span></div><span class="stat-corner"></span>
    </article>
    <article class="card stat-card">
        <span class="stat-icon tone-red"><?= icon('check') ?></span><div class="stat-info"><span class="stat-label">وظایف باز</span><strong class="stat-value"><?= number_format($openTasks) ?></strong><span class="stat-note">در انتظار تکمیل</span></div><span class="stat-corner"></span>
    </article>
</div>

<div class="dashboard-grid">
    <div class="dashboard-column">
        <section class="card chart-card">
            <div class="card-head">
                <div><h2 class="card-title">روند ورود همکاران</h2><p class="card-subtitle">تعداد استخدام‌ها در شش ماه اخیر</p></div>
                <div class="chart-summary"><div><strong><?= number_format(array_sum($chartValues)) ?></strong><br><span>در این بازه</span></div><span class="chart-legend"><i></i>استخدام جدید</span></div>
            </div>
            <div class="chart-wrapper">
                <svg class="mini-chart" viewBox="0 0 620 155" role="img" aria-label="نمودار استخدام شش ماه اخیر">
                    <defs><linearGradient id="chartFill" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#23b7a3" stop-opacity=".23"/><stop offset="1" stop-color="#23b7a3" stop-opacity="0"/></linearGradient></defs>
                    <?php foreach ([26,58,90,122] as $y): ?><line class="chart-grid" x1="38" y1="<?= $y ?>" x2="584" y2="<?= $y ?>"/><?php endforeach; ?>
                    <?php if ($areaPath): ?><path class="chart-area" d="<?= h($areaPath) ?>"/><path class="chart-line" d="<?= h($linePath) ?>"/><?php endif; ?>
                    <?php foreach ($monthlyHires as $index => $item):
                        $x = 45 + ($index * (520 / max(1, $countPoints - 1)));
                        $y = $chartBottom - ((int)$item['count'] / $maxChart) * 92;
                    ?><circle class="chart-dot" cx="<?= round($x,1) ?>" cy="<?= round($y,1) ?>" r="4"/><text class="chart-label" x="<?= round($x,1) ?>" y="150" text-anchor="middle"><?= h(substr($item['label'], 5, 2)) ?></text><?php endforeach; ?>
                </svg>
            </div>
        </section>
        <section class="card table-card">
            <div class="card-head"><div><h2 class="card-title">همکاران اخیر</h2><p class="card-subtitle">آخرین پرونده‌های اضافه‌شده به سامانه</p></div><a class="text-link" href="<?= h(app_url(['page' => 'employees'])) ?>">مشاهده همه <?= icon('arrow') ?></a></div>
            <?php if (!$recentEmployees): ?><div class="empty-state"><span class="empty-illustration"><?= icon('users') ?></span><strong>هنوز پرونده‌ای ثبت نشده است</strong><p>پس از ثبت کارکنان، خلاصه آن‌ها در این بخش نمایش داده می‌شود.</p></div><?php else: ?>
            <div class="table-wrap"><table class="data-table"><thead><tr><th>نام همکار</th><th>واحد</th><th>عنوان شغلی</th><th>وضعیت</th></tr></thead><tbody>
                <?php foreach ($recentEmployees as $employee): ?>
                    <tr><td><div class="employee-cell"><span class="employee-mini-avatar"><?= h(initials($employee['first_name'].' '.$employee['last_name'])) ?></span><span class="employee-cell-copy"><strong><?= h($employee['first_name'].' '.$employee['last_name']) ?></strong><small><?= h($employee['employee_code']) ?></small></span></div></td><td><?= h($employee['department'] ?: '—') ?></td><td><?= h($employee['position'] ?: '—') ?></td><td><span class="status-badge status-<?= h(str_replace('_','-',$employee['status'])) ?>"><?= h(['active'=>'فعال','on_leave'=>'مرخصی','inactive'=>'غیرفعال'][$employee['status']] ?? $employee['status']) ?></span></td></tr>
                <?php endforeach; ?>
            </tbody></table></div><?php endif; ?>
        </section>
    </div>
    <div class="dashboard-column side-column">
        <?php if (!empty($currentUser['employee_id'])): ?>
        <section class="card checkin-card">
            <div class="checkin-head"><span class="checkin-title">تردد امروز من</span><span class="checkin-clock"><?= h(date('Y/m/d')) ?></span></div>
            <?php if (!$hasCheckIn): ?><div class="checkin-time">--:--</div><p class="checkin-note">برای آغاز روز کاری، ساعت ورود خود را ثبت کنید.</p>
            <form method="post" action="<?= h(app_url(['page'=>'dashboard'])) ?>" class="checkin-actions"><input type="hidden" name="action" value="attendance"><input type="hidden" name="kind" value="checkin"><?= csrf_field() ?><button class="button button-primary" type="submit"><?= icon('check') ?> ثبت ورود</button></form>
            <?php elseif (!$hasCheckOut): ?><div class="checkin-time"><?= h($todayData['check_in']) ?></div><p class="checkin-note">ورود ثبت شد؛ در پایان روز خروج را ثبت کنید.</p>
            <div class="checkin-actions"><form method="post" action="<?= h(app_url(['page'=>'dashboard'])) ?>" class="checkin-actions"><input type="hidden" name="action" value="attendance"><input type="hidden" name="kind" value="checkout"><?= csrf_field() ?><button class="button button-soft" type="submit">ثبت ساعت خروج</button></form></div>
            <?php else: ?><div class="checkin-time"><?= h($todayData['check_in']) ?> <span class="muted">—</span> <?= h($todayData['check_out']) ?></div><p class="checkin-note">تردد امروز شما کامل ثبت شده است.</p><?php endif; ?>
            <div class="attendance-breakdown"><div><strong><?= number_format($todayStatusCounts['present']) ?></strong><small>حضوری</small></div><div><strong><?= number_format($todayStatusCounts['remote']) ?></strong><small>دورکار</small></div><div><strong><?= number_format($todayStatusCounts['late']) ?></strong><small>تأخیر</small></div><div><strong><?= number_format($todayStatusCounts['absent']) ?></strong><small>غیبت</small></div></div>
        </section>
        <?php elseif (($currentUser['role'] ?? '') === 'employee'): ?>
        <section class="card checkin-card"><div class="checkin-head"><span class="checkin-title">ثبت تردد روزانه</span><?= icon('clock') ?></div><p class="checkin-note">برای استفاده از ثبت ورود و خروج، مدیر سامانه باید حساب شما را به پرونده کارمندی متصل کند.</p><a class="text-link" href="<?= h(app_url(['page'=>'about'])) ?>">راهنمای سامانه <?= icon('arrow') ?></a></section>
        <?php else: ?>
        <section class="card">
            <div class="card-head"><div><h2 class="card-title">دسترسی سریع</h2><p class="card-subtitle">کارهای پرتکرار در دسترس شما</p></div><?= icon('spark','small-muted') ?></div>
            <div class="quick-actions">
                <?php foreach (['employees','leave','attendance','tasks'] as $quickSlug): $quickModule = ModuleRegistry::get($quickSlug); if (!$quickModule || !Access::canViewModule($quickModule,$currentUser)) continue; $quickCan = Access::canManageModule($quickModule,$currentUser); ?>
                    <a class="quick-action" href="<?= h(app_url(['page'=>$quickSlug] + ($quickCan ? ['action'=>'new'] : []))) ?>"><span class="quick-action-icon"><?= icon($quickModule['icon']) ?></span><span><?= $quickCan ? 'ثبت ' : 'مشاهده ' ?><?= h($quickModule['name']) ?></span></a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
        <?php if ($payrollTotal !== null): ?><section class="card stat-card"><span class="stat-icon tone-blue"><?= icon('wallet') ?></span><div class="stat-info"><span class="stat-label">حقوق پرداخت‌شده ثبت‌شده</span><strong class="stat-value" style="font-size:17px"><?= h(money_fmt($payrollTotal,$currency)) ?></strong><span class="stat-note">جمع رکوردهای پرداخت‌شده</span></div></section><?php endif; ?>
        <section class="card">
            <div class="card-head"><div><h2 class="card-title">رویدادهای اخیر</h2><p class="card-subtitle">آخرین تغییرات سامانه</p></div><?php if (in_array($currentUser['role'] ?? '', ['admin','hr'], true)): ?><a class="text-link" href="<?= h(app_url(['page'=>'audit'])) ?>">همه <?= icon('arrow') ?></a><?php endif; ?></div>
            <div class="card-body">
                <?php if (!$recentAudit): ?><div class="empty-state" style="padding:16px 0"><strong>هنوز رویدادی ثبت نشده است</strong><p>فعالیت‌های مهم در این بخش دیده می‌شوند.</p></div><?php else: ?><div class="recent-audit">
                    <?php foreach ($recentAudit as $audit): ?><div class="audit-item"><span class="audit-dot"><?= icon('file') ?></span><div class="audit-copy"><strong><?= h($audit['action']) ?><?= $audit['module'] ? ' · '.h($audit['module']) : '' ?></strong><small><?= h($audit['user_name'] ?? 'سامانه') ?><?= $audit['details'] ? ' — '.h($audit['details']) : '' ?></small></div><time class="audit-time"><?= h(date('H:i', strtotime($audit['created_at']))) ?></time></div><?php endforeach; ?>
                </div><?php endif; ?>
            </div>
        </section>
    </div>
</div>
