{{-- packages/Webkul/AbandonedCart/src/Resources/views/admin/carts/index.blade.php --}}
<x-admin::layouts>

    <x-slot:title>
        @lang('abandonedcart::app.admin.carts.title')
    </x-slot:title>

    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('abandonedcart::app.admin.carts.title')
        </h1>
    </div>

    <div class="bg-white rounded dark:bg-gray-900 box-shadow">
        <div class="overflow-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b bg-gray-50 dark:bg-gray-800 dark:border-gray-700">
                        <th class="px-4 py-3 text-sm font-semibold text-left text-gray-600 dark:text-gray-300">
                            @lang('abandonedcart::app.admin.carts.id')
                        </th>
                        <th class="px-4 py-3 text-sm font-semibold text-left text-gray-600 dark:text-gray-300">
                            @lang('abandonedcart::app.admin.carts.customer')
                        </th>
                        <th class="px-4 py-3 text-sm font-semibold text-left text-gray-600 dark:text-gray-300">
                            @lang('abandonedcart::app.admin.carts.email')
                        </th>
                        <th class="px-4 py-3 text-sm font-semibold text-left text-gray-600 dark:text-gray-300">
                            @lang('abandonedcart::app.admin.carts.items')
                        </th>
                        <th class="px-4 py-3 text-sm font-semibold text-left text-gray-600 dark:text-gray-300">
                            @lang('abandonedcart::app.admin.carts.total')
                        </th>
                        <th class="px-4 py-3 text-sm font-semibold text-left text-gray-600 dark:text-gray-300">
                            @lang('abandonedcart::app.admin.carts.abandoned-at')
                        </th>
                        <th class="px-4 py-3 text-sm font-semibold text-left text-gray-600 dark:text-gray-300">
                            @lang('abandonedcart::app.admin.carts.reminders')
                        </th>
                        <th class="px-4 py-3 text-sm font-semibold text-left text-gray-600 dark:text-gray-300">
                            @lang('abandonedcart::app.admin.carts.status')
                        </th>
                        <th class="px-4 py-3 text-sm font-semibold text-left text-gray-600 dark:text-gray-300">
                            @lang('abandonedcart::app.admin.carts.actions')
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($carts as $cart)
                    <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                            #{{ $cart->id }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-800 dark:text-white">
                            {{ $cart->customer_full_name }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                            {{ $cart->customer_email }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                            {{ $cart->items_count }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                            {{ core()->formatPrice($cart->cart_total) }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                            {{ $cart->abandoned_at ? $cart->abandoned_at->format('Y-m-d H:i') : 'N/A' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                            {{ $cart->reminder_count }}
                        </td>
                        <td class="px-4 py-3">
                            @if($cart->is_converted)
                            <span class="px-2 py-1 text-xs text-green-800 bg-green-100 rounded-full dark:bg-green-900 dark:text-green-200">
                                @lang('abandonedcart::app.admin.carts.converted')
                            </span>
                            @else
                            <span class="px-2 py-1 text-xs text-yellow-800 bg-yellow-100 rounded-full dark:bg-yellow-900 dark:text-yellow-200">
                                @lang('abandonedcart::app.admin.carts.active')
                            </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.abandoned_cart.carts.view', $cart->id) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300" title="@lang('abandonedcart::app.admin.view')">
                                    <span class="icon-eye"></span>
                                </a>

                                <form action="{{ route('admin.abandoned_cart.carts.send_email', $cart->id) }}" method="POST" onsubmit="return confirm('@lang('abandonedcart::app.admin.send-email-confirm')')" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300" title="@lang('abandonedcart::app.admin.send-email')">
                                        <span class="icon-email"></span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                            @lang('abandonedcart::app.admin.carts.no-records')
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-admin::layouts>
