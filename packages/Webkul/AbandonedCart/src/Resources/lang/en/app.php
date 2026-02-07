<?php
// packages/Webkul/AbandonedCart/src/Resources/lang/en/app.php

return [
    'admin' => [
        // ---------- Menu ----------
        'menu' => [
            'abandoned-cart' => 'Abandoned Cart',
        ],

        // ---------- ACL ----------
        'acl' => [
            'abandoned-cart' => 'Abandoned Cart',
            'rules' => 'Manage Rules',
            'carts' => 'View Abandoned Carts',
            'create' => 'Create Rule',
            'edit' => 'Edit Rule',
            'delete' => 'Delete Rule',
            'view' => 'View',
        ],

        // ---------- Module ----------
        'abandoned-cart' => [
            'title'   => 'Abandoned Cart',
            'content' => 'Manage abandoned cart recovery',

            'create' => [
                'title' => 'Create Rule',
            ],

            'edit' => [
                'title' => 'Edit Rule',
            ],

            'show' => [
                'title'        => 'Rule Details',
                'general-info' => 'General Information',
            ],

            'fields' => [
                'name'           => 'Rule Name',
                'status'         => 'Status',
                'abandoned-after' => 'Abandoned After',
                'send-after'     => 'Send After',
                'max-reminders'  => 'Max Reminders',
                'email-subject'  => 'Email Subject',
                'email-template' => 'Email Template',
                'include-coupon' => 'Include Coupon',
                'coupon-code'    => 'Coupon Code',
                'discount-type'  => 'Discount Type',
                'discount-amount' => 'Discount Amount',
                'created-at'     => 'Created At',
            ],

            'status' => [
                'active'   => 'Active',
                'inactive' => 'Inactive',
            ],

            'discount-types' => [
                'percentage' => 'Percentage',
                'fixed'      => 'Fixed Amount',
            ],

            'messages' => [
                'create-success'  => 'Rule created successfully.',
                'update-success'  => 'Rule updated successfully.',
                'delete-success'  => 'Rule deleted successfully.',
                'save-btn'        => 'Save Rule',
                'update-btn'      => 'Update Rule',
                'error-occurred'  => 'Something went wrong',
                'load-failed'     => 'Failed to load rule data',
            ],

            'datagrid' => [
                'id'          => 'ID',
                'name'        => 'Name',
                'status'      => 'Status',
                'abandoned-after' => 'Abandoned After',
                'send-after'  => 'Send After',
                'max-reminders' => 'Max Reminders',
                'created_at'  => 'Created At',
                'updated_at'  => 'Updated At',
                'edit'        => 'Edit',
                'view'        => 'View',
                'delete'      => 'Delete',
                'active'      => 'Active',
                'inactive'    => 'Inactive',
                'actions'     => 'Actions',
            ],
        ],

        // ---------- System Configuration ----------
        'system' => [
            'abandoned-cart'        => 'Abandoned Cart',
            'abandoned-cart-info'   => 'Configure abandoned cart recovery settings',

            'settings'      => 'General Settings',
            'settings-info' => 'Configure abandoned cart behavior and options',

            'general'      => 'Abandoned Cart Configuration',
            'general-info' => 'General settings for abandoned cart recovery',

            'email'      => 'Email Settings',
            'email-info' => 'Email configuration for abandoned cart reminders',

            'fields' => [
                'status'         => 'Enable Abandoned Cart',
                'abandoned_after' => 'Mark as Abandoned After',
                'max_reminders'  => 'Maximum Reminders',
                'email_subject'  => 'Email Subject',
                'email_template' => 'Email Template',
            ],

            'options' => [
                'minutes' => [
                    '30' => '30 minutes',
                    '60' => '1 hour',
                    '120' => '2 hours',
                    '240' => '4 hours',
                    '360' => '6 hours',
                    '720' => '12 hours',
                    '1440' => '24 hours',
                ],
                'reminders' => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                ],
            ],
        ],
    ],
];
