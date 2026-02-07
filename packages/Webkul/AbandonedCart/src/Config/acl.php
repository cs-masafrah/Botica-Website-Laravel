<?php
// packages/Webkul/AbandonedCart/src/Config/acl.php

// return [
//     'abandoned-cart' => [
//         'key' => 'abandoned-cart',
//         'name' => 'Abandoned Cart',
//         'route' => 'admin.abandoned_cart.rules.index',
//         'sort' => 5,
//         'icon' => 'cart',
//         'children' => [
//             [
//                 'key' => 'abandoned-cart.rules',
//                 'name' => 'Rules',
//                 'route' => 'admin.abandoned_cart.rules.index',
//                 'sort' => 1,
//                 'icon' => 'settings',
//             ],
//             [
//                 'key' => 'abandoned-cart.carts',
//                 'name' => 'Abandoned Carts',
//                 'route' => 'admin.abandoned_cart.carts.index',
//                 'sort' => 2,
//                 'icon' => 'view-list',
//             ]
//         ]
//     ]
// ];


return [
    [
        'key'   => 'abandoned-cart',
        'name'  => 'Abandoned Cart',
        'route' => 'admin.abandoned_cart.rules.index',
        'sort'  => 8,
        'icon'  => 'icon-cart',
    ]
];
