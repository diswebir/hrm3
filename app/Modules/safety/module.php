<?php
declare(strict_types=1);

return [
    'slug' => 'safety',
    'name' => 'سلامت و ایمنی',
    'group' => 'عملیات و پشتیبانی',
    'icon' => 'shield',
    'order' => 250,
    'description' => 'ثبت رخداد ایمنی، محل، اقدام اصلاحی و وضعیت رسیدگی.',
    'primary' => 'title',
    'columns' => [
        'event_date',
        'category',
        'location',
        'action',
        'status',
    ],
    'fields' => [
        [
            'name' => 'title',
            'label' => 'عنوان رخداد',
            'type' => 'text',
            'required' => true,
        ],
        [
            'name' => 'event_date',
            'label' => 'تاریخ رخداد',
            'type' => 'date',
            'required' => true,
        ],
        [
            'name' => 'category',
            'label' => 'دسته‌بندی',
            'type' => 'text',
        ],
        [
            'name' => 'location',
            'label' => 'محل رخداد',
            'type' => 'text',
        ],
        [
            'name' => 'action',
            'label' => 'اقدام اصلاحی',
            'type' => 'textarea',
        ],
        [
            'name' => 'status',
            'label' => 'وضعیت',
            'type' => 'select',
            'required' => true,
            'options' => [
                'reported' => 'ثبت‌شده',
                'investigating' => 'در حال بررسی',
                'resolved' => 'رسیدگی‌شده',
            ],
        ],
    ],
];
