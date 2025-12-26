<?php

// return [
//     'admin' => [
//         'reels' => [
//             'title'   => 'الريلز',
//             'content' => 'محتوى الريلز',
//         ],
//         'datagrid' => [
//             'id' => 'الرمز',
//             'title' => 'العنوان',
//             'status'   => 'الحالة',
//             'pending'  => 'قيد الانتظار',
//             'approved' => 'موافق عليه',
//             'rejected' => 'مرفوض',
//         ],
//     ],
// ];<?php

return [
    'admin' => [
        'reels' => [
            'title'   => 'الريلز',
            'content' => 'إدارة مقاطع الفيديو القصيرة',

            'create' => [
                'title' => 'إضافة ريل',
            ],

            'edit' => [
                'title' => 'تعديل الريل',
            ],

            'show' => [
                'title'        => 'تفاصيل الريل',
                'general-info' => 'المعلومات العامة',
            ],

            'fields' => [
                'title'      => 'العنوان',
                'caption'    => 'الوصف',
                'product'    => 'المنتج',
                'status'     => 'الحالة',
                'created-at' => 'تاريخ الإنشاء',
                'video'      => 'الفيديو',
                'thumbnail'  => 'الصورة المصغرة',
                'duration'    => 'المدة بالثواني',
                'is_active'   => 'نشط',
                'sort_order'  => 'ترتيب الفرز',
                'views_count' => 'عدد المشاهدات',
                'likes_count' => 'عدد الإعجابات',
            ],

            'status' => [
                'active'   => 'نشط',
                'inactive' => 'غير نشط',
            ],

            'messages' => [
                'create-success' => 'تم إنشاء الريل بنجاح.',
                'update-success' => 'تم تحديث الريل بنجاح.',
                'delete-success' => 'تم حذف الريل بنجاح.',
                'video-optional' => 'تحديث الفيديو اختياري',
            ],
            'datagrid' => [
                'id'          => 'المعرف',
                'title'       => 'العنوان',
                'caption'     => 'الوصف',
                'video'       => 'الفيديو',
                'thumbnail'   => 'الصورة المصغرة',
                'duration'    => 'المدة (بالثواني)',
                'status'      => 'الحالة',
                'sort_order'  => 'ترتيب الفرز',
                'views'       => 'عدد المشاهدات',
                'likes'       => 'عدد الإعجابات',
                'created_by'  => 'أنشئ بواسطة',
                'product'     => 'المنتج',
                'created_at'  => 'تاريخ الإنشاء',
                'updated_at'  => 'تاريخ التحديث',
                'deleted_at'  => 'تاريخ الحذف',
                'edit'        => 'تعديل',
                'view'        => 'عرض',
                'delete'      => 'حذف',
                'active'      => 'نشط',
                'inactive'    => 'غير نشط',
                'actions'     => 'الاجراءات'
            ],
        ],
    ],
];