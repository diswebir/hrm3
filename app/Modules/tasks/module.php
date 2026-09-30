<?php
declare(strict_types=1);

return [
    'slug' => 'tasks',
    'name' => 'وظایف و پیگیری‌ها',
    'group' => 'عملیات و پشتیبانی',
    'icon' => 'check',
    'order' => 170,
    'description' => 'واگذاری اقدام، اولویت، مسئول و مهلت انجام کار.',
    'primary' => 'title',
    'employee_access' => 'own',
    'columns' => [
        'assigned_to',
        'priority',
        'due_date',
        'status',
    ],
    'fields' => [
        [
            'name' => 'title',
            'label' => 'عنوان کار',
            'type' => 'text',
            'required' => true,
        ],
        [
            'name' => 'assigned_to',
            'label' => 'مسئول',
            'type' => 'employee',
        ],
        [
            'name' => 'priority',
            'label' => 'اولویت',
            'type' => 'select',
            'required' => true,
            'options' => [
                'low' => 'کم',
                'normal' => 'عادی',
                'high' => 'زیاد',
                'urgent' => 'فوری',
            ],
        ],
        [
            'name' => 'due_date',
            'label' => 'مهلت انجام',
            'type' => 'date',
        ],
        [
            'name' => 'description',
            'label' => 'شرح کار',
            'type' => 'textarea',
        ],
        [
            'name' => 'status',
            'label' => 'وضعیت',
            'type' => 'select',
            'required' => true,
            'options' => [
                'todo' => 'انجام‌نشده',
                'in_progress' => 'در حال انجام',
                'done' => 'انجام‌شده',
                'blocked' => 'متوقف',
            ],
        ],
    ],
];
