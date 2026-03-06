<x-admin::layouts>
    <x-slot:title>
        @lang('reel::app.admin.reels.title')
    </x-slot>

    {!! view_render_event('bagisto.admin.reels.create.before') !!}

    <v-reels>
        <x-admin::datagrid :src="route('admin.reel.index')" ref="datagrid" />
    </v-reels>

    {!! view_render_event('bagisto.admin.reels.create.after') !!}

    @pushOnce('scripts')
    <!-- Sortable.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <!-- Pass locales to JavaScript -->
    <script>
        window.reelLocales = @json($locales->map(function($locale) {
            return [
                'code' => $locale->code,
                'name' => $locale->name
            ];
        }));
    </script>

    <script type="text/x-template" id="v-reels-template">
        <div>
            <!-- Header with Create and Save Sort Order Buttons -->
            <div class="flex items-center justify-between gap-4 mb-4 max-sm:flex-wrap">
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    @lang('reel::app.admin.reels.title')
                </p>

                <div class="flex items-center gap-x-2.5">
                    <!-- Save Sort Order Button (shown when order changes) -->
                    <button
                        v-if="showSaveButton"
                        type="button"
                        class="secondary-button"
                        @click="saveSortOrder"
                        :disabled="isSaving"
                    >
                        <span v-if="isSaving">
                            <svg class="w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        <span v-else>
                            @lang('reel::app.admin.reels.messages.save-sort-order')
                        </span>
                    </button>

                    <!-- Create Button -->
                    @if (bouncer()->hasPermission('reel.create'))
                        <button
                            type="button"
                            class="primary-button"
                            @click="openCreateModal"
                        >
                            @lang('reel::app.admin.reels.create.title')
                        </button>
                    @endif
                </div>
            </div>

            <!-- DataGrid -->
            <div ref="datagridContainer">
                <x-admin::datagrid
                    :src="route('admin.reel.index')"
                    ref="datagrid"
                    @reload="initializeSortable"
                >
                    <!-- DataGrid Header -->
                    <template #header>
                        <div
                            class="grid px-4 py-3 font-medium border-b row bg-gray-50 dark:border-gray-800 dark:bg-gray-900"
                            :style="gridTemplateColumns"
                        >
                            <p class="flex items-center gap-2">
                                <span class="w-4 drag-handle-placeholder"></span>
                                ID
                            </p>
                            <p>@lang('reel::app.admin.reels.datagrid.title')</p>
                            <p>@lang('reel::app.admin.reels.datagrid.caption')</p>
                            <p>@lang('reel::app.admin.reels.datagrid.product')</p>
                            <p>@lang('reel::app.admin.reels.datagrid.duration')</p>
                            <p>@lang('reel::app.admin.reels.fields.status')</p>
                            <p>@lang('reel::app.admin.reels.datagrid.views')</p>
                            <p>@lang('reel::app.admin.reels.datagrid.likes')</p>
                            <p>@lang('reel::app.admin.reels.datagrid.sort_order')</p>
                            <p class="text-right">@lang('reel::app.admin.reels.datagrid.actions')</p>
                        </div>
                    </template>

                    <!-- DataGrid Body -->
                    <template #body="{ isLoading, available, performAction }">
                        <template v-if="isLoading">
                            <x-admin::shimmer.datagrid.table.body />
                        </template>

                        <template v-else>
                            <div
                                ref="sortableContainer"
                                class="sortable-container"
                            >
                                <div
                                    v-for="record in available.records"
                                    :key="record.id"
                                    :data-id="record.id"
                                    class="row grid items-center gap-2.5 border-b px-4 py-4 dark:border-gray-800 sortable-item"
                                    :style="gridTemplateColumns"
                                >
                                    <p class="flex items-center gap-2">
                                        <span class="text-gray-400 cursor-move drag-handle hover:text-gray-600 dark:hover:text-gray-300">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                                            </svg>
                                        </span>
                                        @{{ record.id }}
                                    </p>
                                    <p>@{{ record.title }}</p>
                                    <p class="max-w-xs truncate">@{{ record.caption }}</p>
                                    <p v-if="record.product_name">@{{ record.product_name }}</p>
                                    <p v-else>@lang('reel::app.admin.reels.datagrid.na')</p>
                                    <p>@{{ record.duration }}s</p>
                                    <span
                                        :class="[
                                            'px-2 py-1 rounded-md text-xs',
                                            record.is_active === 'Active'
                                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                                : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                        ]"
                                    >
                                        @{{ record.is_active === 'Active' ? 'Active' : 'Inactive' }}
                                    </span>
                                    <p>@{{ record.views_count }}</p>
                                    <p>@{{ record.likes_count }}</p>
                                    <p>@{{ record.sort_order }}</p>
                                    <div class="flex justify-end gap-2">
                                        <!-- Edit Button -->
                                        @if (bouncer()->hasPermission('reel.edit'))
                                            <a
                                                v-if="record.actions && record.actions.find(a => a.index === 'edit')"
                                                @click="editReel(record.id)"
                                                class="cursor-pointer"
                                                title="Edit"
                                            >
                                                <span class="text-2xl text-blue-600 icon-edit hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"></span>
                                            </a>
                                        @endif

                                        <!-- Delete Button -->
                                        @if (bouncer()->hasPermission('reel.delete'))
                                            <a
                                                v-if="record.actions && record.actions.find(a => a.index === 'delete')"
                                                @click="performAction(record.actions.find(a => a.index === 'delete'))"
                                                class="cursor-pointer"
                                                title="Delete"
                                            >
                                                <span class="text-2xl text-red-600 icon-delete hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"></span>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </template>
                    </template>
                </x-admin::datagrid>
            </div>

            <!-- Video Preview Modal -->
            <x-admin::modal ref="videoPreviewModal">
                <x-slot:header>
                    <p class="text-lg font-bold text-gray-800 dark:text-white">
                        @lang('reel::app.admin.reels.fields.video')
                    </p>
                </x-slot>

                <x-slot:content>
                    <div class="flex justify-center">
                        <video
                            v-if="previewVideoUrl"
                            :src="previewVideoUrl"
                            controls
                            class="max-w-full max-h-[500px] rounded-lg"
                        >
                            @lang('reel::app.admin.reels.messages.video-not-supported')
                        </video>
                    </div>
                </x-slot>
            </x-admin::modal>

            <!-- Create/Edit Reel Modal -->
            <x-admin::form
                v-slot="{ meta, errors, handleSubmit }"
                as="div"
                ref="modalForm"
            >
                <form
                    @submit="handleSubmit($event, createOrUpdate)"
                    ref="createReelForm"
                    enctype="multipart/form-data"
                >
                    <x-admin::modal
                        ref="reelUpdateOrCreateModal"
                        :is-panel="true"
                        :show-close-button="true"
                        :show-footer="true"
                        :modal-id="'reel-modal'"
                        :max-width="'4xl'"
                    >
                        <x-slot:header>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">
                                <span v-if="reel.id">@lang('reel::app.admin.reels.edit.title')</span>
                                <span v-else>@lang('reel::app.admin.reels.create.title')</span>
                            </p>
                        </x-slot>

                        <x-slot:content>
                            <input type="hidden" name="id" v-model="reel.id">

                            <!-- Language Tabs -->
                            <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
                                <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="languageTabs" role="tablist">
                                    <li v-for="(locale, index) in locales" :key="locale.code" class="mr-2" role="presentation">
                                        <button
                                            class="inline-block p-4 rounded-t-lg border-b-2"
                                            :class="activeLocale === locale.code ? 'border-blue-600 text-blue-600 dark:text-blue-500 dark:border-blue-500' : 'border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300'"
                                            :id="locale.code + '-tab'"
                                            type="button"
                                            role="tab"
                                            :aria-controls="'locale-' + locale.code"
                                            :aria-selected="activeLocale === locale.code"
                                            @click="switchLocale(locale.code)"
                                        >
                                            @{{ locale.name }}
                                        </button>
                                    </li>
                                </ul>
                            </div>

                            <!-- Language Content - FIXED: Using standard HTML inputs instead of x-admin components for Vue bindings -->
                            <div id="languageTabContent">
                                <div v-for="(locale, index) in locales" :key="locale.code">
                                    <div
                                        v-show="activeLocale === locale.code"
                                        :id="'locale-' + locale.code"
                                        role="tabpanel"
                                        :aria-labelledby="locale.code + '-tab'"
                                    >
                                        <!-- Title (Translatable) -->
                                        <div class="mb-4">
                                            <label class="block mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300 required">
                                                @lang('reel::app.admin.reels.fields.title')
                                                <span v-text="'(' + locale.name + ')'"></span>
                                            </label>
                                            <input
                                                type="text"
                                                :name="locale.code + '[title]'"
                                                v-model="reel.translations[locale.code].title"
                                                class="w-full px-3 py-2 border rounded dark:bg-gray-800"
                                                required
                                            />
                                            <div v-if="errors && errors[locale.code + '.title']" class="text-red-600 text-xs mt-1">
                                                <span v-for="error in errors[locale.code + '.title']" v-text="error"></span>
                                            </div>
                                        </div>

                                        <!-- Caption (Translatable) -->
                                        <div class="mb-4">
                                            <label class="block mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                @lang('reel::app.admin.reels.fields.caption')
                                                <span v-text="'(' + locale.name + ')'"></span>
                                            </label>
                                            <textarea
                                                :name="locale.code + '[caption]'"
                                                v-model="reel.translations[locale.code].caption"
                                                class="w-full px-3 py-2 border rounded dark:bg-gray-800"
                                                rows="3"
                                            ></textarea>
                                            <div v-if="errors && errors[locale.code + '.caption']" class="text-red-600 text-xs mt-1">
                                                <span v-for="error in errors[locale.code + '.caption']" v-text="error"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Non-Translatable Fields -->
                            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                                <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                                    @lang('reel::app.admin.reels.general-settings')
                                </h3>

                                <!-- Product - FIXED: Using standard HTML select -->
                                <div class="mb-4">
                                    <label class="block mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        @lang('reel::app.admin.reels.fields.product')
                                    </label>
                                    <select
                                        name="product_id"
                                        v-model="reel.product_id"
                                        class="w-full px-3 py-2 border rounded dark:bg-gray-800"
                                    >
                                        <option value="">— @lang('reel::app.admin.reels.datagrid.na') —</option>
                                        <option v-for="product in products" :value="product.id" :key="product.id">
                                            @{{ product.name }}
                                        </option>
                                    </select>
                                    <div v-if="errors && errors.product_id" class="text-red-600 text-xs mt-1">
                                        <span v-for="error in errors.product_id" v-text="error"></span>
                                    </div>
                                </div>

                                <!-- Video Upload - FIXED: Using standard HTML input -->
                                <div class="mb-4">
                                    <label class="block mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300 required">
                                        @lang('reel::app.admin.reels.fields.video')
                                    </label>

                                    <div v-if="reel.video_url" class="mb-2">
                                        <video
                                            :src="reel.video_url"
                                            class="object-cover w-32 h-32 mb-2 rounded-lg"
                                            controls
                                        ></video>
                                        <button
                                            type="button"
                                            @click="removeVideo"
                                            class="text-sm text-red-600 hover:text-red-800"
                                        >
                                            @lang('reel::app.admin.reels.messages.remove-video')
                                        </button>
                                    </div>

                                    <input
                                        type="file"
                                        name="video"
                                        ref="videoInput"
                                        accept="video/mp4,video/quicktime,video/x-msvideo"
                                        @change="handleVideoUpload"
                                        :required="!reel.id"
                                        class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                    />
                                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                                        @lang('reel::app.admin.reels.messages.video-size')
                                        <span v-if="reel.id"> (@lang('reel::app.admin.reels.messages.optional-update'))</span>
                                    </p>
                                    <div v-if="errors && errors.video" class="text-red-600 text-xs mt-1">
                                        <span v-for="error in errors.video" v-text="error"></span>
                                    </div>
                                </div>

                                <!-- Thumbnail Upload - FIXED: Using standard HTML input -->
                                <div class="mb-4">
                                    <label class="block mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        @lang('reel::app.admin.reels.fields.thumbnail')
                                    </label>

                                    <div v-if="reel.thumbnail_url" class="mb-2">
                                        <img
                                            :src="reel.thumbnail_url"
                                            class="object-cover w-32 h-32 mb-2 rounded-lg"
                                            :alt="reel.title"
                                        >
                                        <button
                                            type="button"
                                            @click="removeThumbnail"
                                            class="text-sm text-red-600 hover:text-red-800"
                                        >
                                            @lang('reel::app.admin.reels.messages.remove-thumbnail')
                                        </button>
                                    </div>

                                    <input
                                        type="file"
                                        name="thumbnail"
                                        accept="image/*"
                                        @change="handleThumbnailUpload"
                                        class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                    />
                                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                                        @lang('reel::app.admin.reels.messages.thumbnail-size')
                                    </p>
                                    <div v-if="errors && errors.thumbnail" class="text-red-600 text-xs mt-1">
                                        <span v-for="error in errors.thumbnail" v-text="error"></span>
                                    </div>
                                </div>

                                <!-- Duration -->
                                <div class="mb-4">
                                    <label class="block mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        @lang('reel::app.admin.reels.fields.duration')
                                    </label>
                                    <input
                                        type="number"
                                        name="duration"
                                        v-model="reel.duration"
                                        placeholder="@lang('reel::app.admin.reels.fields.duration')"
                                        step="0.01"
                                        min="0"
                                        class="w-full px-3 py-2 border rounded dark:bg-gray-800"
                                    />
                                    <div v-if="errors && errors.duration" class="text-red-600 text-xs mt-1">
                                        <span v-for="error in errors.duration" v-text="error"></span>
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="mb-4">
                                    <label class="block mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        @lang('reel::app.admin.reels.fields.is_active')
                                    </label>
                                    <select
                                        name="is_active"
                                        v-model="reel.is_active"
                                        class="w-full px-3 py-2 border rounded dark:bg-gray-800"
                                    >
                                        <option value="1">@lang('reel::app.admin.reels.status.active')</option>
                                        <option value="0">@lang('reel::app.admin.reels.status.inactive')</option>
                                    </select>
                                    <div v-if="errors && errors.is_active" class="text-red-600 text-xs mt-1">
                                        <span v-for="error in errors.is_active" v-text="error"></span>
                                    </div>
                                </div>

                                <!-- Sort Order -->
                                <div class="mb-4">
                                    <label class="block mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        @lang('reel::app.admin.reels.fields.sort_order')
                                    </label>
                                    <input
                                        type="number"
                                        name="sort_order"
                                        v-model="reel.sort_order"
                                        placeholder="@lang('reel::app.admin.reels.fields.sort_order')"
                                        min="0"
                                        disabled
                                        class="w-full px-3 py-2 border rounded dark:bg-gray-800 bg-gray-100"
                                    />
                                </div>
                            </div>
                        </x-slot>

                        <x-slot:footer>
                            <button
                                type="submit"
                                class="primary-button"
                                :disabled="isLoading"
                            >
                                <span v-if="isLoading">
                                    <svg class="w-5 h-5 text-white animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                                <span v-else>
                                    <span v-if="reel.id">@lang('reel::app.admin.reels.messages.update-btn')</span>
                                    <span v-else>@lang('reel::app.admin.reels.messages.save-btn')</span>
                                </span>
                            </button>
                        </x-slot>
                    </x-admin::modal>
                </form>
            </x-admin::form>
        </div>
    </script>

    <script type="module">
        app.component('v-reels', {
            template: '#v-reels-template',

            data() {
                return {
                    reel: {
                        id: null,
                        product_id: null,
                        video_url: '',
                        thumbnail_url: '',
                        duration: 0,
                        is_active: 1,
                        sort_order: 0,
                        translations: {}
                    },
                    locales: window.reelLocales || [],
                    activeLocale: window.reelLocales && window.reelLocales.length > 0 ? window.reelLocales[0].code : 'en',
                    products: [],
                    productsLoading: false,
                    previewVideoUrl: '',
                    isLoading: false,
                    isSaving: false,
                    showSaveButton: false,
                    sortableInstance: null,
                    originalOrder: [],
                    currentOrder: [],
                    errors: {}
                }
            },

            computed: {
                gridTemplateColumns() {
                    return 'grid-template-columns: repeat(10, minmax(0, 1fr))';
                }
            },

            mounted() {
                this.initializeTranslations();
                this.fetchProducts();
                setTimeout(() => {
                    this.initializeSortable();
                }, 500);
            },

            methods: {
                initializeTranslations() {
                    // Initialize translations object for all locales
                    if (!this.reel.translations) {
                        this.reel.translations = {};
                    }

                    this.locales.forEach(locale => {
                        if (!this.reel.translations[locale.code]) {
                            this.reel.translations[locale.code] = {
                                title: '',
                                caption: ''
                            };
                        }
                    });
                },

                switchLocale(localeCode) {
                    this.activeLocale = localeCode;
                },

                async fetchProducts() {
                    this.productsLoading = true;
                    try {
                        const response = await this.$axios.get('{{ route("admin.reel.get_products") }}');

                        if (response.data.success) {
                            this.products = response.data.data || [];
                        } else {
                            console.error('Error fetching products:', response.data.message);
                            this.products = [];
                        }
                    } catch (error) {
                        console.error('Error fetching products:', error);
                        this.products = [];
                    } finally {
                        this.productsLoading = false;
                    }
                },

                calculateNextSortOrder() {
                    const rows = document.querySelectorAll('.sortable-item');
                    let maxSortOrder = 0;

                    if (rows.length > 0) {
                        rows.forEach(row => {
                            const cells = row.children;
                            if (cells.length > 8) {
                                const sortOrderCell = cells[8];
                                const sortOrder = parseInt(sortOrderCell.textContent) || 0;
                                if (sortOrder > maxSortOrder) {
                                    maxSortOrder = sortOrder;
                                }
                            }
                        });
                    }

                    return maxSortOrder + 1;
                },

                openCreateModal() {
                    const nextSortOrder = this.calculateNextSortOrder();

                    this.reel = {
                        id: null,
                        product_id: null,
                        video_url: '',
                        thumbnail_url: '',
                        duration: 0,
                        is_active: 1,
                        sort_order: nextSortOrder,
                        translations: {}
                    };

                    this.errors = {};

                    // Initialize translations
                    this.initializeTranslations();

                    if (this.locales.length > 0) {
                        this.activeLocale = this.locales[0].code;
                    }

                    if (this.products.length === 0) {
                        this.fetchProducts();
                    }

                    // Clear file inputs
                    if (this.$refs.videoInput) {
                        this.$refs.videoInput.value = '';
                    }

                    const thumbnailInput = document.querySelector('input[name="thumbnail"]');
                    if (thumbnailInput) {
                        thumbnailInput.value = '';
                    }

                    this.$refs.reelUpdateOrCreateModal.toggle();
                },

                async createOrUpdate(params, { resetForm, setErrors }) {
                    this.isLoading = true;
                    this.errors = {};

                    const formData = new FormData();

                    // Add translations
                    Object.keys(this.reel.translations).forEach(locale => {
                        formData.append(`${locale}[title]`, this.reel.translations[locale].title || '');
                        formData.append(`${locale}[caption]`, this.reel.translations[locale].caption || '');
                    });

                    // Add non-translatable fields
                    if (this.reel.product_id !== undefined && this.reel.product_id !== null) {
                        formData.append('product_id', this.reel.product_id);
                    }

                    formData.append('duration', this.reel.duration);
                    formData.append('is_active', this.reel.is_active);
                    formData.append('sort_order', this.reel.sort_order);

                    // Add video file if exists
                    const videoInput = this.$refs.videoInput;
                    if (videoInput && videoInput.files[0]) {
                        formData.append('video', videoInput.files[0]);
                    }

                    // Add thumbnail file if exists
                    const thumbnailInput = document.querySelector('input[name="thumbnail"]');
                    if (thumbnailInput && thumbnailInput.files[0]) {
                        formData.append('thumbnail', thumbnailInput.files[0]);
                    }

                    // For update, add _method PUT
                    if (this.reel.id) {
                        formData.append('_method', 'PUT');
                    }

                    try {
                        let url;
                        if (this.reel.id) {
                            url = "{{ route('admin.reel.update', ['reel' => '__ID__']) }}".replace('__ID__', this.reel.id);
                        } else {
                            url = "{{ route('admin.reel.store') }}";
                        }

                        const response = await this.$axios.post(url, formData, {
                            headers: {
                                'Content-Type': 'multipart/form-data',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        this.$refs.reelUpdateOrCreateModal.close();
                        this.$emitter.emit('add-flash', {
                            type: 'success',
                            message: response.data.message || 'Operation successful'
                        });

                        this.$refs.datagrid.get();
                    } catch (error) {
                        console.error('Error:', error);
                        if (error.response?.status === 422) {
                            this.errors = error.response.data.errors || {};
                            setErrors(this.errors);
                        } else {
                            this.$emitter.emit('add-flash', {
                                type: 'error',
                                message: error.response?.data?.message || 'An error occurred'
                            });
                        }
                    } finally {
                        this.isLoading = false;
                    }
                },

                async editReel(reelId) {
                    const url = "{{ route('admin.reel.edit', ['reel' => '__ID__']) }}".replace('__ID__', reelId);

                    try {
                        const response = await this.$axios.get(url);
                        const data = response.data.data || response.data;

                        if (!data) {
                            throw new Error('No data received from server');
                        }

                        if (this.products.length === 0) {
                            await this.fetchProducts();
                        }

                        // Initialize translations from response
                        const translations = {};
                        if (data.translations && data.translations.length > 0) {
                            data.translations.forEach(translation => {
                                translations[translation.locale] = {
                                    title: translation.title || '',
                                    caption: translation.caption || ''
                                };
                            });
                        }

                        // Ensure all locales have translations
                        this.locales.forEach(locale => {
                            if (!translations[locale.code]) {
                                translations[locale.code] = {
                                    title: '',
                                    caption: ''
                                };
                            }
                        });

                        this.reel = {
                            id: data.id,
                            product_id: data.product_id || null,
                            video_url: data.video_url || data.video_path || '',
                            thumbnail_url: data.thumbnail_url || data.thumbnail_path || '',
                            duration: data.duration || 0,
                            is_active: data.is_active ?? 1,
                            sort_order: data.sort_order || 0,
                            translations: translations
                        };

                        this.errors = {};

                        if (this.locales.length > 0) {
                            this.activeLocale = this.locales[0].code;
                        }

                        this.$refs.reelUpdateOrCreateModal.toggle();
                    } catch (error) {
                        console.error('Edit error details:', error);
                        let errorMessage = 'Failed to load reel data';
                        if (error.response?.status === 404) {
                            errorMessage = 'Reel not found';
                        } else if (error.response?.data?.message) {
                            errorMessage = error.response.data.message;
                        }

                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: errorMessage
                        });
                    }
                },

                initializeSortable() {
                    const container = this.$refs.sortableContainer;
                    if (!container || this.sortableInstance) {
                        return;
                    }

                    if (this.sortableInstance) {
                        this.sortableInstance.destroy();
                    }

                    this.sortableInstance = new Sortable(container, {
                        animation: 150,
                        handle: '.drag-handle',
                        ghostClass: 'sortable-ghost',
                        chosenClass: 'sortable-chosen',
                        dragClass: 'sortable-drag',
                        onStart: () => {
                            this.originalOrder = this.getCurrentOrder();
                            document.body.classList.add('dragging-active');
                        },
                        onUpdate: () => {
                            this.currentOrder = this.getCurrentOrder();
                            this.showSaveButton = !this.areOrdersEqual(this.originalOrder, this.currentOrder);
                            this.updateSortOrderDisplay();
                        },
                        onEnd: () => {
                            document.body.classList.remove('dragging-active');
                        }
                    });

                    this.originalOrder = this.getCurrentOrder();
                    this.currentOrder = [...this.originalOrder];
                },

                getCurrentOrder() {
                    const container = this.$refs.sortableContainer;
                    if (!container) return [];

                    const items = container.querySelectorAll('.sortable-item');
                    return Array.from(items).map(item => {
                        const id = item.getAttribute('data-id');
                        return id ? parseInt(id) : null;
                    }).filter(id => id !== null);
                },

                areOrdersEqual(order1, order2) {
                    if (order1.length !== order2.length) return false;
                    return order1.every((id, index) => id === order2[index]);
                },

                updateSortOrderDisplay() {
                    const container = this.$refs.sortableContainer;
                    if (!container) return;

                    const items = container.querySelectorAll('.sortable-item');
                    items.forEach((item, index) => {
                        const cells = item.children;
                        if (cells.length > 8) {
                            const sortOrderCell = cells[8];
                            if (sortOrderCell) {
                                sortOrderCell.textContent = index + 1;
                            }
                        }
                    });
                },

                async saveSortOrder() {
                    this.isSaving = true;

                    try {
                        const sortData = this.currentOrder.map((id, index) => ({
                            id: id,
                            sort_order: index + 1
                        }));

                        const response = await this.$axios.post('{{ route("admin.reel.sort") }}', {
                            sort_order: sortData
                        });

                        this.$emitter.emit('add-flash', {
                            type: 'success',
                            message: response.data.message || 'Sort order saved successfully!'
                        });

                        this.showSaveButton = false;

                        this.sortableInstance.destroy();
                        this.sortableInstance = null;
                        this.originalOrder = [];
                        this.currentOrder = [];

                        await this.$refs.datagrid.get();

                        setTimeout(() => {
                            this.initializeSortable();
                        }, 500);

                    } catch (error) {
                        console.error('Error saving sort order:', error);
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: error.response?.data?.message || 'Failed to save sort order'
                        });
                    } finally {
                        this.isSaving = false;
                    }
                },

                handleVideoUpload(event) {
                    const file = event.target.files[0];
                    if (file) {
                        if (!file.type.startsWith('video/')) {
                            this.$emitter.emit('add-flash', {
                                type: 'error',
                                message: 'Please upload a valid video file (MP4, MOV, AVI)'
                            });
                            event.target.value = '';
                            return;
                        }

                        if (file.size > 100 * 1024 * 1024) {
                            this.$emitter.emit('add-flash', {
                                type: 'error',
                                message: 'Video file size should not exceed 100MB'
                            });
                            event.target.value = '';
                            return;
                        }

                        this.reel.video_url = URL.createObjectURL(file);

                        const video = document.createElement('video');
                        video.preload = 'metadata';
                        video.onloadedmetadata = () => {
                            this.reel.duration = Math.round(video.duration);
                            URL.revokeObjectURL(video.src);
                        };
                        video.onerror = () => {
                            console.log('Error loading video metadata');
                        };
                        video.src = URL.createObjectURL(file);
                    }
                },

                handleThumbnailUpload(event) {
                    const file = event.target.files[0];
                    if (file) {
                        if (!file.type.startsWith('image/')) {
                            this.$emitter.emit('add-flash', {
                                type: 'error',
                                message: 'Please upload a valid image file'
                            });
                            event.target.value = '';
                            return;
                        }

                        if (file.size > 5 * 1024 * 1024) {
                            this.$emitter.emit('add-flash', {
                                type: 'error',
                                message: 'Thumbnail file size should not exceed 5MB'
                            });
                            event.target.value = '';
                            return;
                        }

                        this.reel.thumbnail_url = URL.createObjectURL(file);
                    }
                },

                removeVideo() {
                    this.reel.video_url = '';
                    if (this.$refs.videoInput) {
                        this.$refs.videoInput.value = '';
                    }
                },

                removeThumbnail() {
                    this.reel.thumbnail_url = '';
                    const thumbnailInput = document.querySelector('input[name="thumbnail"]');
                    if (thumbnailInput) {
                        thumbnailInput.value = '';
                    }
                },

                previewVideo(videoUrl) {
                    this.previewVideoUrl = videoUrl;
                    this.$refs.videoPreviewModal.toggle();
                }
            }
        });
    </script>

    <style>
        /* Drag & Drop Styles */
        .sortable-ghost {
            opacity: 0.4;
            background-color: #f3f4f6 !important;
        }

        .dark .sortable-ghost {
            background-color: #374151 !important;
        }

        .sortable-chosen {
            background-color: #f9fafb !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .dark .sortable-chosen {
            background-color: #1f2937 !important;
        }

        .sortable-drag {
            opacity: 0.9;
            background-color: white !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            z-index: 9999 !important;
        }

        .dark .sortable-drag {
            background-color: #111827 !important;
        }

        .drag-handle {
            cursor: move !important;
            user-select: none;
            transition: all 0.2s;
        }

        .drag-handle:hover {
            color: #6b7280 !important;
            transform: scale(1.1);
        }

        .dark .drag-handle:hover {
            color: #d1d5db !important;
        }

        .dragging-active {
            cursor: grabbing !important;
        }

        .dragging-active * {
            cursor: grabbing !important;
        }

        /* Row hover effect */
        .row:hover {
            background-color: #f9fafb;
        }

        .dark .row:hover {
            background-color: #1f2937;
        }

        /* Action buttons */
        .icon-edit,
        .icon-delete {
            margin: 0 4px;
            padding: 4px;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .icon-edit:hover {
            background-color: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .icon-delete:hover {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        /* Header drag handle placeholder */
        .drag-handle-placeholder {
            display: inline-block;
            visibility: hidden;
        }

        /* Language tabs */
        #languageTabs {
            border-bottom: 1px solid #e5e7eb;
        }

        .dark #languageTabs {
            border-bottom-color: #374151;
        }
    </style>
    @endPushOnce
</x-admin::layouts>
