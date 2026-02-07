<?php
// packages/Webkul/AbandonedCart/src/Resources/lang/ar/app.php

return [
    'admin' => [
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

        // ---------- Module ----------
        'abandoned-cart' => [
            'title'   => 'عربة التسوق المتروكة',
            'content' => 'إدارة استعادة عربة التسوق المتروكة',

            'create' => [
                'title' => 'إنشاء قاعدة',
            ],

            'edit' => [
                'title' => 'تعديل القاعدة',
            ],

            'show' => [
                'title'        => 'تفاصيل القاعدة',
                'general-info' => 'معلومات عامة',
            ],

            'fields' => [
                'name'           => 'اسم القاعدة',
                'status'         => 'الحالة',
                'abandoned-after' => 'متروك بعد',
                'send-after'     => 'إرسال بعد',
                'max-reminders'  => 'الحد الأقصى للتذكيرات',
                'email-subject'  => 'موضوع البريد الإلكتروني',
                'email-template' => 'قالب البريد الإلكتروني',
                'include-coupon' => 'تضمين كوبون',
                'coupon-code'    => 'رمز الكوبون',
                'discount-type'  => 'نوع الخصم',
                'discount-amount' => 'مبلغ الخصم',
                'created-at'     => 'تاريخ الإنشاء',
            ],

            'status' => [
                'active'   => 'نشط',
                'inactive' => 'غير نشط',
            ],

            'discount-types' => [
                'percentage' => 'النسبة المئوية',
                'fixed'      => 'مبلغ ثابت',
            ],

            'messages' => [
                'create-success'  => 'تم إنشاء القاعدة بنجاح.',
                'update-success'  => 'تم تحديث القاعدة بنجاح.',
                'delete-success'  => 'تم حذف القاعدة بنجاح.',
                'save-btn'        => 'حفظ القاعدة',
                'update-btn'      => 'تحديث القاعدة',
                'error-occurred'  => 'حدث خطأ ما',
                'load-failed'     => 'فشل تحميل بيانات القاعدة',
            ],

            'datagrid' => [
                'id'          => 'المعرف',
                'name'        => 'الاسم',
                'status'      => 'الحالة',
                'abandoned-after' => 'متروك بعد',
                'send-after'  => 'إرسال بعد',
                'max-reminders' => 'الحد الأقصى للتذكيرات',
                'created_at'  => 'تاريخ الإنشاء',
                'updated_at'  => 'تاريخ التحديث',
                'edit'        => 'تعديل',
                'view'        => 'عرض',
                'delete'      => 'حذف',
                'active'      => 'نشط',
                'inactive'    => 'غير نشط',
                'actions'     => 'الإجراءات',
            ],
        ],

        // ---------- System Configuration ----------
        'system' => [
            'abandoned-cart'        => 'عربة التسوق المتروكة',
            'abandoned-cart-info'   => 'تكوين إعدادات استعادة عربة التسوق المتروكة',

            'settings'      => 'الإعدادات العامة',
            'settings-info' => 'تكوين سلوك عربة التسوق المتروكة والخيارات',

            'general'      => 'تكوين عربة التسوق المتروكة',
            'general-info' => 'الإعدادات العامة لاستعادة عربة التسوق المتروكة',

            'email'      => 'إعدادات البريد الإلكتروني',
            'email-info' => 'تكوين البريد الإلكتروني لتذكيرات عربة التسوق المتروكة',

            'fields' => [
                'status'         => 'تفعيل عربة التسوق المتروكة',
                'abandoned_after' => 'اعتبارها متروكة بعد',
                'max_reminders'  => 'الحد الأقصى للتذكيرات',
                'email_subject'  => 'موضوع البريد الإلكتروني',
                'email_template' => 'قالب البريد الإلكتروني',
            ],

            'options' => [
                'minutes' => [
                    '30' => '30 دقيقة',
                    '60' => 'ساعة واحدة',
                    '120' => 'ساعتان',
                    '240' => '4 ساعات',
                    '360' => '6 ساعات',
                    '720' => '12 ساعة',
                    '1440' => '24 ساعة',
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
