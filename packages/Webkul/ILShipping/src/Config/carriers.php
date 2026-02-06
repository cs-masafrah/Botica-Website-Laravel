<?php

return [
    'ilshipping' => [
        'code'         => 'ilshipping',
        'title'        => 'ILShipping',
        'description'  => 'ILShipping',
        'active'       => true,
        'default_rate' => '70',
        'type'         => 'per_order',
        'class'        => 'Webkul\ILShipping\Carriers\ILShipping',
    ],
];