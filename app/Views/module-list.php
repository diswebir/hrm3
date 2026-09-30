<?php
use HRM\Core\ModuleRegistry;
$moduleCount = (int)($listing['total'] ?? 0);
$statusOptions = $statusField['options'] ?? [];
?>
<div class="page-heading">
    <div class="page-heading-main">
        <div class="eyebrow"><?= h($module['group']) ?></div>
        <h1><?= h($module['name']) ?></h1>
        <p><?= h($module['description']) ?></p>
    </div>
    <div class="heading-actions">
        <?php if (in_array($currentUser['role'] ?? '', ['admin','hr','manager'], true)): ?>
            <a class="button button-secondary" href="<?= h(app_url(['page' => 'export', 'module' => $module['slug']])) ?>"><?= icon('download') ?> دریافت CSV</a>
        <?php endif; ?>
        <?php if ($canManage): ?>
            <a class="button button-primary" href="<?= h(app_url(['page' => $module['slug'], 'action' => 'new'])) ?>"><?= icon('plus') ?> ثبت <?= h($module['name']) ?></a>
        <?php endif; ?>
    </div>
</div>
<div class="module-overview">
    <p><?= h($module['description']) ?> <span class="result-count">· <?= number_format($moduleCount) ?> پرونده</span></p>
    <div class="module-overview-meta"><span class="module-icon-large"><?= icon($module['icon']) ?></span></div>
