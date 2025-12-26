<x-admin::layouts>

    <x-slot:title>
        @lang('reel::app.admin.reels.show.title')
    </x-slot:title>

    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('reel::app.admin.reels.show.title') #{{ $reel->id }}
        </h1>
    </div>

    <div class="p-4 bg-white rounded dark:bg-gray-900 box-shadow">
        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
            @lang('reel::app.admin.reels.show.general-info')
        </p>

        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="font-semibold text-gray-600 dark:text-gray-300">
                    @lang('reel::app.admin.reels.fields.title')
                </p>
                <p class="text-gray-800 dark:text-white">
                    {{ $reel->title }}
                </p>
            </div>

            <div>
                <p class="font-semibold text-gray-600 dark:text-gray-300">
                    @lang('reel::app.admin.reels.fields.status')
                </p>
                <span class="badge {{ $reel->is_active ? 'label-active' : 'label-info' }}">
                    {{ $reel->is_active
                        ? trans('reel::app.admin.reels.status.active')
                        : trans('reel::app.admin.reels.status.inactive') }}
                </span>
            </div>

            <div>
                <p class="font-semibold text-gray-600 dark:text-gray-300">
                    @lang('reel::app.admin.reels.fields.product')
                </p>
                <p class="text-gray-800 dark:text-white">
                    {{ optional($reel->product)->name ?? '-' }}
                </p>
            </div>

            <div>
                <p class="font-semibold text-gray-600 dark:text-gray-300">
                    @lang('reel::app.admin.reels.fields.created-at')
                </p>
                <p class="text-gray-800 dark:text-white">
                    {{ $reel->created_at }}
                </p>
            </div>
        </div>

        <div class="mt-6">
            <p class="mb-2 font-semibold text-gray-600 dark:text-gray-300">
                @lang('reel::app.admin.reels.fields.video')
            </p>

            <video controls class="w-full rounded">
                <source src="{{ Storage::url($reel->video_path) }}">
            </video>
        </div>
    </div>

</x-admin::layouts>
