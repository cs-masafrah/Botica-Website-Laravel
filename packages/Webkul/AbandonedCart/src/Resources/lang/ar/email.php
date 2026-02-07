<?php
// packages/Webkul/AbandonedCart/src/Resources/lang/ar/email.php

return [
    'abandoned-cart' => [
        'subject' => 'هل نسيت شيئًا؟',
        'template' => 'مرحبًا {customer_name},<br><br>
                      لقد تركت عناصر في سلة التسوق الخاصة بك!<br><br>
                      عناصر السلة: {cart_items}<br>
                      إجمالي السلة: {cart_total}<br><br>
                      <a href="{recover_url}">انقر هنا لإكمال عملية الشراء</a><br><br>
                      شكرًا لك,<br>
                      {store_name}'
    ]
];
