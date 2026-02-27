<v-product-list src="{{ $src }}" title="{{ $title }}" navigation-link="{{ $navigationLink ?? '' }}">
    <x-shop::shimmer.products.cards.product-list :count="8" :navigation-link="$navigationLink ?? false" />
</v-product-list>

@pushOnce('scripts')
<script type="text/x-template" id="v-product-list-template">
    <div
        class="container mt-20 max-lg:px-8 max-md:mt-8 max-sm:mt-7 max-sm:!px-4"
        v-if="! isLoading && products.length"
    >
        <div class="flex justify-between">
            <h2 class="font-dmserif text-3xl max-md:text-2xl max-sm:text-xl">
                @{{ title }}
            </h2>

            <div class="flex items-center justify-between gap-8">
                <a
                    :href="navigationLink"
                    class="hidden max-lg:flex"
                    v-if="navigationLink"
                >
                    <p class="items-center text-xl max-md:text-base max-sm:text-sm">
                        @lang('shop::app.components.products.carousel.view-all')

                        <span class="icon-arrow-right text-2xl max-md:text-lg max-sm:text-sm"></span>
                    </p>
                </a>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="mt-10 max-md:mt-5">
            <!-- Grid with dynamic columns 1-4 -->
            <div class="grid gap-5 sm:gap-6 md:gap-7 lg:gap-8"
                 :style="gridStyle">
                <v-product-card
                    v-for="product in products"
                    :key="product.id"
                    :product="product"
                    mode="grid"
                    class="w-full"
                />
            </div>

            <!-- Pagination -->
            <div class="flex items-center justify-center mt-10 gap-8 max-md:mt-8" v-if="pagination.last_page > 1">
                <span
                    @click="changePage(pagination.current_page - 1)"
                    :class="[
                        'icon-arrow-left-stylish rtl:icon-arrow-right-stylish inline-block cursor-pointer text-2xl transition hover:scale-110',
                        pagination.current_page <= 1 ? 'opacity-50 cursor-not-allowed' : ''
                    ]"
                    role="button"
                    aria-label="@lang('shop::app.components.products.carousel.previous')"
                    tabindex="0"
                >
                </span>

                <span class="text-lg font-medium max-md:text-base">
                    @{{ pagination.current_page }} / @{{ pagination.last_page }}
                </span>

                <span
                    @click="changePage(pagination.current_page + 1)"
                    :class="[
                        'icon-arrow-right-stylish rtl:icon-arrow-left-stylish inline-block cursor-pointer text-2xl transition hover:scale-110',
                        pagination.current_page >= pagination.last_page ? 'opacity-50 cursor-not-allowed' : ''
                    ]"
                    role="button"
                    aria-label="@lang('shop::app.components.products.carousel.next')"
                    tabindex="0"
                >
                </span>
            </div>
        </div>

        <a
            :href="navigationLink"
            class="secondary-button mx-auto mt-5 block w-max rounded-2xl px-11 py-3 text-center text-base max-lg:mt-0 max-lg:hidden max-lg:py-3.5 max-md:rounded-lg"
            :aria-label="title"
            v-if="navigationLink"
        >
            @lang('shop::app.components.products.carousel.view-all')
        </a>
    </div>

    <!-- No Products -->
    <div
        class="container mt-20 max-lg:px-8 max-md:mt-8 max-sm:mt-7 max-sm:!px-4 text-center py-12 sm:py-16"
        v-else-if="! isLoading && !products?.length"
    >
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-3 sm:w-20 sm:h-20 sm:mb-4">
            <span class="icon-image text-3xl text-gray-400 sm:text-4xl"></span>
        </div>
        <p class="text-gray-500 text-base sm:text-lg">@lang('shop::app.components.products.card.no-products-found')</p>
    </div>

    <!-- Loading Shimmer -->
    <template v-if="isLoading">
        <x-shop::shimmer.products.cards.product-list
            :count="8"
            :navigation-link="$navigationLink ?? false"
        />
    </template>
</script>

<script type="module">
    app.component('v-product-list', {
        template: '#v-product-list-template',

        props: {
            src: String,
            title: String,
            navigationLink: {
                type: String,
                default: ''
            }
        },

        data() {
            return {
                isLoading: true,
                products: [],
                pagination: {
                    current_page: 1,
                    last_page: 1,
                    per_page: 8,
                    total: 0
                },
                currentColumns: 4 // Default to 4 columns
            };
        },

        computed: {
            gridStyle() {
                return {
                    display: 'grid',
                    gridTemplateColumns: `repeat(${this.currentColumns}, minmax(0, 1fr))`,
                    gap: '1rem'
                };
            }
        },

        mounted() {
            this.getProducts();
            this.updateColumns();
            window.addEventListener('resize', this.updateColumns);
        },

        beforeDestroy() {
            window.removeEventListener('resize', this.updateColumns);
        },

        methods: {
            updateColumns() {
                const width = window.innerWidth;

                // Column counts based on screen width (1 to 4 columns)
                if (width < 480) { // Very small phones
                    this.currentColumns = 1;
                } else if (width < 640) { // Small phones
                    this.currentColumns = 2;
                } else if (width < 1024) { // Tablets and small desktops
                    this.currentColumns = 3;
                } else { // Desktops and larger
                    this.currentColumns = 4;
                }

                this.$forceUpdate();
            },

            getProducts(page = 1) {
                this.isLoading = true;

                this.$axios.get(this.src, {
                    params: {
                        page: page,
                        per_page: this.pagination.per_page
                    }
                })
                .then(response => {
                    this.isLoading = false;

                    if (response.data.data) {
                        this.products = response.data.data;

                        if (response.data.meta) {
                            this.pagination = {
                                current_page: response.data.meta.current_page,
                                last_page: response.data.meta.last_page,
                                per_page: response.data.meta.per_page,
                                total: response.data.meta.total
                            };
                        } else if (response.data.current_page) {
                            this.pagination = {
                                current_page: response.data.current_page,
                                last_page: response.data.last_page,
                                per_page: response.data.per_page,
                                total: response.data.total
                            };
                        }
                    } else if (Array.isArray(response.data)) {
                        this.products = response.data;
                        this.pagination = {
                            current_page: 1,
                            last_page: 1,
                            per_page: response.data.length,
                            total: response.data.length
                        };
                    }
                })
                .catch(error => {
                    this.isLoading = false;
                    console.error('Error fetching products:', error);
                });
            },

            changePage(page) {
                if (page < 1 || page > this.pagination.last_page) return;
                this.getProducts(page);
                this.$el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        },
    });
</script>
@endPushOnce
