<?php
declare(strict_types=1);

return [
    'slug' => 'goals',
    'name' => 'اهداف و نتایج کلیدی',
    'group' => 'رشد و یادگیری',
    'icon' => 'chart',
    'order' => 110,
    'description' => 'تعریف هدف، شاخص سنجش و میزان پیشرفت فردی یا تیمی.',
    'primary' => 'title',
    'columns' => [
        'employee',
        'period',
        'target',
        'progress',
        'status',
    ],
    'fields' => [
        [
            'name' => 'title',
            'label' => 'عنوان هدف',
            'type' => 'text',
            'required' => true,
        ],
        [
            'name' => 'employee',
            'label' => 'مسئول هدف',
            'type' => 'employee',
        ],
        [
            'name' => 'period',
            'label' => 'دوره هدف',
            'type' => 'text',
        ],
        [
            'name' => 'target',
            'label' => 'شاخص / نتیجه مورد انتظار',
            'type' => 'textarea',
        ],
        [
            'name' => 'progress',
            'label' => 'درصد پیشرفت',
            'type' => 'number',
        ],
        [
            'name' => 'due_date',
            'label' => 'مهلت تحقق',
            'type' => 'date',
        ],
        [
            'name' => 'status',
            'label' => 'وضعیت',
            'type' => 'select',
            'required' => true,
            'options' => [
                'planned' => 'برنامه‌ریزی‌شده',
                'in_progress' => 'در حال اجرا',
                'completed' => 'محقق‌شده',
                'cancelled' => 'لغوشده',
            ],
        ],
    ],
];
