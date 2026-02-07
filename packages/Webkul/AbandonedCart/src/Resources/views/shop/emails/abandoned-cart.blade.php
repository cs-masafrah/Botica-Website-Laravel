<!DOCTYPE html>
<html>
<head>
    <title>{{ $rule->email_subject }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f8f9fa; padding: 20px; text-align: center; border-radius: 5px; }
        .content { padding: 30px 20px; }
        .cart-items { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .cart-items th { background: #f8f9fa; padding: 10px; text-align: left; }
        .cart-items td { padding: 10px; border-bottom: 1px solid #ddd; }
        .button { display: inline-block; background: #3490dc; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .coupon { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Did You Forget Something?</h1>
        </div>

        <div class="content">
            <p>Hi {{ $customerName }},</p>

            <p>We noticed you left some items in your shopping cart. They're still available and waiting for you!</p>

            <h3>Your Cart Items:</h3>
            <table class="cart-items">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cartItems as $item)
                    <tr>
                        <td>{{ $item['name'] }}</td>
                        <td>{{ core()->formatPrice($item['price']) }}</td>
                        <td>{{ $item['quantity'] }}</td>
                        <td>{{ core()->formatPrice($item['total']) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="text-align: center; margin: 30px 0;">
                <h3>Cart Total: {{ core()->formatPrice($cart->cart_total) }}</h3>

                @if($rule->include_coupon && $rule->coupon_code)
                <div class="coupon">
                    <h4>Special Offer Just For You!</h4>
                    <p>Use coupon code: <strong style="font-size: 18px;">{{ $rule->coupon_code }}</strong></p>
                    <p>Get {{ $rule->discount_type == 'percentage' ? $rule->discount_amount.'%' : core()->formatPrice($rule->discount_amount) }} off your purchase!</p>
                    <p><small>Valid for 7 days</small></p>
                </div>
                @endif

                <a href="{{ $recoverUrl }}" class="button">
                    Complete Your Purchase Now
                </a>

                <p style="margin-top: 20px; font-size: 14px; color: #666;">
                    This link will expire in 7 days. If you no longer wish to receive these reminders,
                    <a href="{{ route('shop.abandoned_cart.unsubscribe', ['token' => 'unsubscribe_token_here']) }}">unsubscribe here</a>.
                </p>
            </div>
        </div>

        <div class="footer">
            <p>{{ config('app.name') }} | © {{ date('Y') }} All rights reserved.</p>
            <p><a href="{{ route('shop.home.index') }}">Visit our store</a></p>
        </div>
    </div>
</body>
</html>
