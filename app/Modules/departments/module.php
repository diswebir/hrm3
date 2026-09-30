<?php
declare(strict_types=1);

return [
    'slug' => 'departments',
    'name' => 'واحدهای سازمانی',
    'group' => 'سرمایه انسانی',
    'icon' => 'building',
    'order' => 20,
    'description' => 'تعریف واحدها، مسئولان، محل فعالیت و مرکز هزینه.',
    'primary' => 'title',
    'columns' => [
        'manager',
        'location',
        'cost_center',
        'budget',
        'status',
    ],
    'fields' => [
        [
            'name' => 'title',
            'label' => 'نام واحد',
            'type' => 'text',
            'required' => true,
        ],
        [
            'name' => 'manager',
            'label' => 'مدیر واحد',
            'type' => 'text',
        ],
        [
            'name' => 'location',
            'label' => 'محل فعالیت',
            'type' => 'text',
        ],
        [
            'name' => 'cost_center',
            'label' => 'مرکز هزینه',
            'type' => 'text',
        ],
        [
            'name' => 'budget',
            'label' => 'بودجه سالانه',
            'type' => 'number',
        ],
        [
            'name' => 'status',
            'label' => 'وضعیت',
            'type' => 'select',
            'required' => true,
            'options' => [
                'active' => 'فعال',
                'inactive' => 'غیرفعال',
            ],
        ],
    ],
];
