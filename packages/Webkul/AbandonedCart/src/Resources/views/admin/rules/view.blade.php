{{-- packages/Webkul/AbandonedCart/src/Resources/views/admin/carts/view.blade.php --}}
<x-admin::layouts>

    <x-slot:title>
        Abandoned Cart Details
    </x-slot:title>

    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold text-gray-800 dark:text-white">
            Abandoned Cart Details #{{ $cart->id }}
        </h1>

        <div class="flex gap-2">
            <a href="{{ route('admin.abandoned_cart.carts.index') }}" class="secondary-button">
                Back to Carts
            </a>

            <form action="{{ route('admin.abandoned_cart.carts.send_email', $cart->id) }}" method="POST" onsubmit="return confirm('Send reminder email to this customer?')" class="inline">
                @csrf
                <button type="submit" class="primary-button">
                    Send Reminder Email
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6">
        <!-- Customer Information -->
        <div class="p-6 bg-white rounded dark:bg-gray-900 box-shadow">
            <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                Customer Information
            </h2>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">Customer Name</p>
                    <p class="text-gray-800 dark:text-white">{{ $cart->customer_full_name }}</p>
                </div>

                <div>
                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">Customer Email</p>
                    <p class="text-gray-800 dark:text-white">{{ $cart->customer_email }}</p>
                </div>

                @if($cart->customer_id)
                <div>
                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">Customer ID</p>
                    <p class="text-gray-800 dark:text-white">{{ $cart->customer_id }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Cart Information -->
        <div class="p-6 bg-white rounded dark:bg-gray-900 box-shadow">
            <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                Cart Information
            </h2>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">Cart Total</p>
                    <p class="text-gray-800 dark:text-white">{{ core()->formatPrice($cart->cart_total) }}</p>
                </div>

                <div>
                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">Items Count</p>
                    <p class="text-gray-800 dark:text-white">{{ $cart->items_count }}</p>
                </div>

                <div>
                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">Status</p>
                    <span class="px-2 py-1 text-xs rounded-full {{ $cart->is_converted ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                        {{ $cart->is_converted ? 'Converted' : 'Active' }}
                    </span>
                </div>

                <div>
                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">Abandoned At</p>
                    <p class="text-gray-800 dark:text-white">{{ $cart->abandoned_at ? $cart->abandoned_at->format('Y-m-d H:i:s') : 'N/A' }}</p>
                </div>

                <div>
                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">Last Reminder Sent</p>
                    <p class="text-gray-800 dark:text-white">{{ $cart->last_reminder_sent_at ? $cart->last_reminder_sent_at->format('Y-m-d H:i:s') : 'Never' }}</p>
                </div>

                <div>
                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">Reminder Count</p>
                    <p class="text-gray-800 dark:text-white">{{ $cart->reminder_count }}</p>
                </div>

                @if($cart->converted_at)
                <div>
                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">Converted At</p>
                    <p class="text-gray-800 dark:text-white">{{ $cart->converted_at->format('Y-m-d H:i:s') }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Cart Items -->
        @if($cart->cart_items && is_array($cart->cart_items) && count($cart->cart_items) > 0)
        <div class="p-6 bg-white rounded dark:bg-gray-900 box-shadow">
            <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                Cart Items ({{ count($cart->cart_items) }})
            </h2>

            <div class="overflow-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-gray-50 dark:bg-gray-800 dark:border-gray-700">
                            <th class="px-4 py-3 text-sm font-semibold text-left text-gray-600 dark:text-gray-300">
                                Product
                            </th>
                            <th class="px-4 py-3 text-sm font-semibold text-left text-gray-600 dark:text-gray-300">
                                SKU
                            </th>
                            <th class="px-4 py-3 text-sm font-semibold text-left text-gray-600 dark:text-gray-300">
                                Price
                            </th>
                            <th class="px-4 py-3 text-sm font-semibold text-left text-gray-600 dark:text-gray-300">
                                Quantity
                            </th>
                            <th class="px-4 py-3 text-sm font-semibold text-left text-gray-600 dark:text-gray-300">
                                Total
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cart->cart_items as $item)
                        <tr class="border-b dark:border-gray-700">
                            <td class="px-4 py-3 text-sm text-gray-800 dark:text-white">
                                {{ $item['name'] ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                {{ $item['sku'] ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                {{ core()->formatPrice($item['price'] ?? 0) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                {{ $item['quantity'] ?? 0 }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                {{ core()->formatPrice($item['total'] ?? 0) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="p-6 bg-white rounded dark:bg-gray-900 box-shadow">
            <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                Cart Items
            </h2>
            <p class="text-gray-500 dark:text-gray-400">No cart items data available</p>
        </div>
        @endif
    </div>

</x-admin::layouts>
