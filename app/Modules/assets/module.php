<?php
declare(strict_types=1);

return [
    'slug' => 'assets',
    'name' => 'دارایی‌ها و تجهیزات',
    'group' => 'عملیات و پشتیبانی',
    'icon' => 'briefcase',
    'order' => 140,
    'description' => 'ثبت دارایی‌های سازمان، مسئول تحویل‌گیرنده و وضعیت نگهداری.',
    'primary' => 'title',
    'columns' => [
        'asset_tag',
        'category',
        'assigned_to',
        'assigned_date',
        'condition',
        'status',
    ],
    'fields' => [
        [
            'name' => 'title',
            'label' => 'نام دارایی',
            'type' => 'text',
            'required' => true,
        ],
        [
            'name' => 'asset_tag',
            'label' => 'شناسه دارایی',
            'type' => 'text',
            'required' => true,
        ],
        [
            'name' => 'category',
            'label' => 'دسته‌بندی',
            'type' => 'text',
        ],
        [
            'name' => 'assigned_to',
            'label' => 'تحویل‌گیرنده',
            'type' => 'employee',
        ],
        [
            'name' => 'assigned_date',
            'label' => 'تاریخ تحویل',
            'type' => 'date',
        ],
        [
            'name' => 'condition',
            'label' => 'وضعیت فیزیکی',
            'type' => 'select',
            'options' => [
                'new' => 'نو',
                'good' => 'سالم',
                'repair' => 'نیازمند تعمیر',
            ],
        ],
        [
            'name' => 'status',
            'label' => 'وضعیت',
            'type' => 'select',
            'required' => true,
            'options' => [
                'available' => 'آزاد',
                'assigned' => 'تحویل‌شده',
                'repair' => 'در تعمیر',
                'retired' => 'خارج از سرویس',
            ],
        ],
    ],
];
