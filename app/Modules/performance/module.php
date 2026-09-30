<?php
declare(strict_types=1);

return [
    'slug' => 'performance',
    'name' => 'ارزیابی عملکرد',
    'group' => 'رشد و یادگیری',
    'icon' => 'chart',
    'order' => 100,
    'description' => 'ثبت دوره ارزیابی، بازخورد، امتیاز و گفت‌وگوی عملکرد.',
    'primary' => 'title',
    'columns' => [
        'employee',
        'review_period',
        'score',
        'reviewer',
        'status',
    ],
    'fields' => [
        [
            'name' => 'title',
            'label' => 'عنوان ارزیابی',
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
            'name' => 'review_period',
            'label' => 'دوره ارزیابی',
            'type' => 'text',
            'required' => true,
        ],
        [
            'name' => 'score',
            'label' => 'امتیاز از ۱۰۰',
            'type' => 'number',
        ],
        [
            'name' => 'reviewer',
            'label' => 'ارزیاب',
            'type' => 'text',
        ],
        [
            'name' => 'goals',
            'label' => 'اهداف و دستاوردها',
            'type' => 'textarea',
        ],
        [
            'name' => 'feedback',
            'label' => 'بازخورد',
            'type' => 'textarea',
        ],
        [
            'name' => 'status',
            'label' => 'وضعیت',
            'type' => 'select',
            'required' => true,
            'options' => [
                'draft' => 'پیش‌نویس',
                'in_review' => 'در حال بررسی',
                'completed' => 'نهایی‌شده',
            ],
        ],
    ],
];
