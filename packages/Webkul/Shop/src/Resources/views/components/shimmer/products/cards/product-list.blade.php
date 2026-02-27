@props(['count' => 8, 'navigationLink' => false])

<div class="container mt-20 max-lg:px-8 max-md:mt-8 max-sm:mt-7 max-sm:!px-4">
    <!-- Title Shimmer -->
    <div class="flex justify-between">
        <div class="shimmer h-10 w-64 rounded-lg font-dmserif text-3xl"></div>
        @if($navigationLink)
        <div class="shimmer h-6 w-24 rounded-lg"></div>
        @endif
    </div>

    <!-- Grid Shimmer with 1-4 columns responsive -->
    <div class="mt-10 max-md:mt-5">
        <div class="grid grid-cols-1 gap-5 xs:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 sm:gap-6 md:gap-7 lg:gap-8">
            @for ($i = 0; $i < $count; $i++) <div class="rounded-lg overflow-hidden bg-white">
                <!-- Image Shimmer -->
                <div class="shimmer relative pt-[100%] bg-gray-200">
                    <div class="absolute top-0 left-0 w-full h-full"></div>
                </div>

                <!-- Content Shimmer -->
                <div class="p-3 space-y-2">
                    <div class="shimmer h-4 w-3/4 rounded-lg"></div>
                    <div class="shimmer h-5 w-1/2 rounded-lg"></div>
                    <div class="flex items-center justify-between mt-2">
                        <div class="shimmer h-7 w-20 rounded-lg"></div>
                        <div class="shimmer h-7 w-7 rounded-lg"></div>
                    </div>
                </div>
        </div>
        @endfor
    </div>
</div>

<!-- Pagination Shimmer -->
<div class="flex items-center justify-center mt-10 gap-8 max-md:mt-8">
    <div class="shimmer h-8 w-8 rounded-full"></div>
    <div class="shimmer h-6 w-24 rounded-lg"></div>
    <div class="shimmer h-8 w-8 rounded-full"></div>
</div>

<!-- Desktop View All Button Shimmer -->
@if($navigationLink)
<div class="shimmer mx-auto mt-5 h-12 w-48 rounded-2xl"></div>
@endif
</div>
