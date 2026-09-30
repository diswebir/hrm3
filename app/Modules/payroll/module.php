<?php
declare(strict_types=1);

return [
    'slug' => 'payroll',
    'name' => 'حقوق و دستمزد',
    'group' => 'جبران خدمات',
    'icon' => 'wallet',
    'order' => 50,
    'description' => 'ثبت دوره‌های پرداخت، اجزا و وضعیت تسویه حقوق.',
    'primary' => 'title',
    'sensitive' => true,
    'columns' => [
        'employee',
        'period',
        'base_salary',
        'allowances',
        'deductions',
        'net_salary',
        'status',
    ],
    'fields' => [
        [
            'name' => 'title',
            'label' => 'عنوان فیش',
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
            'name' => 'period',
            'label' => 'ماه پرداخت',
            'type' => 'month',
            'required' => true,
        ],
        [
            'name' => 'base_salary',
            'label' => 'حقوق پایه',
            'type' => 'number',
            'required' => true,
        ],
        [
            'name' => 'allowances',
            'label' => 'مزایا',
            'type' => 'number',
        ],
        [
            'name' => 'deductions',
            'label' => 'کسورات',
            'type' => 'number',
        ],
        [
            'name' => 'net_salary',
            'label' => 'خالص پرداختی',
            'type' => 'number',
            'help' => 'در صورت خالی‌بودن، سامانه خالص را محاسبه می‌کند.',
        ],
        [
            'name' => 'status',
            'label' => 'وضعیت',
            'type' => 'select',
            'required' => true,
            'options' => [
                'draft' => 'پیش‌نویس',
                'approved' => 'تأیید شده',
                'paid' => 'پرداخت شده',
            ],
        ],
    ],
];
