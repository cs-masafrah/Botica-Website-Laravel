<?php
// packages/Webkul/AbandonedCart/src/Resources/lang/en/app.php

return [
    'admin' => [
        // ---------- Common Translations ----------
        'view' => 'View',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'save' => 'Save',
        'cancel' => 'Cancel',
        'create' => 'Create',
        'update' => 'Update',
        'actions' => 'Actions',
        'status' => 'Status',
        'active' => 'Active',
        'inactive' => 'Inactive',
        'yes' => 'Yes',
        'no' => 'No',
        'send-email' => 'Send Email',
        'send-email-confirm' => 'Send reminder email to this customer?',
        'delete-confirm' => 'Are you sure you want to delete this?',

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

        // ---------- CARTS ----------
        'carts' => [
            'title' => 'Abandoned Carts',
            'id' => 'ID',
            'customer' => 'Customer',
            'email' => 'Email',
            'items' => 'Items',
            'total' => 'Total',
            'abandoned-at' => 'Abandoned At',
            'reminders' => 'Reminders',
            'status' => 'Status',
            'actions' => 'Actions',
            'converted' => 'Converted',
            'active' => 'Active',
            'no-records' => 'No abandoned carts found',
        ],

        // ---------- RULES ----------
        'rules' => [
            'title' => 'Abandoned Cart Rules',
            'create-title' => 'Create Rule',
            'create' => 'Create Rule',
            'edit' => 'Edit Rule',
            'id' => 'ID',
            'name' => 'Name',
            'abandoned-after' => 'Abandoned After',
            'send-after' => 'Send After',
            'max-reminders' => 'Max Reminders',
            'no-records' => 'No rules found',
        ],

        // ---------- Module ----------
        'abandoned-cart' => [
            'title'   => 'Abandoned Cart',
            'content' => 'Manage abandoned cart recovery',

            'fields' => [
                'name'           => 'Rule Name',
                'abandoned-after' => 'Abandoned After',
                'send-after'     => 'Send After',
                'max-reminders'  => 'Max Reminders',
                'email-subject'  => 'Email Subject',
                'email-template' => 'Email Template',
            ],

            'messages' => [
                'create-success'  => 'Rule created successfully.',
                'update-success'  => 'Rule updated successfully.',
                'delete-success'  => 'Rule deleted successfully.',
            ],
        ],

        // ---------- System Configuration ----------
        'system' => [
            'abandoned-cart'        => 'Abandoned Cart',
            'abandoned-cart-info'   => 'Configure abandoned cart recovery settings',

            'fields' => [
                'status'         => 'Enable Abandoned Cart',
                'abandoned_after' => 'Mark as Abandoned After',
                'max_reminders'  => 'Maximum Reminders',
                'email_subject'  => 'Email Subject',
                'email_template' => 'Email Template',
            ],
        ],
    ],
];
