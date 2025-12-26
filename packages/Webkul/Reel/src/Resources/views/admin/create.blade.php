<x-admin::layouts>

    <x-slot:title>
        @lang('reel::app.admin.reels.create.title')
    </x-slot:title>

    <form method="POST" action="{{ route('admin.reel.store') }}" enctype="multipart/form-data" class="max-w-3xl p-6 bg-white rounded dark:bg-gray-900 box-shadow">

        @csrf

        {{-- Title --}}
        <div class="mb-4">
            <label class="block mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                @lang('reel::app.admin.reels.fields.title')
            </label>
            <input type="text" name="title" class="w-full px-3 py-2 border rounded dark:bg-gray-800" required>
        </div>

        {{-- Caption --}}
        <div class="mb-4">
            <label class="block mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                @lang('reel::app.admin.reels.fields.caption')
            </label>
            <textarea name="caption" class="w-full px-3 py-2 border rounded dark:bg-gray-800"></textarea>
        </div>

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

        {{-- Duration (seconds) --}}
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
        <div class="mb-4">
            <label class="block mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                @lang('reel::app.admin.reels.fields.sort_order')
            </label>
            <input type="number" name="sort_order" class="w-full px-3 py-2 border rounded dark:bg-gray-800" min="0" step="1" value="0">
        </div>

        {{-- Views Count --}}
        <div class="mb-4">
            <label class="block mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                @lang('reel::app.admin.reels.fields.views_count')
            </label>
            <input type="number" name="views_count" class="w-full px-3 py-2 border rounded dark:bg-gray-800" min="0" step="1" value="0">
        </div>

        {{-- Likes Count --}}
        <div class="mb-6">
            <label class="block mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                @lang('reel::app.admin.reels.fields.likes_count')
            </label>
            <input type="number" name="likes_count" class="w-full px-3 py-2 border rounded dark:bg-gray-800" min="0" step="1" value="0">
        </div>

        {{-- Created By (hidden or set in controller) --}}
        {{-- Usually you don’t want user to select this; handle in backend --}}
        <input type="hidden" name="created_by" value="{{ auth()->id() }}">

        <div class="flex gap-2">
            <button type="submit" class="primary-button">
                @lang('reel::common.save')
            </button>

            <a href="{{ route('admin.reel.index') }}" class="secondary-button">
                @lang('reel::common.cancel')
            </a>
        </div>
    </form>

</x-admin::layouts>
