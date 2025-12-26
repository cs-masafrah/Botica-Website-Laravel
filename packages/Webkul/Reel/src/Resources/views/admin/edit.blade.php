<x-admin::layouts>

    <x-slot:title>
        @lang('reel::app.admin.reels.edit.title')
    </x-slot:title>

    <form method="POST" action="{{ route('admin.reel.update', $reel->id) }}" enctype="multipart/form-data" class="max-w-3xl p-6 bg-white rounded dark:bg-gray-900 box-shadow">

        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                @lang('reel::app.admin.reels.fields.title')
            </label>
            <input type="text" name="title" value="{{ $reel->title }}" class="w-full px-3 py-2 border rounded dark:bg-gray-800" required>
        </div>

        <div class="mb-4">
            <label class="block mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                @lang('reel::app.admin.reels.fields.caption')
            </label>
            <textarea name="caption" class="w-full px-3 py-2 border rounded dark:bg-gray-800">{{ $reel->caption }}</textarea>
        </div>

        <div class="mb-4">
            <label class="block mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                @lang('reel::app.admin.reels.fields.product')
            </label>
            <select name="product_id" class="w-full px-3 py-2 border rounded dark:bg-gray-800">
                <option value="">—</option>
                @foreach ($products as $product)
                <option value="{{ $product->id }}" @selected($reel->product_id == $product->id)>
                    {{ $product->name }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="mb-6">
            <p class="mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                @lang('reel::app.admin.reels.fields.video')
            </p>

            <video controls class="w-full rounded">
                <source src="{{ Storage::url($reel->video_path) }}">
            </video>
        </div>

        <div class="flex gap-2">
            <button class="primary-button">
                @lang('reel::common.update')
            </button>

            <a href="{{ route('admin.reel.index') }}" class="secondary-button">
                @lang('reel::common.cancel')
            </a>
        </div>
    </form>

</x-admin::layouts>
