<?php
declare(strict_types=1);

return [
    'slug' => 'onboarding',
    'name' => 'ورود همکار جدید',
    'group' => 'جذب و استخدام',
    'icon' => 'spark',
    'order' => 80,
    'description' => 'چک‌لیست و برنامه شروع همکاری و آشنایی سازمانی.',
    'primary' => 'title',
    'columns' => [
        'employee',
        'start_date',
        'mentor',
        'checklist',
        'status',
    ],
    'fields' => [
        [
            'name' => 'title',
            'label' => 'عنوان برنامه',
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
            'name' => 'start_date',
            'label' => 'تاریخ شروع',
            'type' => 'date',
        ],
        [
            'name' => 'mentor',
            'label' => 'مربی / همراه',
            'type' => 'text',
        ],
        [
            'name' => 'checklist',
            'label' => 'چک‌لیست اقدامات',
            'type' => 'textarea',
        ],
        [
            'name' => 'status',
            'label' => 'وضعیت',
            'type' => 'select',
            'required' => true,
            'options' => [
                'planned' => 'برنامه‌ریزی‌شده',
                'in_progress' => 'در حال اجرا',
                'completed' => 'تکمیل‌شده',
            ],
        ],
    ],
];
