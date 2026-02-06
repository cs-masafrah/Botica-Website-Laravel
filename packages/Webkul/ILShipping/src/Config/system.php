<?php

return [
    [
        'key'    => 'sales.carriers.ilshipping',
        'name'   => 'ILShipping', // use translation
        'info'   => 'Information about ILShipping', // use translation
        'sort'   => 1,
        'fields' => [
            [
                'name'          => 'title',
                'title'         => 'Title', // use translation
                'type'          => 'text',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
            ],
            [
                'name'          => 'description',
                'title'         => 'Description', // use translation
                'type'          => 'textarea',
                'channel_based' => false,
                'locale_based'  => true,
            ],
            [
                'name'          => 'default_rate',
                'title'         => 'Base Rate ($)',
                'type'          => 'text',
                'validation'    => 'required|numeric|min:0',
                'channel_based' => true,
                'locale_based'  => false
            ],
            [
                'name'    => 'type',
                'title'   => 'Pricing Type',
                'type'    => 'select',
                'options' => [
                    [
                        'title' => 'Per Order (Flat Rate)',
                        'value' => 'per_order',
                    ],
                    [
                        'title' => 'Per Item',
                        'value' => 'per_unit',
                    ],
                ],
                'channel_based' => true,
                'locale_based'  => false,
            ],
            [
                'name'          => 'active',
                'title'         => 'Status', // use translation
                'type'          => 'boolean',
                'validation'    => 'required',
                'channel_based' => true,
                'locale_based'  => true,
            ]
        ]
    ]
];