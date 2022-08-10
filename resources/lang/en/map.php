<?php

return [
    'fieldset' => [
        'search_box' => [
            'placeholder' => 'Search on map...',
        ],
    ],

    'actions' => [
        'edit' => [
            'label' => 'ُEdit Markers',

            'fieldset' => [
                'lat_lng_system' => [
                    'label' => 'Latitude & Longitude System',
                ],

                'markers' => [
                    'label' => 'Markers',
                    'actions' => [
                        'create' => 'Add Marker'
                    ]
                ],

                'latitude' => [
                    'label' => 'Latitude'
                ],

                'longitude' => [
                    'label' => 'Longitude'
                ]
            ]
        ],

        'current_location' => [
            'label' => 'Current Location',
        ],
    ]
];
