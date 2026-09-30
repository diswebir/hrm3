<div class="card confirm-card">
    <span class="error-icon"><?= icon('trash') ?></span>
    <h1>حذف این رکورد تأیید شود؟</h1>
    <p>«<?= h($module['entity'] === 'employees' ? trim(($deleteRecord['first_name'] ?? '').' '.($deleteRecord['last_name'] ?? '')) : ($deleteRecord['title'] ?? '')) ?>» برای همیشه حذف می‌شود. این اقدام قابل بازگشت نیست.</p>
    <form method="post" action="<?= h(app_url(['page' => $module['slug']])) ?>" class="confirm-actions">
        <input type="hidden" name="action" value="delete_record"><input type="hidden" name="module" value="<?= h($module['slug']) ?>"><input type="hidden" name="id" value="<?= (int)$deleteRecord['id'] ?>"><?= csrf_field() ?>
        <a class="button button-secondary" href="<?= h(app_url(['page' => $module['slug']])) ?>">بازگشت</a><button class="button button-danger" type="submit">حذف رکورد</button>
    </form>
</div>
