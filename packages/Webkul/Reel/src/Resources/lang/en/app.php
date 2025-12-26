<?php

return [
    'admin' => [
        'reels' => [
            'title'   => 'ريلز',
            'content' => 'إدارة مقاطع الفيديو القصيرة',

            'create' => [
                'title' => 'إنشاء ريل',
            ],

            'edit' => [
                'title' => 'تعديل الريل',
            ],

            'show' => [
                'title'        => 'تفاصيل الريل',
                'general-info' => 'المعلومات العامة',
            ],

            'fields' => [
                'title'           => 'العنوان',
                'caption'         => 'الوصف',
                'product'         => 'المنتج',
                'status'          => 'الحالة',
                'created-at'      => 'تاريخ الإنشاء',
                'video'           => 'الفيديو',
                'thumbnail'       => 'الصورة المصغّرة',
                'duration'        => 'المدة (بالثواني)',
                'is_active'       => 'مفعل',
                'sort_order'      => 'ترتيب العرض',
                'views_count'     => 'عدد المشاهدات',
                'likes_count'     => 'عدد الإعجابات',
                'select-product'  => 'اختر المنتج',
            ],

            'status' => [
                'active'   => 'مفعل',
                'inactive' => 'غير مفعل',
            ],

            'messages' => [
                'create-success'          => 'تم إنشاء الريل بنجاح.',
                'update-success'          => 'تم تحديث الريل بنجاح.',
                'delete-success'          => 'تم حذف الريل بنجاح.',
                'save-btn'                => 'حفظ الريل',
                'update-btn'              => 'تحديث الريل',
                'remove-video'            => 'إزالة الفيديو',
                'remove-thumbnail'        => 'إزالة الصورة المصغّرة',
                'video-size'              => 'الحد الأقصى 100MB — الصيغ المسموحة: MP4, MOV, AVI',
                'thumbnail-size'          => 'الحد الأقصى 5MB — الصيغ المسموحة: JPG, PNG, GIF',
                'invalid-video-type'      => 'يرجى رفع ملف فيديو صالح (MP4, MOV, AVI)',
                'invalid-image-type'      => 'يرجى رفع صورة صالحة',
                'video-size-exceeded'     => 'حجم الفيديو لا يجب أن يتجاوز 100MB',
                'thumbnail-size-exceeded' => 'حجم الصورة لا يجب أن يتجاوز 5MB',
                'video-not-supported'     => 'متصفحك لا يدعم تشغيل الفيديو.',
                'error-occurred'          => 'حدث خطأ ما',
                'load-failed'             => 'فشل تحميل بيانات الريل',
                'video-optional'          => ' (اختياري عند التحديث)',
            ],

            'datagrid' => [
                'id'          => 'المعرف',
                'title'       => 'العنوان',
                'caption'     => 'الوصف',
                'video'       => 'الفيديو',
                'thumbnail'   => 'الصورة المصغّرة',
                'duration'    => 'المدة',
                'status'      => 'الحالة',
                'sort_order'  => 'ترتيب العرض',
                'views'       => 'المشاهدات',
                'likes'       => 'الإعجابات',
                'created_by'  => 'تم الإنشاء بواسطة',
                'product'     => 'المنتج',
                'created_at'  => 'تاريخ الإنشاء',
                'updated_at'  => 'تاريخ التعديل',
                'deleted_at'  => 'تاريخ الحذف',
                'edit'        => 'تعديل',
                'view'        => 'عرض',
                'delete'      => 'حذف',
                'active'      => 'مفعل',
                'inactive'    => 'غير مفعل',
                'na'          => 'غير متوفر',
                'actions'     => 'الإجراءات',
            ],
        ],
    ],
];