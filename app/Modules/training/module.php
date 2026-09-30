<?php
declare(strict_types=1);

return [
    'slug' => 'training',
    'name' => 'آموزش و توسعه',
    'group' => 'رشد و یادگیری',
    'icon' => 'book',
    'order' => 120,
    'description' => 'تقویم دوره‌ها، مدرس، ساعات آموزش و ثبت مشارکت کارکنان.',
    'primary' => 'title',
    'employee_access' => 'own',
    'employee_read_all' => true,
    'columns' => [
        'employee',
        'provider',
        'start_date',
        'hours',
        'status',
    ],
    'fields' => [
        [
            'name' => 'title',
            'label' => 'عنوان دوره',
            'type' => 'text',
            'required' => true,
        ],
        [
            'name' => 'employee',
            'label' => 'شرکت‌کننده',
            'type' => 'employee',
        ],
        [
            'name' => 'provider',
            'label' => 'ارائه‌دهنده / مدرس',
            'type' => 'text',
        ],
        [
            'name' => 'start_date',
            'label' => 'تاریخ شروع',
            'type' => 'date',
        ],
        [
            'name' => 'hours',
            'label' => 'مدت (ساعت)',
            'type' => 'number',
        ],
        [
            'name' => 'description',
            'label' => 'شرح دوره',
            'type' => 'textarea',
        ],
        [
            'name' => 'status',
            'label' => 'وضعیت',
            'type' => 'select',
            'required' => true,
            'options' => [
                'planned' => 'برنامه‌ریزی‌شده',
                'in_progress' => 'در حال برگزاری',
                'completed' => 'تکمیل‌شده',
                'cancelled' => 'لغوشده',
            ],
        ],
    ],
];
