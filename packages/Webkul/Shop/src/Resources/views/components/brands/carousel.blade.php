<v-brands-carousel src="{{ $src }}" title="{{ $title }}" navigation-link="{{ $navigationLink ?? '' }}">
    <x-shop::shimmer.brands.carousel :count="8" :navigation-link="$navigationLink ?? false" />
</v-brands-carousel>

@pushOnce('scripts')
<script type="text/x-template" id="v-brands-carousel-template">
    <div
            class="container mt-14 max-lg:px-8 max-md:mt-7 max-md:!px-0 max-sm:mt-5"
            v-if="! isLoading && brands?.length"
        >
            <div class="relative">
                <div
                    ref="swiperContainer"
                    class="flex gap-10 overflow-auto scrollbar-hide scroll-smooth max-lg:gap-4"
                >
                    <div
                        class="grid min-w-[120px] max-w-[120px] grid-cols-1 justify-items-center gap-4 font-medium max-md:min-w-20 max-md:max-w-20 max-md:gap-2.5 max-md:first:ml-4 max-sm:min-w-[60px] max-sm:max-w-[60px] max-sm:gap-1.5"
                        v-for="brand in brands"
                    >
                        <a
                            :href="brand.slug"
                            class="h-[110px] w-[110px] rounded-full bg-zinc-100 max-md:h-20 max-md:w-20 max-sm:h-[60px] max-sm:w-[60px]"
                            :aria-label="brand.name"
                        >

                          <x-shop::media.images.lazy
                            ::src="brand.image || fallback"
                            ::alt="brand.name"
                            width="110"
                            height="110"
                            class="object-cover w-full h-full rounded-full"
                        />
                        </a>

                        <a
                            :href="brand.slug"
                            class=""
                        >
                            <p
                                class="text-lg text-center text-black max-md:text-base max-md:font-normal max-sm:text-sm"
                                v-text="brand.name"
                            >
                            </p>
                        </a>
                    </div>
                </div>

                <span
                    class="icon-arrow-left-stylish absolute -left-10 top-9 flex h-[50px] w-[50px] cursor-pointer items-center justify-center rounded-full border border-black bg-white text-2xl transition hover:bg-black hover:text-white max-lg:-left-7 max-md:hidden"
                    role="button"
                    aria-label="@lang('shop::components.carousel.previous')"
                    tabindex="0"
                    @click="swipeLeft"
                >
                </span>

                <span
                    class="icon-arrow-right-stylish absolute -right-6 top-9 flex h-[50px] w-[50px] cursor-pointer items-center justify-center rounded-full border border-black bg-white text-2xl transition hover:bg-black hover:text-white max-lg:-right-7 max-md:hidden"
                    role="button"
                    aria-label="@lang('shop::components.carousel.next')"
                    tabindex="0"
                    @click="swipeRight"
                >
                </span>
            </div>
        </div>

        <!-- Brands Carousel Shimmer -->
        <template v-if="isLoading">
            <x-shop::shimmer.brands.carousel
                :count="8"
                :navigation-link="$navigationLink ?? false"
            />
        </template>
    </script>

<script type="module">
    app.component('v-brands-carousel', {
            template: '#v-brands-carousel-template',

            props: [
                'src',
                'title',
                'navigationLink',
            ],

            data() {
                return {
                    isLoading: true,

                    brands: [],

                    offset: 323,

                    fallback: "{{ bagisto_asset('images/small-product-placeholder.webp') }}"
                };
            },

            mounted() {
                this.getBrands();
            },

            methods: {
                getBrands() {
                    this.$axios.get(this.src)
                        .then(response => {
                            this.isLoading = false;

                            this.brands = response.data.data;
                        }).catch(error => {
                            console.log(error);
                        });
                },

                swipeLeft() {
                    const container = this.$refs.swiperContainer;

                    container.scrollLeft -= this.offset;
                },

                swipeRight() {
                    const container = this.$refs.swiperContainer;

                    container.scrollLeft += this.offset;
                },
            },
        });
    </script>
@endPushOnce
