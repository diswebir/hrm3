<?php
declare(strict_types=1);

return [
    'slug' => 'travel',
    'name' => 'ماموریت و سفر کاری',
    'group' => 'عملیات و پشتیبانی',
    'icon' => 'briefcase',
    'order' => 160,
    'description' => 'درخواست سفر کاری، مقصد، بازه زمانی و وضعیت تصویب.',
    'primary' => 'title',
    'employee_access' => 'own',
    'columns' => [
        'employee',
        'destination',
        'departure_date',
        'return_date',
        'purpose',
        'status',
    ],
    'fields' => [
        [
            'name' => 'title',
            'label' => 'عنوان ماموریت',
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
            'name' => 'destination',
            'label' => 'مقصد',
            'type' => 'text',
            'required' => true,
        ],
        [
            'name' => 'departure_date',
            'label' => 'تاریخ حرکت',
            'type' => 'date',
            'required' => true,
        ],
        [
            'name' => 'return_date',
            'label' => 'تاریخ بازگشت',
            'type' => 'date',
        ],
        [
            'name' => 'purpose',
            'label' => 'هدف ماموریت',
            'type' => 'textarea',
        ],
        [
            'name' => 'status',
            'label' => 'وضعیت',
            'type' => 'select',
            'required' => true,
            'options' => [
                'requested' => 'درخواست‌شده',
                'approved' => 'تأییدشده',
                'completed' => 'انجام‌شده',
                'cancelled' => 'لغوشده',
            ],
        ],
    ],
];
