<?php
// packages/Webkul/AbandonedCart/src/Config/system.php

return [
    /**
     * Top level menu - Abandoned Cart
     */
    [
        'key'  => 'sales.abandoned_cart',
        'name' => 'Abandoned Cart',
        'info' => 'Configure abandoned cart recovery settings',
        'sort' => 6
    ],

    /**
     * Second level - General Settings
     */
    [
        'key'  => 'sales.abandoned_cart.general',
        'name' => 'General Settings',
        'info' => 'General abandoned cart settings',
        'icon' => 'settings/settings.svg',
        'sort' => 1,
        'fields' => [
            [
                'name'          => 'enabled',  // Changed from 'status' to 'enabled'
                'title'         => 'Enable Abandoned Cart',
                'type'          => 'boolean',
                'validation'    => 'required',
                'channel_based' => false,  // Changed to false for global setting
                'locale_based'  => false,
                'default'       => 0,
                'sort_order'    => 1
            ],
            [
                'name'          => 'abandoned_after',
                'title'         => 'Mark as Abandoned After (minutes)',
                'type'          => 'select',
                'options'       => [
                    [
                        'title' => '30 minutes',
                        'value' => 30
                    ],
                    [
                        'title' => '1 hour',
                        'value' => 60
                    ],
                    [
                        'title' => '2 hours',
                        'value' => 120
                    ],
                    [
                        'title' => '4 hours',
                        'value' => 240
                    ],
                    [
                        'title' => '6 hours',
                        'value' => 360
                    ],
                    [
                        'title' => '12 hours',
                        'value' => 720
                    ],
                    [
                        'title' => '24 hours',
                        'value' => 1440
                    ]
                ],
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => false,
                'default'       => 60,
                'sort_order'    => 2
            ],
            [
                'name'          => 'max_reminders',
                'title'         => 'Maximum Reminders',
                'type'          => 'select',
                'options'       => [
                    [
                        'title' => '1',
                        'value' => 1
                    ],
                    [
                        'title' => '2',
                        'value' => 2
                    ],
                    [
                        'title' => '3',
                        'value' => 3
                    ],
                    [
                        'title' => '4',
                        'value' => 4
                    ],
                    [
                        'title' => '5',
                        'value' => 5
                    ]
                ],
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => false,
                'default'       => 3,
                'sort_order'    => 3
            ]
        ]
    ],

    /**
     * Second level - Email Settings
     */
    [
        'key'  => 'sales.abandoned_cart.email',
        'name' => 'Email Settings',
        'info' => 'Configure email settings for abandoned cart reminders',
        'icon' => 'email/email.svg',
        'sort' => 2,
        'fields' => [
            [
                'name'          => 'email_subject',
                'title'         => 'Email Subject',
                'type'          => 'text',
                'validation'    => 'required',
                'channel_based' => true,
                'locale_based'  => true,
                'default'       => 'Did you forget something?',
                'sort_order'    => 1
            ],
            [
                'name'          => 'email_template',
                'title'         => 'Email Template',
                'type'          => 'textarea',
                'validation'    => 'required',
                'channel_based' => true,
                'locale_based'  => true,
                'default'       => 'Hello {customer_name}, you left items in your cart!',
                'sort_order'    => 2
            ]
        ]
    ]
];
