<?php
declare(strict_types=1);

return [
    'slug' => 'disciplinary',
    'name' => 'پرونده‌های انضباطی',
    'group' => 'عملیات و پشتیبانی',
    'icon' => 'shield',
    'order' => 260,
    'description' => 'پرونده محرمانه رسیدگی انضباطی با دسترسی محدود مدیر منابع انسانی.',
    'primary' => 'title',
    'sensitive' => true,
    'columns' => [
        'employee',
        'event_date',
        'category',
        'notes',
        'status',
    ],
    'fields' => [
        [
            'name' => 'title',
            'label' => 'عنوان پرونده',
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
            'name' => 'event_date',
            'label' => 'تاریخ',
            'type' => 'date',
        ],
        [
            'name' => 'category',
            'label' => 'موضوع',
            'type' => 'text',
        ],
        [
            'name' => 'notes',
            'label' => 'یادداشت محرمانه',
            'type' => 'textarea',
        ],
        [
            'name' => 'status',
            'label' => 'وضعیت',
            'type' => 'select',
            'required' => true,
            'options' => [
                'open' => 'در حال رسیدگی',
                'resolved' => 'مختومه',
                'confidential' => 'محرمانه',
            ],
        ],
    ],
];
