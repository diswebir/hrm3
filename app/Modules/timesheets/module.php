<?php
declare(strict_types=1);

return [
    'slug' => 'timesheets',
    'name' => 'کارکرد و اضافه‌کاری',
    'group' => 'زمان و حضور',
    'icon' => 'clock',
    'order' => 230,
    'description' => 'ثبت کارکرد دوره‌ای و ساعات اضافه‌کاری جهت بازبینی مدیر.',
    'primary' => 'title',
    'columns' => [
        'employee',
        'week_start',
        'hours',
        'overtime',
        'approver',
        'status',
    ],
    'fields' => [
        [
            'name' => 'title',
            'label' => 'عنوان کارکرد',
            'type' => 'text',
            'required' => true,
        ],
        [
            'name' => 'employee',
            'label' => 'کارمند',
            'type' => 'employee',
            'required' => true,
        ],
        [
            'name' => 'week_start',
            'label' => 'شروع دوره',
            'type' => 'date',
            'required' => true,
        ],
        [
            'name' => 'hours',
            'label' => 'ساعات عادی',
            'type' => 'number',
            'required' => true,
        ],
        [
            'name' => 'overtime',
            'label' => 'اضافه‌کاری (ساعت)',
            'type' => 'number',
        ],
        [
            'name' => 'approver',
            'label' => 'تأییدکننده',
            'type' => 'text',
        ],
        [
            'name' => 'status',
            'label' => 'وضعیت',
            'type' => 'select',
            'required' => true,
            'options' => [
                'draft' => 'پیش‌نویس',
                'submitted' => 'ارسال‌شده',
                'approved' => 'تأییدشده',
                'rejected' => 'نیازمند اصلاح',
            ],
        ],
    ],
];
