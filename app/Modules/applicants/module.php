<?php
declare(strict_types=1);

return [
    'slug' => 'applicants',
    'name' => 'متقاضیان استخدام',
    'group' => 'جذب و استخدام',
    'icon' => 'users',
    'order' => 70,
    'description' => 'پرونده نامزدها و پیگیری مرحله‌به‌مرحله فرایند جذب.',
    'primary' => 'title',
    'columns' => [
        'email',
        'phone',
        'applied_for',
        'source',
        'stage',
        'applied_at',
    ],
    'fields' => [
        [
            'name' => 'title',
            'label' => 'نام متقاضی',
            'type' => 'text',
            'required' => true,
        ],
        [
            'name' => 'email',
            'label' => 'ایمیل',
            'type' => 'email',
        ],
        [
            'name' => 'phone',
            'label' => 'شماره تماس',
            'type' => 'tel',
        ],
        [
            'name' => 'applied_for',
            'label' => 'موقعیت شغلی',
            'type' => 'text',
        ],
        [
            'name' => 'source',
            'label' => 'منبع آشنایی',
            'type' => 'text',
        ],
        [
            'name' => 'applied_at',
            'label' => 'تاریخ درخواست',
            'type' => 'date',
        ],
        [
            'name' => 'stage',
            'label' => 'مرحله جذب',
            'type' => 'select',
            'required' => true,
            'options' => [
                'new' => 'جدید',
                'screening' => 'بررسی رزومه',
                'interview' => 'مصاحبه',
                'offer' => 'پیشنهاد همکاری',
                'hired' => 'استخدام شد',
                'rejected' => 'رد شد',
            ],
        ],
        [
            'name' => 'notes',
            'label' => 'یادداشت مصاحبه',
            'type' => 'textarea',
        ],
    ],
];
