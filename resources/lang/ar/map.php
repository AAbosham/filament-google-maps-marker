<?php

return [
    'fieldset' => [
        'search_box' => [
            'placeholder' => 'بحث في الخريطة',
        ],
    ],

    'actions' => [
        'edit' => [
            'label' => 'ادخال الاحداثيات',

            'fieldset' => [
                'lat_lng_system' => [
                    'label' => 'ادخال عبر خطوط الطول والعرض',
                ],

                'markers' => [
                    'label' => 'نقاط الاحداثيات',
                    'actions' => [
                        'create' => 'اضافة احداثيات'
                    ]
                ],

                'latitude' => [
                    'label' => 'خط العرض'
                ],

                'longitude' => [
                    'label' => 'خط الطول'
                ]
            ]
        ],

        'current_location' => [
            'label' => 'موقعي الحالي',
        ],
    ]
];
