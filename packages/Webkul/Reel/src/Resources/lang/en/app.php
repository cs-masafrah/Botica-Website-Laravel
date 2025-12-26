<?php

// return [
//     'admin' => [
//         'reels' => [
//             'title' => 'Reels',
//             'content' => 'Reels Content',
//         ],
//         'datagrid' => [
//             'id' => 'ID',
//             'title' => 'title',
//             'status' => 'Status',
//             'pending' => 'Pending',
//             'approved' => 'Approved',
//             'rejected' => 'Rejected',
//         ],
//     ],
// ];

//
return [
    'admin' => [
        'reels' => [
            'title'   => 'Reels',
            'content' => 'Manage short video reels',

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
                'title'      => 'Title',
                'caption'    => 'Caption',
                'product'    => 'Product',
                'status'     => 'Status',
                'created-at' => 'Created At',
                'video'      => 'Video',
                'thumbnail'  => 'Thumbnail',
                'duration'    => 'Duration in seconds',
                'is_active'   => 'Active',
                'sort_order'  => 'Sort Order',
                'views_count' => 'Views Count',
                'likes_count' => 'Likes Count',
                'select-product' => 'Select Product',
            ],

            'status' => [
                'active'   => 'Active',
                'inactive' => 'Inactive',
            ],

            'messages' => [
                'create-success' => 'Reel created successfully.',
                'update-success' => 'Reel updated successfully.',
                'delete-success' => 'Reel deleted successfully.',
                'save-btn' => 'Save Reel',
                'update-btn' => 'Update Reel',
                'remove-video' => 'Remove Video',
                'remove-thumbnail' => 'Remove Thumbnail',
                'video-size' => 'Max file size: 100MB. Allowed formats: MP4, MOV, AVI',
                'thumbnail-size' => 'Max file size: 5MB. Allowed formats: JPG, PNG, GIF',
                'invalid-video-type' => 'Please upload a valid video file (MP4, MOV, AVI)',
                'invalid-image-type' => 'Please upload a valid image file',
                'video-size-exceeded' => 'Video file size should not exceed 100MB',
                'thumbnail-size-exceeded' => 'Thumbnail file size should not exceed 5MB',
                'video-not-supported' => 'Your browser does not support the video tag.',
                'error-occurred' => 'An error occurred',
                'load-failed' => 'Failed to load reel data',
                'video-optional' => ' (Optional for updates)',
            ],

            'datagrid' => [
                'id'          => 'ID',
                'title'       => 'Title',
                'caption'     => 'Caption',
                'video'       => 'Video',
                'thumbnail'   => 'Thumbnail',
                'duration'    => 'Duration (seconds)',
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
                'actions'     => 'Actions'

            ],
        ],
    ],
];