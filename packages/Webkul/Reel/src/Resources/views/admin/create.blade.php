{{-- Resources/views/admin/create.blade.php --}}
<x-admin::layouts>

    <x-slot:title>
        @lang('reel::app.admin.reels.create.title')
    </x-slot:title>

    <form method="POST" action="{{ route('admin.reel.store') }}" enctype="multipart/form-data" class="max-w-3xl p-6 bg-white rounded dark:bg-gray-900 box-shadow">

        @csrf

        {{-- Language Tabs --}}
        <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="languageTabs" role="tablist">
                @foreach ($locales as $index => $locale)
                    <li class="mr-2" role="presentation">
                        <button
                            class="inline-block p-4 rounded-t-lg border-b-2 {{ $index === 0 ? 'border-blue-600 text-blue-600 dark:text-blue-500 dark:border-blue-500' : 'border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300' }}"
                            id="{{ $locale->code }}-tab"
                            data-tabs-target="#{{ $locale->code }}"
                            type="button"
                            role="tab"
                            aria-controls="{{ $locale->code }}"
                            aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                            @click="switchTab('{{ $locale->code }}')"
                        >
                            {{ $locale->name }}
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Language Content --}}
        <div id="languageTabContent">
            @foreach ($locales as $index => $locale)
                <div
                    class="{{ $index === 0 ? '' : 'hidden' }}"
                    id="{{ $locale->code }}"
                    role="tabpanel"
                    aria-labelledby="{{ $locale->code }}-tab"
                >
                    {{-- Title --}}
                    <div class="mb-4">
                        <label class="block mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                            @lang('reel::app.admin.reels.fields.title') ({{ $locale->name }})
                            <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="{{ $locale->code }}[title]"
                            class="w-full px-3 py-2 border rounded dark:bg-gray-800"
                            required
                        >
                    </div>

                    {{-- Caption --}}
                    <div class="mb-4">
                        <label class="block mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                            @lang('reel::app.admin.reels.fields.caption') ({{ $locale->name }})
                        </label>
                        <textarea
                            name="{{ $locale->code }}[caption]"
                            class="w-full px-3 py-2 border rounded dark:bg-gray-800"
                            rows="3"
                        ></textarea>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Non-Translatable Fields --}}
        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                @lang('reel::app.admin.reels.general-settings')
            </h3>

            {{-- Product --}}
            <div class="mb-4">
                <label class="block mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                    @lang('reel::app.admin.reels.fields.product')
                </label>
                <select name="product_id" class="w-full px-3 py-2 border rounded dark:bg-gray-800">
                    <option value="">—</option>
                    @foreach ($products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Video --}}
            <div class="mb-4">
                <label class="block mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                    @lang('reel::app.admin.reels.fields.video')
                    <span class="text-red-500">*</span>
                </label>
                <input type="file" name="video" accept="video/mp4,video/mov,video/webm" class="w-full text-sm" required>
            </div>

            {{-- Thumbnail --}}
            <div class="mb-4">
                <label class="block mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                    @lang('reel::app.admin.reels.fields.thumbnail')
                </label>
                <input type="file" name="thumbnail" accept="image/*" class="w-full text-sm">
            </div>

            {{-- Duration --}}
            <div class="mb-4">
                <label class="block mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                    @lang('reel::app.admin.reels.fields.duration')
                </label>
                <input type="number" name="duration" class="w-full px-3 py-2 border rounded dark:bg-gray-800" min="0" step="1" placeholder="Duration in seconds">
            </div>

            {{-- Is Active --}}
            <div class="mb-4 flex items-center gap-2">
                <input type="checkbox" id="is_active" name="is_active" value="1" checked>
                <label for="is_active" class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                    @lang('reel::app.admin.reels.fields.is_active')
                </label>
            </div>

            {{-- Sort Order --}}
            <div class="mb-6">
                <label class="block mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                    @lang('reel::app.admin.reels.fields.sort_order')
                </label>
                <input type="number" name="sort_order" class="w-full px-3 py-2 border rounded dark:bg-gray-800" min="0" step="1" value="0">
            </div>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="primary-button">
                @lang('reel::common.save')
            </button>

            <a href="{{ route('admin.reel.index') }}" class="secondary-button">
                @lang('reel::common.cancel')
            </a>
        </div>
    </form>

    @pushOnce('scripts')
    <script>
        function switchTab(tabId) {
            // Hide all tabs
            document.querySelectorAll('[role="tabpanel"]').forEach(tab => {
                tab.classList.add('hidden');
            });

            // Show selected tab
            document.getElementById(tabId).classList.remove('hidden');

            // Update tab buttons
            document.querySelectorAll('[role="tab"]').forEach(button => {
                button.classList.remove('border-blue-600', 'text-blue-600', 'dark:text-blue-500', 'dark:border-blue-500');
                button.classList.add('border-transparent');
                button.setAttribute('aria-selected', 'false');
            });

            // Activate clicked tab
            const activeTab = document.getElementById(tabId + '-tab');
            activeTab.classList.add('border-blue-600', 'text-blue-600', 'dark:text-blue-500', 'dark:border-blue-500');
            activeTab.classList.remove('border-transparent');
            activeTab.setAttribute('aria-selected', 'true');
        }
    </script>
    @endPushOnce

</x-admin::layouts>
