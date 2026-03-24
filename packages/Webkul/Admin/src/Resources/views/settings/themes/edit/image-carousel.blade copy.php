<v-image-carousel :errors="errors">
    <x-admin::shimmer.settings.themes.image-carousel />
</v-image-carousel>

<!-- Image Carousel Vue Component -->
@pushOnce('scripts')
<script type="text/x-template" id="v-image-carousel-template">
    <div class="flex flex-col flex-1 gap-2 max-xl:flex-auto">
        <div class="p-4 bg-white rounded box-shadow dark:bg-gray-900">
            <div class="flex items-center justify-between gap-x-2.5">
                <div class="flex flex-col gap-1">
                    <p class="text-base font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.settings.themes.edit.slider')
                    </p>

                    <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                        @lang('admin::app.settings.themes.edit.slider-description')
                    </p>
                </div>

                <!-- Add Slider Button -->
                <div
                    class="secondary-button"
                    @click="$refs.addSliderModal.toggle()"
                >
                    @lang('admin::app.settings.themes.edit.slider-add-btn')
                </div>
            </div>

            <template v-for="(deletedSlider, index) in deletedSliders">
                <input
                    type="hidden"
                    :name="'{{ $currentLocale->code }}[deleted_sliders]['+ index +'][image]'"
                    :value="deletedSlider.image"
                />
            </template>

            <div
                class="grid pt-4"
                v-if="normalizedImages.length"
                v-for="(image, index) in normalizedImages"
                :key="index"
            >
                <!-- Hidden file input for new image (if replaced) -->
                <input
                    type="file"
                    class="hidden"
                    :name="'{{ $currentLocale->code }}[options]['+ index +'][image]'"
                    :ref="'imageInput_' + index"
                />

                <!-- Existing data (always present) -->
                <input
                    type="hidden"
                    :name="'{{ $currentLocale->code }}[options]['+ index +'][title]'"
                    :value="image.title"
                />
                <input
                    type="hidden"
                    :name="'{{ $currentLocale->code }}[options]['+ index +'][link]'"
                    :value="image.link"
                />
                <input
                    type="hidden"
                    :name="'{{ $currentLocale->code }}[options]['+ index +'][image]'"
                    :value="image.image"
                />

                <!-- New fields – always included with empty string fallback -->
                <input
                    type="hidden"
                    :name="'{{ $currentLocale->code }}[options]['+ index +'][sku]'"
                    :value="image.sku"
                />
                <input
                    type="hidden"
                    :name="'{{ $currentLocale->code }}[options]['+ index +'][category]'"
                    :value="image.category"
                />
                <input
                    type="hidden"
                    :name="'{{ $currentLocale->code }}[options]['+ index +'][brand]'"
                    :value="image.brand"
                />

                <!-- Details -->
                <div
                    class="flex cursor-pointer justify-between gap-2.5 py-5"
                    :class="{
                        'border-b border-slate-300 dark:border-gray-800': index < normalizedImages.length - 1
                    }"
                >
                    <div class="flex gap-2.5">
                        <div class="grid place-content-start gap-1.5">
                            <p class="text-gray-600 dark:text-gray-300">
                                @lang('admin::app.settings.themes.edit.image-title'):
                                <span class="text-gray-600 transition-all dark:text-gray-300">@{{ image.title }}</span>
                            </p>

                            <p class="text-gray-600 dark:text-gray-300">
                                @lang('admin::app.settings.themes.edit.link'):
                                <span class="text-gray-600 transition-all dark:text-gray-300">@{{ image.link }}</span>
                            </p>

                            <p v-if="image.sku" class="text-gray-600 dark:text-gray-300">
                                @lang('admin::app.settings.themes.edit.sku'):
                                <span class="text-gray-600 transition-all dark:text-gray-300">@{{ image.sku }}</span>
                            </p>
                            <p v-if="image.category" class="text-gray-600 dark:text-gray-300">
                                @lang('admin::app.settings.themes.edit.category'):
                                <span class="text-gray-600 transition-all dark:text-gray-300">@{{ image.category }}</span>
                            </p>
                            <p v-if="image.brand" class="text-gray-600 dark:text-gray-300">
                                @lang('admin::app.settings.themes.edit.brand'):
                                <span class="text-gray-600 transition-all dark:text-gray-300">@{{ image.brand }}</span>
                            </p>

                            <p class="text-gray-600 dark:text-gray-300">
                                @lang('admin::app.settings.themes.edit.image'):
                                <span class="text-gray-600 transition-all dark:text-gray-300">
                                    <a
                                        :href="'{{ config('app.url') }}/' + image.image"
                                        :ref="'image_' + index"
                                        target="_blank"
                                        class="text-blue-600 transition-all hover:underline ltr:ml-2 rtl:mr-2"
                                    >
                                        <span :ref="'imageName_' + index">
                                            @{{ image.image }}
                                        </span>
                                    </a>
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-1 text-right place-content-start">
                        <p
                            class="text-red-600 transition-all cursor-pointer hover:underline"
                            @click="remove(image)"
                        >
                            @lang('admin::app.settings.themes.edit.delete')
                        </p>
                    </div>
                </div>
            </div>

            <!-- Empty Page -->
            <div
                class="grid justify-center justify-items-center gap-3.5 px-2.5 py-10"
                v-else
            >
                <img
                    class="h-[120px] w-[120px] p-2 dark:mix-blend-exclusion dark:invert"
                    src="{{ bagisto_asset('images/empty-placeholders/default.svg') }}"
                    alt="@lang('admin::app.settings.themes.edit.slider')"
                >
                <div class="flex flex-col items-center gap-1.5">
                    <p class="text-base font-semibold text-gray-400">
                        @lang('admin::app.settings.themes.edit.slider-add-btn')
                    </p>
                    <p class="text-gray-400">
                        @lang('admin::app.settings.themes.edit.slider-description')
                    </p>
                </div>
            </div>
        </div>

        <!-- Modal form (separate from the main form) -->
        <x-admin::form
            v-slot="{ meta, errors, handleSubmit }"
            as="div"
        >
            <form
                @submit="handleSubmit($event, saveSliderImage)"
                enctype="multipart/form-data"
                ref="createSliderForm"
            >
                <x-admin::modal ref="addSliderModal">
                    <x-slot:header>
                        <p class="text-lg font-bold text-gray-800 dark:text-white">
                            @lang('admin::app.settings.themes.edit.update-slider')
                        </p>
                    </x-slot>

                    <x-slot:content>
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.themes.edit.image-title')
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="text"
                                name="{{ $currentLocale->code }}[title]"
                                rules="required"
                                :placeholder="trans('admin::app.settings.themes.edit.image-title')"
                                :label="trans('admin::app.settings.themes.edit.image-title')"
                            />
                            <x-admin::form.control-group.error control-name="{{ $currentLocale->code }}[title]" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('admin::app.settings.themes.edit.link')
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="text"
                                name="{{ $currentLocale->code }}[link]"
                                :placeholder="trans('admin::app.settings.themes.edit.link')"
                            />
                        </x-admin::form.control-group>

                        <!-- New nullable fields -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('admin::app.settings.themes.edit.sku')
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="text"
                                name="{{ $currentLocale->code }}[sku]"
                                :placeholder="trans('admin::app.settings.themes.edit.sku')"
                            />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('admin::app.settings.themes.edit.category')
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="text"
                                name="{{ $currentLocale->code }}[category]"
                                :placeholder="trans('admin::app.settings.themes.edit.category')"
                            />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('admin::app.settings.themes.edit.brand')
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="text"
                                name="{{ $currentLocale->code }}[brand]"
                                :placeholder="trans('admin::app.settings.themes.edit.brand')"
                            />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.themes.edit.slider-image')
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="image"
                                name="slider_image"
                                rules="required"
                                :is-multiple="false"
                            />
                            <x-admin::form.control-group.error control-name="slider_image" />
                        </x-admin::form.control-group>

                        <p class="text-xs text-gray-600 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.image-size')
                        </p>
                    </x-slot>

                    <x-slot:footer>
                        <x-admin::button
                            button-type="submit"
                            class="justify-center primary-button"
                            :title="trans('admin::app.settings.themes.edit.save-btn')"
                        />
                    </x-slot>
                </x-admin::modal>
            </form>
        </x-admin::form>
    </div>
