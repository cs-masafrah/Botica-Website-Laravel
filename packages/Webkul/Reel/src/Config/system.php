<?php

return [
    [
        'key'  => 'reel',
        'name' => 'reel::app.admin.system.reels',
        'info' => 'reel::app.admin.system.reels-info',
        'sort' => 1
    ],

    [
        'key'  => 'reels.settings',
        'name' => 'reel::app.admin.system.settings',
        'info' => 'reel::app.admin.system.settings-info',
        'icon' => 'settings/settings.svg',
        'sort' => 1,
    ],

    [
        'key'    => 'reels.settings.general',
        'name'   => 'reel::app.admin.system.general',
        'info'   => 'reel::app.admin.system.general-info',
        'sort'   => 1,

        'fields' => [
            [
                'name'  => 'enable',
                'title' => 'reel::app.admin.system.fields.enable',
                'type'  => 'boolean',
            ],

            [
                'name'       => 'max_duration',
                'title'      => 'reel::app.admin.system.fields.max-duration',
                'type'       => 'number',
                'validation' => 'numeric|min:5|max:300',
            ],

            [
                'name'  => 'allow_likes',
                'title' => 'reel::app.admin.system.fields.allow-likes',
                'type'  => 'boolean',
            ],

            [
                'name'  => 'allow_comments',
                'title' => 'reel::app.admin.system.fields.allow-comments',
                'type'  => 'boolean',
            ],

            [
                'name'       => 'items_per_page',
                'title'      => 'reel::app.admin.system.fields.items-per-page',
                'type'       => 'number',
                'validation' => 'numeric|min:1|max:50',
            ],
        ],
    ],
];