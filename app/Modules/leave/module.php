<?php
declare(strict_types=1);

return [
    'slug' => 'leave',
    'name' => 'درخواست‌های مرخصی',
    'group' => 'زمان و حضور',
    'icon' => 'calendar',
    'order' => 40,
    'description' => 'ثبت، پیگیری و تأیید گردش درخواست انواع مرخصی.',
    'primary' => 'title',
    'employee_access' => 'own',
    'columns' => [
        'employee',
        'leave_type',
        'start_date',
        'end_date',
        'days',
        'status',
    ],
    'fields' => [
        [
            'name' => 'title',
            'label' => 'عنوان درخواست',
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
            'name' => 'leave_type',
            'label' => 'نوع مرخصی',
            'type' => 'select',
            'required' => true,
            'options' => [
                'annual' => 'استحقاقی',
                'sick' => 'استعلاجی',
                'unpaid' => 'بدون حقوق',
                'personal' => 'ساعتی / شخصی',
            ],
        ],
        [
            'name' => 'start_date',
            'label' => 'از تاریخ',
            'type' => 'date',
            'required' => true,
        ],
        [
            'name' => 'end_date',
            'label' => 'تا تاریخ',
            'type' => 'date',
            'required' => true,
        ],
        [
            'name' => 'days',
            'label' => 'تعداد روز',
            'type' => 'number',
            'required' => true,
        ],
        [
            'name' => 'reason',
            'label' => 'دلیل درخواست',
            'type' => 'textarea',
            'required' => true,
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
            ],
        ],
    ],
];