</script>

<script type="module">
    app.component('v-image-carousel', {
        template: '#v-image-carousel-template',

        props: ['errors'],

        data() {
            return {
                sliders: @json($theme->translate($currentLocale->code)['options'] ?? null),
                deletedSliders: [],
            };
        },

        computed: {
            // Normalize images to always have the nullable fields
            normalizedImages() {
                if (!this.sliders.images) return [];

                return this.sliders.images.map(image => ({
                    sku: image.sku ?? '',
                    category: image.category ?? '',
                    brand: image.brand ?? '',
                    ...image,
                }));
            }
        },

        created() {
            // Ensure sliders has the correct structure
            if (!this.sliders) {
                this.sliders = { images: [] };
            } else if (Array.isArray(this.sliders)) {
                this.sliders = { images: this.sliders };
            } else if (!this.sliders.images) {
                this.sliders.images = [];
            }
        },

        methods: {
            saveSliderImage(params, { resetForm, setErrors }) {
                let formData = new FormData(this.$refs.createSliderForm);

                try {
                    const sliderImage = formData.get("slider_image[]");
                    if (!sliderImage) {
                        throw new Error("{{ trans('admin::app.settings.themes.edit.slider-required') }}");
                    }

                    const title = formData.get("{{ $currentLocale->code }}[title]");
                    const link = formData.get("{{ $currentLocale->code }}[link]");
                    const sku = formData.get("{{ $currentLocale->code }}[sku]");
                    const category = formData.get("{{ $currentLocale->code }}[category]");
                    const brand = formData.get("{{ $currentLocale->code }}[brand]");

                    // Push new slider – the computed property will automatically add missing fields
                    this.sliders.images.push({
                        title,
                        link,
                        sku,
                        category,
                        brand,
                        image: sliderImage,  // temporary file object
                    });

                    if (sliderImage instanceof File) {
                        this.setFile(sliderImage, this.sliders.images.length - 1);
                    }

                    resetForm();
                    this.$refs.addSliderModal.toggle();
                } catch (error) {
                    setErrors({ 'slider_image': [error.message] });
                }
            },

            setFile(file, index) {
                let dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);

                this.$nextTick(() => {
                    if (this.$refs['image_' + index]) {
                        this.$refs['image_' + index][0].href = URL.createObjectURL(file);
                        this.$refs['imageName_' + index][0].innerHTML = file.name;
                        this.$refs['imageInput_' + index][0].files = dataTransfer.files;
                    }
                });
            },

            remove(image) {
                this.$emitter.emit('open-confirm-modal', {
                    agree: () => {
                        // Find the original image object (by title+link+image)
                        const originalImage = this.sliders.images.find(item =>
                            item.title === image.title &&
                            item.link === image.link &&
                            item.image === image.image
                        );
                        if (originalImage) {
                            this.deletedSliders.push(originalImage);
                            this.sliders.images = this.sliders.images.filter(item => item !== originalImage);
                        }
                    }
                });
            },
        },
    });
</script>
@endPushOnce
