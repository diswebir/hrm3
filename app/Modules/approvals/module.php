<?php
declare(strict_types=1);

return [
    'slug' => 'approvals',
    'name' => 'گردش تأییدها',
    'group' => 'عملیات و پشتیبانی',
    'icon' => 'check',
    'order' => 270,
    'description' => 'فهرست درخواست‌های نیازمند بررسی، مسئول تصویب و مهلت اقدام.',
    'primary' => 'title',
    'columns' => [
        'requester',
        'request_type',
        'amount',
        'approver',
        'due_date',
        'status',
    ],
    'fields' => [
        [
            'name' => 'title',
            'label' => 'عنوان درخواست',
            'type' => 'text',
            'required' => true,
        ],
        [
            'name' => 'requester',
            'label' => 'درخواست‌کننده',
            'type' => 'text',
        ],
        [
            'name' => 'request_type',
            'label' => 'نوع درخواست',
            'type' => 'text',
        ],
        [
            'name' => 'amount',
            'label' => 'مبلغ (در صورت نیاز)',
            'type' => 'number',
        ],
        [
            'name' => 'approver',
            'label' => 'تأییدکننده',
            'type' => 'text',
        ],
        [
            'name' => 'due_date',
            'label' => 'مهلت بررسی',
            'type' => 'date',
        ],
        [
            'name' => 'status',
            'label' => 'وضعیت',
            'type' => 'select',
            'required' => true,
            'options' => [
                'pending' => 'در انتظار',
                'approved' => 'تأییدشده',
                'rejected' => 'ردشده',
            ],
        ],
    ],
];
