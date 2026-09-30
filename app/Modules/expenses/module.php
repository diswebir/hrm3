<?php
declare(strict_types=1);

return [
    'slug' => 'expenses',
    'name' => 'هزینه‌ها و بازپرداخت',
    'group' => 'عملیات و پشتیبانی',
    'icon' => 'wallet',
    'order' => 150,
    'description' => 'ثبت هزینه‌های کارکنان، پیوست توضیح و پیگیری تأیید و پرداخت.',
    'primary' => 'title',
    'employee_access' => 'own',
    'columns' => [
        'employee',
        'expense_date',
        'category',
        'amount',
        'status',
    ],
    'fields' => [
        [
            'name' => 'title',
            'label' => 'شرح هزینه',
            'type' => 'text',
            'required' => true,
        ],
        [
            'name' => 'employee',
            'label' => 'درخواست‌کننده',
            'type' => 'employee',
            'required' => true,
        ],
        [
            'name' => 'expense_date',
            'label' => 'تاریخ هزینه',
            'type' => 'date',
            'required' => true,
        ],
        [
            'name' => 'category',
            'label' => 'دسته هزینه',
            'type' => 'select',
            'required' => true,
            'options' => [
                'transport' => 'رفت‌وآمد',
                'meal' => 'پذیرایی',
                'equipment' => 'تجهیزات',
                'other' => 'سایر',
            ],
        ],
        [
            'name' => 'amount',
            'label' => 'مبلغ (تومان)',
            'type' => 'number',
            'required' => true,
        ],
        [
            'name' => 'description',
            'label' => 'توضیحات',
            'type' => 'textarea',
        ],
        [
            'name' => 'status',
            'label' => 'وضعیت',
            'type' => 'select',
            'required' => true,
            'options' => [
                'pending' => 'در انتظار',
                'approved' => 'تأیید شده',
                'rejected' => 'رد شده',
                'paid' => 'پرداخت شده',
            ],
        ],
    ],
];
