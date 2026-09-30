<?php
declare(strict_types=1);

return [
    'slug' => 'attendance',
    'name' => 'حضور و غیاب',
    'group' => 'زمان و حضور',
    'icon' => 'clock',
    'order' => 30,
    'description' => 'ثبت روزانه ورود و خروج، دورکاری، تأخیر و غیبت.',
    'primary' => 'title',
    'employee_access' => 'own',
    'columns' => [
        'employee',
        'work_date',
        'check_in',
        'check_out',
        'status',
    ],
    'fields' => [
        [
            'name' => 'title',
            'label' => 'عنوان ثبت',
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
            'name' => 'work_date',
            'label' => 'تاریخ',
            'type' => 'date',
            'required' => true,
        ],
        [
            'name' => 'check_in',
            'label' => 'ساعت ورود',
            'type' => 'time',
        ],
        [
            'name' => 'check_out',
            'label' => 'ساعت خروج',
            'type' => 'time',
        ],
        [
            'name' => 'status',
            'label' => 'وضعیت حضور',
            'type' => 'select',
            'required' => true,
            'options' => [
                'present' => 'حاضر',
                'remote' => 'دورکار',
                'late' => 'با تأخیر',
                'absent' => 'غایب',
                'leave' => 'مرخصی',
            ],
        ],
        [
            'name' => 'note',
            'label' => 'توضیحات',
            'type' => 'textarea',
        ],
    ],
];
