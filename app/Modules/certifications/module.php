<?php
declare(strict_types=1);

return [
    'slug' => 'certifications',
    'name' => 'گواهینامه‌ها و مهارت‌ها',
    'group' => 'رشد و یادگیری',
    'icon' => 'book',
    'order' => 280,
    'description' => 'ثبت گواهینامه حرفه‌ای کارکنان و بازبینی تاریخ اعتبار آن.',
    'primary' => 'title',
    'columns' => [
        'employee',
        'issuer',
        'issued_on',
        'expires_on',
        'status',
    ],
    'fields' => [
        [
            'name' => 'title',
            'label' => 'عنوان گواهینامه',
            'type' => 'text',
            'required' => true,
        ],
        [
            'name' => 'employee',
            'label' => 'دارنده',
            'type' => 'employee',
            'required' => true,
        ],
        [
            'name' => 'issuer',
            'label' => 'صادرکننده',
            'type' => 'text',
        ],
        [
            'name' => 'issued_on',
            'label' => 'تاریخ صدور',
            'type' => 'date',
        ],
        [
            'name' => 'expires_on',
            'label' => 'تاریخ انقضا',
            'type' => 'date',
        ],
        [
            'name' => 'file',
            'label' => 'تصویر / فایل گواهی',
            'type' => 'file',
        ],
        [
            'name' => 'status',
            'label' => 'وضعیت',
            'type' => 'select',
            'required' => true,
            'options' => [
                'active' => 'معتبر',
                'expiring' => 'نزدیک به انقضا',
                'expired' => 'منقضی',
            ],
        ],
    ],
];
