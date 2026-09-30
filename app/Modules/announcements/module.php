<?php
declare(strict_types=1);

return [
    'slug' => 'announcements',
    'name' => 'اطلاعیه‌های سازمانی',
    'group' => 'عملیات و پشتیبانی',
    'icon' => 'file',
    'order' => 180,
    'description' => 'انتشار پیام‌های داخلی برای همه کارکنان یا گروه هدف.',
    'primary' => 'title',
    'employee_access' => 'own',
    'employee_read_all' => true,
    'columns' => [
        'audience',
        'published_on',
        'status',
    ],
    'fields' => [
        [
            'name' => 'title',
            'label' => 'عنوان اطلاعیه',
            'type' => 'text',
            'required' => true,
        ],
        [
            'name' => 'audience',
            'label' => 'مخاطبان',
            'type' => 'select',
            'required' => true,
            'options' => [
                'all' => 'همه کارکنان',
                'managers' => 'مدیران',
                'department' => 'واحد مشخص',
            ],
        ],
        [
            'name' => 'published_on',
            'label' => 'تاریخ انتشار',
            'type' => 'date',
        ],
        [
            'name' => 'body',
            'label' => 'متن اطلاعیه',
            'type' => 'textarea',
            'required' => true,
        ],
        [
            'name' => 'status',
            'label' => 'وضعیت',
            'type' => 'select',
            'required' => true,
            'options' => [
                'draft' => 'پیش‌نویس',
                'published' => 'منتشرشده',
                'archived' => 'بایگانی',
            ],
        ],
    ],
];
