<?php
declare(strict_types=1);

return [
    'slug' => 'compensation',
    'name' => 'تغییرات جبران خدمات',
    'group' => 'جبران خدمات',
    'icon' => 'wallet',
    'order' => 210,
    'description' => 'ثبت تاریخچه تغییر حقوق، علت و تأیید تغییرات حساس پرداخت.',
    'primary' => 'title',
    'sensitive' => true,
    'columns' => [
        'employee',
        'effective_date',
        'old_salary',
        'new_salary',
        'change_reason',
        'status',
    ],
    'fields' => [
        [
            'name' => 'title',
            'label' => 'عنوان تغییر',
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
            'name' => 'effective_date',
            'label' => 'تاریخ اجرا',
            'type' => 'date',
            'required' => true,
        ],
        [
            'name' => 'old_salary',
            'label' => 'حقوق قبلی',
            'type' => 'number',
        ],
        [
            'name' => 'new_salary',
            'label' => 'حقوق جدید',
            'type' => 'number',
        ],
        [
            'name' => 'change_reason',
            'label' => 'علت تغییر',
            'type' => 'textarea',
        ],
        [
            'name' => 'status',
            'label' => 'وضعیت',
            'type' => 'select',
            'required' => true,
            'options' => [
                'pending' => 'در انتظار',
                'approved' => 'تأییدشده',
                'effective' => 'اجراشده',
            ],
        ],
    ],
];
