<?php
$values = $recordValues ?? [];
$isEdit = ($formAction ?? '') === 'edit';
$ownModule = ($currentUser['role'] ?? '') === 'employee' && !empty($module['employee_access']) && $module['employee_access'] === 'own';
?>
<div class="page-heading">
    <div class="page-heading-main">
        <div class="eyebrow"><?= h($module['group']) ?></div>
        <h1><?= $isEdit ? 'ویرایش رکورد' : 'ثبت رکورد جدید' ?></h1>
        <p><?= h($module['description']) ?></p>
    </div>
    <div class="heading-actions"><a class="button button-secondary" href="<?= h(app_url(['page' => $module['slug']])) ?>"><?= icon('arrow') ?> بازگشت به فهرست</a></div>
</div>
<form class="card form-card" method="post" action="<?= h(app_url(['page' => $module['slug']])) ?>" enctype="multipart/form-data" data-confirm-unsaved>
    <input type="hidden" name="action" value="save_record">
    <input type="hidden" name="module" value="<?= h($module['slug']) ?>">
    <input type="hidden" name="id" value="<?= $isEdit ? (int)$editRecord['id'] : '' ?>">
    <?= csrf_field() ?>
    <section class="form-section">
        <div class="form-section-head"><span class="form-section-icon"><?= icon($module['icon']) ?></span><div><h2>اطلاعات <?= h($module['name']) ?></h2><p>فیلدهای ستاره‌دار برای ذخیره الزامی هستند.</p></div></div>
        <div class="form-grid">
        <?php foreach ($module['fields'] as $field):
            $name = (string)$field['name'];
            $type = (string)$field['type'];
            $value = (string)($values[$name] ?? ($field['default'] ?? ''));
            $required = (bool)($field['required'] ?? false);
            $employeeField = $type === 'employee';
            $isSelfEmployee = $employeeField && $ownModule && !empty($currentUser['employee_id']);
            $fullSpan = $type === 'textarea';
        ?>
            <label class="field <?= $fullSpan ? 'full-span' : '' ?>">
                <span><?= h($field['label']) ?><?= $required ? '<b class="required-star">*</b>' : '' ?></span>
                <?php if ($type === 'textarea'): ?>
                    <textarea name="<?= h($name) ?>" maxlength="8000" placeholder="<?= h($field['placeholder'] ?? '') ?>" <?= $required ? 'required' : '' ?>><?= h($value) ?></textarea>
                <?php elseif ($type === 'select'): ?>
                    <select name="<?= h($name) ?>" <?= $required ? 'required' : '' ?>>
                        <?php if (!$required): ?><option value="">انتخاب کنید</option><?php endif; ?>
                        <?php foreach (($field['options'] ?? []) as $optionValue => $optionLabel): ?>
                            <option value="<?= h($optionValue) ?>" <?= $value === (string)$optionValue ? 'selected' : '' ?>><?= h($optionLabel) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php elseif ($employeeField): ?>
                    <?php if ($isSelfEmployee):
                        $selfEmployee = null;
                        foreach ($employeeOptions as $option) { if ((int)$option['id'] === (int)$currentUser['employee_id']) { $selfEmployee = $option; break; } }
                    ?>
                        <input type="hidden" name="<?= h($name) ?>" value="<?= (int)$currentUser['employee_id'] ?>">
                        <input type="text" value="<?= h($selfEmployee ? trim($selfEmployee['first_name'].' '.$selfEmployee['last_name']).' · '.$selfEmployee['employee_code'] : 'پرونده متصل به حساب شما') ?>" disabled>
                    <?php else: ?>
                        <select name="<?= h($name) ?>" <?= $required ? 'required' : '' ?>>
                            <?php if (!$required): ?><option value="">انتخاب کارمند</option><?php endif; ?>
                            <?php foreach ($employeeOptions as $option): $employeeValue = (string)$option['id']; ?>
                                <option value="<?= h($employeeValue) ?>" <?= $value === $employeeValue ? 'selected' : '' ?>><?= h(trim($option['first_name'].' '.$option['last_name']).' · '.$option['employee_code']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                <?php elseif ($type === 'file'): ?>
                    <input type="file" name="<?= h($name) ?>" accept=".pdf,.png,.jpg,.jpeg,.doc,.docx,.xls,.xlsx,.txt">
                    <?php if ($value !== '' && $isEdit): ?><small class="file-current"><a href="<?= h(app_url(['page'=>'download','module'=>$module['slug'],'id'=>$editRecord['id'],'field'=>$name])) ?>">پیوست فعلی · دریافت فایل</a> (بارگذاری فایل تازه، جایگزین آن می‌شود)</small><?php endif; ?>
                    <small>PDF، تصویر یا سند اداری · حداکثر ۸ مگابایت</small>
                <?php else: ?>
                    <input type="<?= h($type === 'tel' ? 'tel' : $type) ?>" name="<?= h($name) ?>" value="<?= h($value) ?>" placeholder="<?= h($field['placeholder'] ?? '') ?>" <?= $required ? 'required' : '' ?> <?= $type === 'number' ? 'min="0" step="any" inputmode="decimal"' : '' ?> <?= $type === 'email' ? 'dir="ltr" autocomplete="email"' : '' ?>>
                <?php endif; ?>
                <?php if (!empty($field['help'])): ?><small><?= h($field['help']) ?></small><?php endif; ?>
            </label>
        <?php endforeach; ?>
        </div>
    </section>
    <div class="form-footer">
        <div class="form-footer-note"><span class="required-star">*</span> تکمیل این فیلد الزامی است. اطلاعات در پایگاه داده محلی SQLite ذخیره می‌شود.</div>
        <div class="form-footer-actions"><a class="button button-secondary" href="<?= h(app_url(['page' => $module['slug']])) ?>">انصراف</a><button class="button button-primary" type="submit"><?= icon('check') ?> <?= $isEdit ? 'ذخیره تغییرات' : 'ثبت اطلاعات' ?></button></div>
    </div>
</form>
