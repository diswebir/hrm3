<?php
declare(strict_types=1);

return [
    'slug' => 'documents',
    'name' => 'اسناد کارکنان',
    'group' => 'عملیات و پشتیبانی',
    'icon' => 'file',
    'order' => 130,
    'description' => 'نگهداری امن فراداده و فایل اسناد با تاریخ انقضا و دسترسی محدود.',
    'primary' => 'title',
    'sensitive' => true,
    'columns' => [
        'employee',
        'document_type',
        'expires_on',
        'file',
        'status',
    ],
    'fields' => [
        [
            'name' => 'title',
            'label' => 'عنوان سند',
            'type' => 'text',
            'required' => true,
        ],
        [
            'name' => 'employee',
            'label' => 'کارمند',
            'type' => 'employee',
        ],
        [
            'name' => 'document_type',
            'label' => 'دسته‌بندی',
            'type' => 'select',
            'required' => true,
            'options' => [
                'identity' => 'هویتی',
                'contract' => 'قرارداد',
                'education' => 'تحصیلی',
                'insurance' => 'بیمه',
                'other' => 'سایر',
            ],
        ],
        [
            'name' => 'expires_on',
            'label' => 'تاریخ انقضا',
            'type' => 'date',
        ],
        [
            'name' => 'file',
            'label' => 'فایل پیوست',
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
