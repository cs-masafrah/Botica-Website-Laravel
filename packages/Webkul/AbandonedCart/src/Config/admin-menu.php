<?php
// packages/Webkul/AbandonedCart/src/Config/admin-menu.php

// return [
//     'abandoned-cart' => [
//         'key' => 'abandoned-cart',
//         'name' => 'Abandoned Cart',
//         'route' => 'admin.abandoned_cart.rules.index',
//         'sort' => 5,
//         'icon' => 'cart',  // Add this line - Bagisto icon name
//         'children' => [
//             [
//                 'key' => 'abandoned-cart.rules',
//                 'name' => 'Rules',
//                 'route' => 'admin.abandoned_cart.rules.index',
//                 'sort' => 1,
//                 'icon' => 'settings',  // Add icon for child items too
//             ],
//             [
//                 'key' => 'abandoned-cart.carts',
//                 'name' => 'Abandoned Carts',
//                 'route' => 'admin.abandoned_cart.carts.index',
//                 'sort' => 2,
//                 'icon' => 'view-list',  // Add icon for child items too
//             ]
//         ]
//     ]
// ];


return [
    [
        'key'   => 'abandoned-cart',
        'name'  => 'Abandoned Cart',
        'route' => 'admin.abandoned_cart.rules.index',
        'sort'  => 8,  // Different from reel's sort 7
        'icon'  => 'icon-cart',  // Use icon-cart instead of just cart
    ]
];
