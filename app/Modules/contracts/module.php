<?php
declare(strict_types=1);

return [
    'slug' => 'contracts',
    'name' => 'قراردادها',
    'group' => 'جبران خدمات',
    'icon' => 'file',
    'order' => 200,
    'description' => 'مدیریت نوع قرارداد، تاریخ اعتبار، یادآور تمدید و فایل مرتبط.',
    'primary' => 'title',
    'sensitive' => true,
    'columns' => [
        'employee',
        'contract_type',
        'start_date',
        'end_date',
        'renewal',
        'status',
    ],
    'fields' => [
        [
            'name' => 'title',
            'label' => 'عنوان قرارداد',
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
            'name' => 'contract_type',
            'label' => 'نوع قرارداد',
            'type' => 'select',
            'required' => true,
            'options' => [
                'permanent' => 'دائم',
                'fixed' => 'مدت‌دار',
                'consulting' => 'مشاوره',
                'internship' => 'کارآموزی',
            ],
        ],
        [
            'name' => 'start_date',
            'label' => 'تاریخ شروع',
            'type' => 'date',
            'required' => true,
        ],
        [
            'name' => 'end_date',
            'label' => 'تاریخ پایان',
            'type' => 'date',
        ],
        [
            'name' => 'renewal',
            'label' => 'نیازمند تمدید',
            'type' => 'select',
            'options' => [
                'yes' => 'بله',
                'no' => 'خیر',
            ],
        ],
        [
            'name' => 'file',
            'label' => 'فایل قرارداد',
            'type' => 'file',
        ],
        [
            'name' => 'status',
            'label' => 'وضعیت',
            'type' => 'select',
            'required' => true,
            'options' => [
                'draft' => 'پیش‌نویس',
                'active' => 'معتبر',
                'expiring' => 'در آستانه انقضا',
                'ended' => 'پایان‌یافته',
            ],
        ],
    ],
];
