<?php
declare(strict_types=1);

return [
    'slug' => 'offboarding',
    'name' => 'خروج همکار',
    'group' => 'جذب و استخدام',
    'icon' => 'logout',
    'order' => 90,
    'description' => 'هماهنگی تسویه، تحویل دارایی‌ها و مراحل خروج کارکنان.',
    'primary' => 'title',
    'sensitive' => true,
    'columns' => [
        'employee',
        'last_working_date',
        'reason',
        'clearance',
        'status',
    ],
    'fields' => [
        [
            'name' => 'title',
            'label' => 'عنوان پرونده خروج',
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
            'name' => 'last_working_date',
            'label' => 'آخرین روز کاری',
            'type' => 'date',
            'required' => true,
        ],
        [
            'name' => 'reason',
            'label' => 'دلیل خروج',
            'type' => 'textarea',
        ],
        [
            'name' => 'clearance',
            'label' => 'وضعیت تسویه و تحویل',
            'type' => 'textarea',
        ],
        [
            'name' => 'status',
            'label' => 'وضعیت',
            'type' => 'select',
            'required' => true,
            'options' => [
                'pending' => 'شروع نشده',
                'in_progress' => 'در حال انجام',
                'completed' => 'تکمیل‌شده',
            ],
        ],
    ],
];
