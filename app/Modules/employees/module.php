<?php
declare(strict_types=1);

return [
    'slug' => 'employees',
    'name' => 'پرونده کارکنان',
    'group' => 'سرمایه انسانی',
    'icon' => 'users',
    'order' => 10,
    'entity' => 'employees',
    'description' => 'پرونده پایه افراد، اطلاعات شغلی و وضعیت همکاری.',
    'primary' => 'first_name',
    'columns' => [
        'employee_code',
        'department',
        'position',
        'email',
        'hire_date',
        'status',
    ],
    'fields' => [
        [
            'name' => 'employee_code',
            'label' => 'کد پرسنلی',
            'type' => 'text',
            'required' => true,
            'placeholder' => 'مثلاً HR-1007',
        ],
        [
            'name' => 'first_name',
            'label' => 'نام',
            'type' => 'text',
            'required' => true,
        ],
        [
            'name' => 'last_name',
            'label' => 'نام خانوادگی',
            'type' => 'text',
            'required' => true,
        ],
        [
            'name' => 'email',
            'label' => 'ایمیل سازمانی',
            'type' => 'email',
        ],
        [
            'name' => 'phone',
            'label' => 'شماره تماس',
            'type' => 'tel',
        ],
        [
            'name' => 'department',
            'label' => 'واحد سازمانی',
            'type' => 'text',
        ],
        [
            'name' => 'position',
            'label' => 'عنوان شغلی',
            'type' => 'text',
        ],
        [
            'name' => 'manager',
            'label' => 'مدیر مستقیم',
            'type' => 'text',
        ],
        [
            'name' => 'hire_date',
            'label' => 'تاریخ شروع همکاری',
            'type' => 'date',
        ],
        [
            'name' => 'employment_type',
            'label' => 'نوع همکاری',
            'type' => 'select',
            'required' => true,
            'options' => [
                'full_time' => 'تمام‌وقت',
                'part_time' => 'پاره‌وقت',
                'contract' => 'قراردادی',
                'intern' => 'کارآموز',
            ],
        ],
        [
            'name' => 'status',
            'label' => 'وضعیت',
            'type' => 'select',
            'required' => true,
            'options' => [
                'active' => 'فعال',
                'on_leave' => 'مرخصی',
                'inactive' => 'غیرفعال',
            ],
        ],
        [
            'name' => 'location',
            'label' => 'محل خدمت',
            'type' => 'text',
        ],
        [
            'name' => 'notes',
            'label' => 'یادداشت پرونده',
            'type' => 'textarea',
        ],
    ],
];
