<?php
// packages/Webkul/AbandonedCart/src/Resources/lang/en/email.php

return [
    'abandoned-cart' => [
        'subject' => 'Did you forget something?',
        'template' => 'Hello {customer_name},<br><br>
                      You left items in your shopping cart!<br><br>
                      Cart Items: {cart_items}<br>
                      Cart Total: {cart_total}<br><br>
                      <a href="{recover_url}">Click here to complete your purchase</a><br><br>
                      Thank you,<br>
                      {store_name}'
    ]
];
