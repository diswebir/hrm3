<?php
declare(strict_types=1);

return [
    'slug' => 'recruitment',
    'name' => 'فرصت‌های شغلی',
    'group' => 'جذب و استخدام',
    'icon' => 'briefcase',
    'order' => 60,
    'description' => 'مدیریت درخواست نیروی انسانی، موقعیت شغلی و انتشار فرصت‌ها.',
    'primary' => 'title',
    'columns' => [
        'department',
        'location',
        'employment_type',
        'openings',
        'closing_date',
        'status',
    ],
    'fields' => [
        [
            'name' => 'title',
            'label' => 'عنوان موقعیت شغلی',
            'type' => 'text',
            'required' => true,
        ],
        [
            'name' => 'department',
            'label' => 'واحد سازمانی',
            'type' => 'text',
        ],
        [
            'name' => 'location',
            'label' => 'محل کار',
            'type' => 'text',
        ],
        [
            'name' => 'employment_type',
            'label' => 'نوع همکاری',
            'type' => 'select',
            'options' => [
                'full_time' => 'تمام‌وقت',
                'part_time' => 'پاره‌وقت',
                'contract' => 'قراردادی',
                'intern' => 'کارآموزی',
            ],
        ],
        [
            'name' => 'openings',
            'label' => 'تعداد ظرفیت',
            'type' => 'number',
        ],
        [
            'name' => 'closing_date',
            'label' => 'مهلت درخواست',
            'type' => 'date',
        ],
        [
            'name' => 'description',
            'label' => 'شرح موقعیت',
            'type' => 'textarea',
        ],
        [
            'name' => 'status',
            'label' => 'وضعیت',
            'type' => 'select',
            'required' => true,
            'options' => [
                'open' => 'در حال جذب',
                'paused' => 'متوقف',
                'closed' => 'بسته',
            ],
        ],
    ],
];
