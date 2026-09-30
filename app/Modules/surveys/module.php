<?php
declare(strict_types=1);

return [
    'slug' => 'surveys',
    'name' => 'نظرسنجی کارکنان',
    'group' => 'رشد و یادگیری',
    'icon' => 'file',
    'order' => 240,
    'description' => 'تعریف پرسش یا نظرسنجی داخلی و مدیریت بازه انتشار آن.',
    'primary' => 'title',
    'columns' => [
        'audience',
        'deadline',
        'question',
        'status',
    ],
    'fields' => [
        [
            'name' => 'title',
            'label' => 'عنوان نظرسنجی',
            'type' => 'text',
            'required' => true,
        ],
        [
            'name' => 'audience',
            'label' => 'مخاطبان',
            'type' => 'select',
            'required' => true,
            'options' => [
                'all' => 'همه',
                'managers' => 'مدیران',
                'department' => 'یک واحد',
            ],
        ],
        [
            'name' => 'deadline',
            'label' => 'مهلت پاسخ',
            'type' => 'date',
        ],
        [
            'name' => 'question',
            'label' => 'پرسش / توضیحات',
            'type' => 'textarea',
            'required' => true,
        ],
        [
            'name' => 'status',
            'label' => 'وضعیت',
            'type' => 'select',
            'required' => true,
            'options' => [
                'draft' => 'پیش‌نویس',
                'open' => 'باز',
                'closed' => 'بسته',
            ],
        ],
    ],
];
