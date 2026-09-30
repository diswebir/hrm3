<?php
$roleOptions=['admin'=>'مدیر سامانه','hr'=>'منابع انسانی','manager'=>'مدیر واحد','employee'=>'کارمند'];
?>
<div class="page-heading"><div class="page-heading-main"><div class="eyebrow">مدیریت دسترسی</div><h1>کاربران و دسترسی‌ها</h1><p>حساب ورود بسازید، نقش‌ها را کنترل کنید و حساب را به پرونده کارمند متصل کنید.</p></div></div>
<div class="users-layout">
    <section class="card user-form-card"><h2>ایجاد حساب کاربری</h2><p>گذرواژه اولیه را به‌صورت امن در اختیار کاربر قرار دهید.</p>
        <form class="form-stack" method="post" action="<?= h(app_url(['page'=>'users'])) ?>">
            <input type="hidden" name="action" value="create_user"><?= csrf_field() ?>
            <label class="field"><span>نام نمایشی</span><input name="name" required maxlength="100" placeholder="نام و نام خانوادگی"></label>
            <label class="field"><span>ایمیل ورود</span><input type="email" name="email" required maxlength="190" dir="ltr" placeholder="name@company.com"></label>
            <label class="field"><span>گذرواژه اولیه</span><input type="password" name="password" required minlength="10" autocomplete="new-password"><small>حداقل ۱۰ نویسه؛ کاربر پس از ورود از بخش «حساب کاربری من» می‌تواند آن را تغییر دهد.</small></label>
            <label class="field"><span>نقش کاربر</span><select name="role" required><?php foreach($roleOptions as $role=>$label): ?><option value="<?= h($role) ?>"><?= h($label) ?></option><?php endforeach; ?></select></label>
            <label class="field"><span>پرونده کارمند مرتبط</span><select name="employee_id"><option value="0">بدون اتصال</option><?php foreach($employees as $employee): ?><option value="<?= (int)$employee['id'] ?>"><?= h(trim($employee['first_name'].' '.$employee['last_name']).' · '.$employee['employee_code']) ?></option><?php endforeach; ?></select><small>برای سلف‌سرویس و ثبت تردد، اتصال کارمند ضروری است.</small></label>
            <button class="button button-primary button-wide" type="submit"><?= icon('plus') ?> ایجاد حساب</button>
        </form>
    </section>
    <section class="card users-table-card"><div class="card-head"><div><h2 class="card-title">حساب‌های سامانه</h2><p class="card-subtitle"><?= number_format(count($users)) ?> حساب ثبت‌شده</p></div><?= icon('users','small-muted') ?></div><div class="table-wrap"><table class="data-table"><thead><tr><th>کاربر</th><th>نقش</th><th>پرونده کارمند</th><th>وضعیت</th><th>آخرین ورود</th><th>مدیریت</th></tr></thead><tbody>
    <?php foreach($users as $account): ?>
        <tr><td><div class="user-person"><span class="avatar"><?= h(initials($account['name'])) ?></span><div><strong><?= h($account['name']) ?></strong><small><?= h($account['email']) ?></small></div></div></td><td><span class="role-chip role-<?= h($account['role']) ?>"><?= h($roleOptions[$account['role']]??$account['role']) ?></span></td><td><?= $account['employee_id'] ? h(trim(($account['first_name']??'').' '.($account['last_name']??'')).' · '.($account['employee_code']??'')) : '<span class="small-muted">متصل نیست</span>' ?></td><td><span class="status-badge <?= (int)$account['active']===1?'status-active':'status-inactive' ?>"><?= (int)$account['active']===1?'فعال':'غیرفعال' ?></span></td><td><?= h($account['last_login_at'] ? date_fmt($account['last_login_at']) : '—') ?></td><td>
            <?php if ((int)$account['id'] !== (int)$currentUser['id']): ?><form method="post" action="<?= h(app_url(['page'=>'users'])) ?>"><input type="hidden" name="action" value="toggle_user"><input type="hidden" name="id" value="<?= (int)$account['id'] ?>"><?= csrf_field() ?><button class="button button-small <?= (int)$account['active']===1?'button-danger':'button-soft' ?>" type="submit" data-confirm="وضعیت دسترسی این حساب تغییر کند؟"><?= (int)$account['active']===1?'غیرفعال‌سازی':'فعال‌سازی' ?></button></form><?php else: ?><span class="small-muted">حساب شما</span><?php endif; ?>
        </td></tr>
    <?php endforeach; ?>
    </tbody></table></div><div class="card-body"><div class="security-note"><?= icon('shield') ?><span>نقش مدیر سامانه را فقط به افراد مورداعتماد بدهید. نقش کارمند به پرونده‌های شخصی محدود است و بخش‌های حساس حقوق و اسناد را نمی‌بیند.</span></div></div></section>
</div>
