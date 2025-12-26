<?php

return [
    'admin' => [

        // ---------- Menu ----------
        'menu' => [
            'reels' => 'Reels',
        ],

        // ---------- ACL ----------
        'acl' => [
            'reel'        => 'Reels',
            'list'         => 'List Reels',
            'create'       => 'Create Reel',
            'edit'         => 'Edit Reel',
            'delete'       => 'Delete Reel',
            'view'         => 'View',
        ],

        // ---------- Module ----------
        'reels' => [
            'title'   => 'Reels',
            'content' => 'Manage short video clips',

            'create' => [
                'title' => 'Create Reel',
            ],

            'edit' => [
                'title' => 'Edit Reel',
            ],

            'show' => [
                'title'        => 'Reel Details',
                'general-info' => 'General Information',
            ],

            'fields' => [
                'title'           => 'Title',
                'caption'         => 'Caption',
                'product'         => 'Product',
                'status'          => 'Status',
                'created-at'      => 'Created At',
                'video'           => 'Video',
                'thumbnail'       => 'Thumbnail',
                'duration'        => 'Duration (seconds)',
                'is_active'       => 'Active',
                'sort_order'      => 'Sort Order',
                'views_count'     => 'Views Count',
                'likes_count'     => 'Likes Count',
                'select-product'  => 'Select Product',
            ],

            'status' => [
                'active'   => 'Active',
                'inactive' => 'Inactive',
            ],

            'messages' => [
                'unauthorized'     => 'Unauthorized access.',
                'create-success'          => 'Reel created successfully.',
                'update-success'          => 'Reel updated successfully.',
                'delete-success'          => 'Reel deleted successfully.',
                'save-btn'                => 'Save Reel',
                'update-btn'              => 'Update Reel',
                'remove-video'            => 'Remove Video',
                'remove-thumbnail'        => 'Remove Thumbnail',
                'video-size'              => 'Max size 100MB — Allowed: MP4, MOV, AVI',
                'thumbnail-size'          => 'Max size 5MB — Allowed: JPG, PNG, GIF',
                'invalid-video-type'      => 'Please upload a valid video file (MP4, MOV, AVI)',
                'invalid-image-type'      => 'Please upload a valid image file',
                'video-size-exceeded'     => 'Video must not exceed 100MB',
                'thumbnail-size-exceeded' => 'Thumbnail must not exceed 5MB',
                'video-not-supported'     => 'Your browser does not support video playback.',
                'error-occurred'          => 'Something went wrong',
                'load-failed'             => 'Failed to load reel data',
                'video-optional'          => '(Optional when updating)',
            ],

            'datagrid' => [
                'id'          => 'ID',
                'title'       => 'Title',
                'caption'     => 'Caption',
                'video'       => 'Video',
                'thumbnail'   => 'Thumbnail',
                'duration'    => 'Duration',
                'status'      => 'Status',
                'sort_order'  => 'Sort Order',
                'views'       => 'Views',
                'likes'       => 'Likes',
                'created_by'  => 'Created By',
                'product'     => 'Product',
                'created_at'  => 'Created At',
                'updated_at'  => 'Updated At',
                'deleted_at'  => 'Deleted At',
                'edit'        => 'Edit',
                'view'        => 'View',
                'delete'      => 'Delete',
                'active'      => 'Active',
                'inactive'    => 'Inactive',
                'na'          => 'N/A',
                'actions'     => 'Actions',
            ],
        ],

        'system' => [
            'reels'        => 'Reels',
            'reels-info'   => 'Reels module settings',

            'settings'      => 'General Settings',
            'settings-info' => 'Configure Reels behavior and options',

            'general'      => 'Reels Configuration',
            'general-info' => 'General settings for the Reels module',

            'fields' => [
                'enable'         => 'Enable Reels',
                'max-duration'   => 'Maximum Video Duration (seconds)',
                'allow-likes'    => 'Allow Likes',
                'allow-comments' => 'Allow Comments',
                'items-per-page' => 'Reels Per Page',
            ],
        ],
    ],
];