</div>
<div class="card table-card">
    <form class="filter-bar" method="get" action="index.php">
        <input type="hidden" name="page" value="<?= h($module['slug']) ?>">
        <div class="filter-left">
            <label class="search-control">
                <?= icon('search') ?>
                <input type="search" name="q" value="<?= h($query) ?>" placeholder="جست‌وجو در <?= h($module['name']) ?>…" aria-label="جست‌وجو">
            </label>
            <?php if ($statusOptions): ?>
                <select class="filter-select" name="status" aria-label="فیلتر وضعیت">
                    <option value="">همه وضعیت‌ها</option>
                    <?php foreach ($statusOptions as $value => $label): ?>
                        <option value="<?= h($value) ?>" <?= $statusFilter === (string)$value ? 'selected' : '' ?>><?= h($label) ?></option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
            <button class="button button-secondary button-small" type="submit">اعمال فیلتر</button>
        </div>
        <div class="filter-right"><span class="table-count"><strong><?= number_format($moduleCount) ?></strong> رکورد</span></div>
    </form>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr>
                <th><?= h($module['entity'] === 'employees' ? 'نام کارمند' : ($titleField['label'] ?? 'عنوان')) ?></th>
                <?php foreach ($module['columns'] as $column):
                    $columnField = null;
                    foreach ($module['fields'] as $field) { if ($field['name'] === $column) { $columnField = $field; break; } }
                    if (!$columnField || $column === ($module['primary'] ?? 'title')) continue;
                ?>
                    <th><?= h($columnField['label']) ?></th>
                <?php endforeach; ?>
                <th>آخرین تغییر</th>
                <?php if ($canManage): ?><th>اقدام</th><?php endif; ?>
            </tr></thead>
            <tbody>
            <?php if (!$listing['rows']): ?>
                <tr><td colspan="<?= 3 + count($module['columns']) + ($canManage ? 1 : 0) ?>">
                    <div class="empty-state"><span class="empty-illustration"><?= icon($module['icon']) ?></span><strong>هنوز رکوردی ثبت نشده است</strong><p>با ثبت اولین مورد، سوابق این بخش در همین‌جا نمایش داده می‌شوند.</p></div>
                </td></tr>
            <?php else: foreach ($listing['rows'] as $row):
                $rowData = $module['entity'] === 'employees' ? $row : ($row['data_array'] ?? []);
                $rowTitle = $module['entity'] === 'employees'
                    ? trim((string)$row['first_name'] . ' ' . (string)$row['last_name'])
                    : (string)($rowData[$module['primary'] ?? 'title'] ?? $row['title'] ?? '—');
                $rowId = (int)$row['id'];
                $rowStatus = (string)($row['status'] ?? '');
                $statusLabel = $statusOptions[$rowStatus] ?? $rowStatus;
                $rowUrl = app_url(['page' => $module['slug'], 'action' => 'edit', 'id' => $rowId]);
            ?>
                <tr>
                    <td class="table-primary">
                        <div class="employee-cell">
                            <span class="employee-mini-avatar"><?= h(initials($rowTitle)) ?></span>
                            <span class="employee-cell-copy"><strong><?= h($rowTitle) ?></strong><?php if ($module['entity'] === 'employees'): ?><small><?= h($row['employee_code']) ?></small><?php else: ?><small>شناسه #<?= number_format($rowId) ?></small><?php endif; ?></span>
                        </div>
                    </td>
                    <?php foreach ($module['columns'] as $column):
                        $columnField = null;
                        foreach ($module['fields'] as $field) { if ($field['name'] === $column) { $columnField = $field; break; } }
                        if (!$columnField || $column === ($module['primary'] ?? 'title')) continue;
                        if ($column === 'status'):
                    ?>
                        <td><span class="status-badge status-<?= h(str_replace('_','-', $rowStatus)) ?>"><?= h($statusLabel !== '' ? $statusLabel : 'نامشخص') ?></span></td>
                    <?php elseif ($columnField['type'] === 'file' && !empty($rowData[$column])): ?>
                        <td><a class="text-link" href="<?= h(app_url(['page' => 'download','module' => $module['slug'],'id' => $rowId,'field' => $column])) ?>"><?= icon('download') ?> دریافت فایل</a></td>
                    <?php else: ?>
                        <td><?= h(hrm_module_value(db(), $module, $row, $columnField)) ?></td>
                    <?php endif; endforeach; ?>
                    <td><span class="small-muted"><?= h(date_fmt((string)($row['updated_at'] ?? $row['created_at'] ?? ''))) ?></span></td>
                    <?php if ($canManage): $canEditRow = ($currentUser['role'] ?? '') !== 'employee' || in_array($rowStatus, ['pending','requested','draft'], true); ?>
                    <td><?php if ($canEditRow): ?><div class="row-actions">
                        <?php if (in_array($currentUser['role'] ?? '', ['admin','hr','manager'], true) && $module['entity'] === 'records' && in_array($rowStatus, ['pending','requested','submitted'], true) && $statusField):
                            $approveStatus = $rowStatus === 'requested' ? 'approved' : 'approved';
                            $rejectStatus = 'rejected';
                            if (isset($statusOptions[$approveStatus])): ?>
                                <form method="post" action="<?= h(app_url(['page' => $module['slug']])) ?>" title="تأیید">
                                    <input type="hidden" name="action" value="update_status"><input type="hidden" name="module" value="<?= h($module['slug']) ?>"><input type="hidden" name="id" value="<?= $rowId ?>"><input type="hidden" name="status" value="<?= h($approveStatus) ?>"><?= csrf_field() ?><button class="row-action" type="submit" aria-label="تأیید"><?= icon('check') ?></button>
                                </form>
                            <?php endif; if (isset($statusOptions[$rejectStatus])): ?>
                                <form method="post" action="<?= h(app_url(['page' => $module['slug']])) ?>" title="رد">
                                    <input type="hidden" name="action" value="update_status"><input type="hidden" name="module" value="<?= h($module['slug']) ?>"><input type="hidden" name="id" value="<?= $rowId ?>"><input type="hidden" name="status" value="<?= h($rejectStatus) ?>"><?= csrf_field() ?><button class="row-action danger" type="submit" aria-label="رد"><?= icon('close') ?></button>
                                </form>
                            <?php endif; endif; ?>
                        <a class="row-action" href="<?= h($rowUrl) ?>" title="ویرایش" aria-label="ویرایش"><?= icon('edit') ?></a>
                        <a class="row-action danger" href="<?= h(app_url(['page' => $module['slug'],'action' => 'delete','id' => $rowId])) ?>" title="حذف" aria-label="حذف"><?= icon('trash') ?></a>
                    </div><?php else: ?><span class="small-muted">بررسی‌شده</span><?php endif; ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($listing['pages'] > 1): ?>
        <div class="pagination">
            <span>صفحه <?= number_format($listing['page']) ?> از <?= number_format($listing['pages']) ?></span>
            <div class="pagination-controls">
                <a class="page-button" href="<?= h(app_url(['page' => $module['slug'],'q' => $query,'status' => $statusFilter,'p' => max(1,$listing['page']-1)])) ?>" aria-label="صفحه قبل">‹</a>
                <?php for ($p=max(1,$listing['page']-2); $p<=min($listing['pages'],$listing['page']+2); $p++): ?>
                    <a class="page-button <?= $p===$listing['page'] ? 'active' : '' ?>" href="<?= h(app_url(['page' => $module['slug'],'q' => $query,'status' => $statusFilter,'p' => $p])) ?>"><?= number_format($p) ?></a>
                <?php endfor; ?>
                <a class="page-button" href="<?= h(app_url(['page' => $module['slug'],'q' => $query,'status' => $statusFilter,'p' => min($listing['pages'],$listing['page']+1)])) ?>" aria-label="صفحه بعد">›</a>
            </div>
        </div>
    <?php endif; ?>
</div>
