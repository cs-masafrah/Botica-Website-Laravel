<?php
// packages/Webkul/AbandonedCart/src/Resources/lang/ar/app.php

return [
    'admin' => [
        // ---------- Common Translations ----------
        'view' => 'عرض',
        'edit' => 'تعديل',
        'delete' => 'حذف',
        'save' => 'حفظ',
        'cancel' => 'إلغاء',
        'create' => 'إنشاء',
        'update' => 'تحديث',
        'actions' => 'الإجراءات',
        'status' => 'الحالة',
        'active' => 'نشط',
        'inactive' => 'غير نشط',
        'yes' => 'نعم',
        'no' => 'لا',
        'send-email' => 'إرسال بريد إلكتروني',
        'send-email-confirm' => 'إرسال بريد تذكير إلى هذا العميل؟',
        'delete-confirm' => 'هل أنت متأكد أنك تريد حذف هذا؟',

        // ---------- Menu ----------
        'menu' => [
            'abandoned-cart' => 'عربة التسوق المتروكة',
        ],

        // ---------- ACL ----------
        'acl' => [
            'abandoned-cart' => 'عربة التسوق المتروكة',
            'rules' => 'إدارة القواعد',
            'carts' => 'عرض عربات التسوق المتروكة',
            'create' => 'إنشاء قاعدة',
            'edit' => 'تعديل القاعدة',
            'delete' => 'حذف القاعدة',
            'view' => 'عرض',
        ],

        // ---------- CARTS ----------
        'carts' => [
            'title' => 'عربات التسوق المتروكة',
            'id' => 'المعرف',
            'customer' => 'العميل',
            'email' => 'البريد الإلكتروني',
            'items' => 'العناصر',
            'total' => 'المجموع',
            'abandoned-at' => 'تم التخلي عنه في',
            'reminders' => 'التذكيرات',
            'status' => 'الحالة',
            'actions' => 'الإجراءات',
            'converted' => 'تم التحويل',
            'active' => 'نشط',
            'no-records' => 'لم يتم العثور على عربات تسوق متروكة',
        ],

        // ---------- RULES ----------
        'rules' => [
            'title' => 'قواعد عربة التسوق المتروكة',
            'create-title' => 'إنشاء قاعدة',
            'create' => 'إنشاء قاعدة',
            'edit' => 'تعديل القاعدة',
            'id' => 'المعرف',
            'name' => 'الاسم',
            'abandoned-after' => 'متروك بعد',
            'send-after' => 'إرسال بعد',
            'max-reminders' => 'الحد الأقصى للتذكيرات',
            'no-records' => 'لم يتم العثور على قواعد',
        ],

        // ---------- Module ----------
        'abandoned-cart' => [
            'title'   => 'عربة التسوق المتروكة',
            'content' => 'إدارة استعادة عربة التسوق المتروكة',

            'fields' => [
                'name'           => 'اسم القاعدة',
                'abandoned-after' => 'متروك بعد',
                'send-after'     => 'إرسال بعد',
                'max-reminders'  => 'الحد الأقصى للتذكيرات',
                'email-subject'  => 'موضوع البريد الإلكتروني',
                'email-template' => 'قالب البريد الإلكتروني',
            ],

            'messages' => [
                'create-success'  => 'تم إنشاء القاعدة بنجاح.',
                'update-success'  => 'تم تحديث القاعدة بنجاح.',
                'delete-success'  => 'تم حذف القاعدة بنجاح.',
            ],
        ],

        // ---------- System Configuration ----------
        'system' => [
            'abandoned-cart'        => 'عربة التسوق المتروكة',
            'abandoned-cart-info'   => 'تكوين إعدادات استعادة عربة التسوق المتروكة',

            'fields' => [
                'status'         => 'تفعيل عربة التسوق المتروكة',
                'abandoned_after' => 'اعتبارها متروكة بعد',
                'max_reminders'  => 'الحد الأقصى للتذكيرات',
                'email_subject'  => 'موضوع البريد الإلكتروني',
                'email_template' => 'قالب البريد الإلكتروني',
            ],
        ],
    ],
];
