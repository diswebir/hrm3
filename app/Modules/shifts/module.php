<?php
declare(strict_types=1);

return [
    'slug' => 'shifts',
    'name' => 'شیفت‌ها و برنامه کاری',
    'group' => 'زمان و حضور',
    'icon' => 'clock',
    'order' => 220,
    'description' => 'تعریف الگوی شیفت، ساعات کاری، واحد و وضعیت انتشار برنامه.',
    'primary' => 'title',
    'columns' => [
        'department',
        'day',
        'start_time',
        'end_time',
        'assigned_to',
        'status',
    ],
    'fields' => [
        [
            'name' => 'title',
            'label' => 'نام شیفت',
            'type' => 'text',
            'required' => true,
        ],
        [
            'name' => 'department',
            'label' => 'واحد سازمانی',
            'type' => 'text',
        ],
        [
            'name' => 'day',
            'label' => 'روز هفته',
            'type' => 'select',
            'required' => true,
            'options' => [
                'sat' => 'شنبه',
                'sun' => 'یکشنبه',
                'mon' => 'دوشنبه',
                'tue' => 'سه‌شنبه',
                'wed' => 'چهارشنبه',
                'thu' => 'پنجشنبه',
                'fri' => 'جمعه',
            ],
        ],
        [
            'name' => 'start_time',
            'label' => 'شروع',
            'type' => 'time',
            'required' => true,
        ],
        [
            'name' => 'end_time',
            'label' => 'پایان',
            'type' => 'time',
            'required' => true,
        ],
        [
            'name' => 'assigned_to',
            'label' => 'کارمند',
            'type' => 'employee',
        ],
        [
            'name' => 'status',
            'label' => 'وضعیت',
            'type' => 'select',
            'required' => true,
            'options' => [
                'planned' => 'پیش‌نویس',
                'published' => 'منتشرشده',
                'archived' => 'بایگانی',
            ],
        ],
    ],
];
