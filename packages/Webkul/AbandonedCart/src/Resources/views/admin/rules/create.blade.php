{{-- packages/Webkul/AbandonedCart/src/Resources/views/admin/rules/create.blade.php --}}
<x-admin::layouts>

    <x-slot:title>
        Create Abandoned Cart Rule
    </x-slot:title>

    <form method="POST" action="{{ route('admin.abandoned_cart.rules.store') }}" class="max-w-4xl p-6 bg-white rounded dark:bg-gray-900 box-shadow">
        @csrf

        <div class="mb-6">
            <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                Rule Information
            </h2>

            <div class="space-y-4">
                <!-- Name -->
                <div>
                    <label class="block mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300 required">
                        Rule Name
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" class="w-full px-3 py-2 border rounded dark:bg-gray-800 dark:border-gray-700" required>
                    @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label class="block mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300 required">
                        Status
                    </label>
                    <select name="status" class="w-full px-3 py-2 border rounded dark:bg-gray-800 dark:border-gray-700" required>
                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>
                            Active
                        </option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>
                            Inactive
                        </option>
                    </select>
                </div>

                <!-- Abandoned After -->
                <div>
                    <label class="block mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300 required">
                        Abandoned After (minutes)
                    </label>
                    <input type="number" name="abandoned_after_minutes" value="{{ old('abandoned_after_minutes', 60) }}" min="1" class="w-full px-3 py-2 border rounded dark:bg-gray-800 dark:border-gray-700" required>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Time in minutes after which a cart is considered abandoned
                    </p>
                </div>

                <!-- Send After -->
                <div>
                    <label class="block mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300 required">
                        Send After (minutes)
                    </label>
                    <input type="number" name="send_after_minutes" value="{{ old('send_after_minutes', 1440) }}" min="1" class="w-full px-3 py-2 border rounded dark:bg-gray-800 dark:border-gray-700" required>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Time in minutes after abandonment to send first reminder
                    </p>
                </div>

                <!-- Max Reminders -->
                <div>
                    <label class="block mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300 required">
                        Maximum Reminders
                    </label>
                    <input type="number" name="max_reminders" value="{{ old('max_reminders', 3) }}" min="1" max="10" class="w-full px-3 py-2 border rounded dark:bg-gray-800 dark:border-gray-700" required>
                </div>
            </div>
        </div>

        <div class="mb-6">
            <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                Email Settings
            </h2>

            <div class="space-y-4">
                <!-- Email Subject -->
                <div>
                    <label class="block mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300 required">
                        Email Subject
                    </label>
                    <input type="text" name="email_subject" value="{{ old('email_subject', 'Did you forget something?') }}" class="w-full px-3 py-2 border rounded dark:bg-gray-800 dark:border-gray-700" required>
                </div>

                <!-- Email Template -->
                <div>
                    <label class="block mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                        Email Template (HTML)
                    </label>
                    <textarea name="email_template" rows="6" class="w-full px-3 py-2 border rounded dark:bg-gray-800 dark:border-gray-700">{{ old('email_template') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Use {customer_name}, {cart_items}, {cart_total}, {recover_url} as variables
                    </p>
                </div>
            </div>
        </div>

        <div class="mb-6">
            <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                Coupon Settings
            </h2>

            <div class="space-y-4">
                <!-- Include Coupon -->
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="include_coupon" name="include_coupon" value="1" {{ old('include_coupon') ? 'checked' : '' }} onchange="toggleCouponFields()">
                    <label for="include_coupon" class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                        Include Coupon in Reminder
                    </label>
                </div>

                <!-- Coupon Fields (hidden by default) -->
                <div id="coupon-fields" class="space-y-4" style="{{ old('include_coupon') ? '' : 'display: none;' }}">
                    <!-- Coupon Code -->
                    <div>
                        <label class="block mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                            Coupon Code
                        </label>
                        <input type="text" name="coupon_code" value="{{ old('coupon_code') }}" class="w-full px-3 py-2 border rounded dark:bg-gray-800 dark:border-gray-700">
                    </div>

                    <!-- Discount Type -->
                    <div>
                        <label class="block mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                            Discount Type
                        </label>
                        <select name="discount_type" class="w-full px-3 py-2 border rounded dark:bg-gray-800 dark:border-gray-700">
                            <option value="percentage" {{ old('discount_type') == 'percentage' ? 'selected' : '' }}>
                                Percentage
                            </option>
                            <option value="fixed" {{ old('discount_type') == 'fixed' ? 'selected' : '' }}>
                                Fixed Amount
                            </option>
                        </select>
                    </div>

                    <!-- Discount Amount -->
                    <div>
                        <label class="block mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                            Discount Amount
                        </label>
                        <input type="number" name="discount_amount" value="{{ old('discount_amount') }}" step="0.01" min="0" class="w-full px-3 py-2 border rounded dark:bg-gray-800 dark:border-gray-700">
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="primary-button">
                Save Rule
            </button>

            <a href="{{ route('admin.abandoned_cart.rules.index') }}" class="secondary-button">
                Cancel
            </a>
        </div>
    </form>

</x-admin::layouts>

@push('scripts')
<script>
    function toggleCouponFields() {
        const includeCoupon = document.getElementById('include_coupon');
        const couponFields = document.getElementById('coupon-fields');

        if (includeCoupon.checked) {
            couponFields.style.display = 'block';
        } else {
            couponFields.style.display = 'none';
        }
    }

</script>
@endpush
