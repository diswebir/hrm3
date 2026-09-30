<?php
declare(strict_types=1);

return [
    'slug' => 'benefits',
    'name' => 'مزایا و رفاهیات',
    'group' => 'جبران خدمات',
    'icon' => 'spark',
    'order' => 190,
    'description' => 'ثبت مزایا، استحقاق‌ها، مبلغ یا بازه بهره‌مندی کارکنان.',
    'primary' => 'title',
    'sensitive' => true,
    'columns' => [
        'employee',
        'benefit_type',
        'amount',
        'start_date',
        'status',
    ],
    'fields' => [
        [
            'name' => 'title',
            'label' => 'عنوان مزیت',
            'type' => 'text',
            'required' => true,
        ],
        [
            'name' => 'employee',
            'label' => 'کارمند',
            'type' => 'employee',
        ],
        [
            'name' => 'benefit_type',
            'label' => 'نوع مزیت',
            'type' => 'text',
        ],
        [
            'name' => 'amount',
            'label' => 'مبلغ / سقف',
            'type' => 'number',
        ],
        [
            'name' => 'start_date',
            'label' => 'تاریخ شروع',
            'type' => 'date',
        ],
        [
            'name' => 'end_date',
            'label' => 'تاریخ پایان',
            'type' => 'date',
        ],
        [
            'name' => 'status',
            'label' => 'وضعیت',
            'type' => 'select',
            'required' => true,
            'options' => [
                'active' => 'فعال',
                'paused' => 'متوقف',
                'ended' => 'پایان‌یافته',
            ],
        ],
    ],
];
