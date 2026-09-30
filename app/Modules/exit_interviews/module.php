<?php
declare(strict_types=1);

return [
    'slug' => 'exit_interviews',
    'name' => 'مصاحبه خروج',
    'group' => 'جذب و استخدام',
    'icon' => 'users',
    'order' => 290,
    'description' => 'ثبت زمان مصاحبه خروج و جمع‌بندی بازخورد پایان همکاری.',
    'primary' => 'title',
    'sensitive' => true,
    'columns' => [
        'employee',
        'scheduled_on',
        'interviewer',
        'reason',
        'status',
    ],
    'fields' => [
        [
            'name' => 'title',
            'label' => 'عنوان پرونده',
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
            'name' => 'scheduled_on',
            'label' => 'تاریخ مصاحبه',
            'type' => 'date',
        ],
        [
            'name' => 'interviewer',
            'label' => 'مصاحبه‌گر',
            'type' => 'text',
        ],
        [
            'name' => 'reason',
            'label' => 'دلیل خروج',
            'type' => 'textarea',
        ],
        [
            'name' => 'feedback',
            'label' => 'بازخورد و جمع‌بندی',
            'type' => 'textarea',
        ],
        [
            'name' => 'status',
            'label' => 'وضعیت',
            'type' => 'select',
            'required' => true,
            'options' => [
                'scheduled' => 'زمان‌بندی‌شده',
                'completed' => 'انجام‌شده',
                'cancelled' => 'لغوشده',
            ],
        ],
    ],
];
