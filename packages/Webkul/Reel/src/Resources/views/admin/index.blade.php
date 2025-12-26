<x-admin::layouts>
    <x-slot:title>
        @lang('reel::app.admin.reels.title')
        </x-slot>

        {!! view_render_event('bagisto.admin.reels.create.before') !!}

        <v-reels>
            <!-- DataGrid Shimmer will show while loading -->
            <x-admin::datagrid :src="route('admin.reel.index')" ref="datagrid" />
        </v-reels>

        {!! view_render_event('bagisto.admin.reels.create.after') !!}

        @pushOnce('scripts')
        <script type="text/x-template" id="v-reels-template">
            <div>
                <!-- Header with Create Button -->
                <div class="flex items-center justify-between gap-4 max-sm:flex-wrap mb-4">
                    <p class="text-xl font-bold text-gray-800 dark:text-white">
                        @lang('reel::app.admin.reels.title')
                    </p>

                    <div class="flex items-center gap-x-2.5">
                        @if (bouncer()->hasPermission('reel.create'))
                            <button
                                type="button"
                                class="primary-button"
                                @click="resetForm(); $refs.reelUpdateOrCreateModal.toggle()"
                            >
                                @lang('reel::app.admin.reels.create.title')
                            </button>
                        @endif
                    </div>
                </div>

                <!-- DataGrid -->
                <x-admin::datagrid
                    :src="route('admin.reel.index')"
                    ref="datagrid"
                >
                    <!-- DataGrid Header -->
                    <template #header>
                        <div
                            class="row grid font-medium border-b bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900"
                            style="grid-template-columns: repeat(10, minmax(0, 1fr))"
                        >
                            <p>ID</p>
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
                                v-for="record in available.records"
                                class="row grid items-center gap-2.5 border-b px-4 py-4 dark:border-gray-800"
                                style="grid-template-columns: repeat(10, minmax(0, 1fr))"
                            >
                                <p>@{{ record.id }}</p>
                                <p>@{{ record.title }}</p>
                                <p class="truncate max-w-xs">@{{ record.caption }}</p>
                                <p v-if="record.product_name">@{{ record.product_name }}</p>
                                <p v-else>@lang('reel::app.admin.reels.datagrid.na')</p>
                                <p>@{{ record.duration }}s</p>
                                <span v-if="console.log(record.is_active)"></span>
                               <span
  :class="[
    'px-2 py-1 rounded-md text-xs',
    record.is_active === 'Active'
      ? 'bg-green-100 text-green-800'
      : 'bg-red-100 text-red-800'
  ]"
>
  @{{ record.is_active === 'Active' ? 'Active' : 'Inactive' }}
</span>

                                <p>@{{ record.views_count }}</p>
                                <p>@{{ record.likes_count }}</p>
                                <p>@{{ record.sort_order }}</p>
                                <div class="flex justify-end gap-2">
                                    <!-- Edit Button - Opens Edit Modal -->
                                    <a
                                        v-if="record.actions && record.actions.find(a => a.index === 'edit')"
                                        @click="editReel(record.id)"
                                        class="cursor-pointer"
                                        title="Edit"
                                    >
                                        <span class="icon-edit text-2xl text-blue-600 hover:text-blue-800"></span>
                                    </a>

                                    <!-- Delete Button - Uses Datagrid's performAction -->
                                    <a
                                        v-if="record.actions && record.actions.find(a => a.index === 'delete')"
                                        @click="performAction(record.actions.find(a => a.index === 'delete'))"
                                        class="cursor-pointer"
                                        title="Delete"
                                    >
                                        <span class="icon-delete text-2xl text-red-600 hover:text-red-800"></span>
                                    </a>
                                </div>
                            </div>
                        </template>
                    </template>
                </x-admin::datagrid>

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

                                <!-- Title -->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        @lang('reel::app.admin.reels.fields.title')
                                    </x-admin::form.control-group.label>
                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="title"
                                        rules="required|max:255"
                                        v-model="reel.title"
                                        placeholder="{{ __('reel::app.admin.reels.fields.title') }}"
                                    />
                                    <x-admin::form.control-group.error control-name="title" />
                                </x-admin::form.control-group>

                                <!-- Caption -->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label>
                                        @lang('reel::app.admin.reels.fields.caption')
                                    </x-admin::form.control-group.label>
                                    <x-admin::form.control-group.control
                                        type="textarea"
                                        name="caption"
                                        v-model="reel.caption"
                                        placeholder="{{__('reel::app.admin.reels.fields.caption')}}"
                                        rows="3"
                                    />
                                    <x-admin::form.control-group.error control-name="caption" />
                                </x-admin::form.control-group>

                                <!-- Video Upload -->
                                <x-admin::form.control-group>
                                    <!-- FIXED: Remove the Blade syntax and use simple Vue -->
                                    {{-- <x-admin::form.control-group.label :class="reel.id ? '' : 'required'">
                                        @lang('reel::app.admin.reels.fields.video')
                                    </x-admin::form.control-group.label> --}}
                                    
                                    <div v-if="reel.video_url" class="mb-2">
                                        <video
                                            :src="reel.video_url"
                                            class="w-32 h-32 object-cover rounded-lg mb-2"
                                            controls
                                        ></video>
                                        <button
                                            type="button"
                                            @click="removeVideo"
                                            class="text-red-600 text-sm hover:text-red-800"
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
                                        :rules="reel.id ? '' : 'required'"
                                        class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                    />
                                    <p class="text-xs text-gray-600 dark:text-gray-300 mt-1">
                                        @lang('reel::app.admin.reels.messages.video-size')
                                        <span v-if="reel.id"> (Optional for updates)</span>
                                    </p>
                                    <x-admin::form.control-group.error control-name="video" />
                                </x-admin::form.control-group>

                                <!-- Thumbnail Upload -->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label>
                                        @lang('reel::app.admin.reels.fields.thumbnail')
                                    </x-admin::form.control-group.label>
                                    
                                    <div v-if="reel.thumbnail_url" class="mb-2">
                                        <img
                                            :src="reel.thumbnail_url"
                                            class="w-32 h-32 object-cover rounded-lg mb-2"
                                            :alt="reel.title"
                                        >
                                        <button
                                            type="button"
                                            @click="removeThumbnail"
                                            class="text-red-600 text-sm hover:text-red-800"
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
                                    <p class="text-xs text-gray-600 dark:text-gray-300 mt-1">
                                        @lang('reel::app.admin.reels.messages.thumbnail-size')
                                    </p>
                                    <x-admin::form.control-group.error control-name="thumbnail" />
                                </x-admin::form.control-group>

                                <!-- Duration -->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label>
                                        @lang('reel::app.admin.reels.fields.duration')
                                    </x-admin::form.control-group.label>
                                    <x-admin::form.control-group.control
                                        type="number"
                                        name="duration"
                                        v-model="reel.duration"
                                        placeholder="@lang('reel::app.admin.reels.fields.duration')"
                                        step="0.01"
                                    />
                                    <x-admin::form.control-group.error control-name="duration" />
                                </x-admin::form.control-group>

                                <!-- Status -->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label>
                                        @lang('reel::app.admin.reels.fields.is_active')
                                    </x-admin::form.control-group.label>
                                    <x-admin::form.control-group.control
                                        type="select"
                                        name="is_active"
                                        v-model="reel.is_active"
                                    >
                                        <option value="1">@lang('reel::app.admin.reels.status.active')</option>
                                        <option value="0">@lang('reel::app.admin.reels.status.inactive')</option>
                                    </x-admin::form.control-group.control>
                                    <x-admin::form.control-group.error control-name="is_active" />
                                </x-admin::form.control-group>

                                <!-- Sort Order -->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label>
                                        @lang('reel::app.admin.reels.fields.sort_order')
                                    </x-admin::form.control-group.label>
                                    <x-admin::form.control-group.control
                                        type="number"
                                        name="sort_order"
                                        v-model="reel.sort_order"
                                        placeholder="@lang('reel::app.admin.reels.fields.sort_order')"
                                    />
                                    <x-admin::form.control-group.error control-name="sort_order" />
                                </x-admin::form.control-group>
                            </x-slot>

                            <x-slot:footer>
                                <button
                                    type="submit"
                                    class="primary-button"
                                    :disabled="isLoading"
                                >
                                    <span v-if="isLoading">
                                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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
                            title: '',
                            caption: '',
                            video_url: '',
                            thumbnail_url: '',
                            duration: 0,
                            is_active: 1,
                            sort_order: 0,
                        },
                        previewVideoUrl: '',
                        isLoading: false,
                    }
                },

                methods: {
                    async createOrUpdate(params, { resetForm, setErrors }) {
                        this.isLoading = true;

                        const formData = new FormData(this.$refs.createReelForm);
                        
                        // For update, add _method PUT
                        if (this.reel.id) {
                            formData.append('_method', 'PUT');
                        }

                        try {
                            let url;
                            console.log(this.reel)
                            if (this.reel.id) {
                                // For update
                                url = "{{ route('admin.reel.update', ['reel' => '__ID__']) }}".replace('__ID__', this.reel.id);
                            } else {
                                // For create
                                url = "{{ route('admin.reel.store') }}";
                            }

                            console.log('Sending request to:', url);

                            const response = await this.$axios.post(url, formData, {
                                headers: { 'Content-Type': 'multipart/form-data' }
                            });

                            console.log('Response:', response.data);

                            this.$refs.reelUpdateOrCreateModal.close();
                            this.$emitter.emit('add-flash', { 
                                type: 'success', 
                                message: response.data.message || 'Operation successful' 
                            });
                            this.$refs.datagrid.get();
                            this.resetForm();
                        } catch (error) {
                            console.error('Error:', error);
                            if (error.response?.status === 422) {
                                setErrors(error.response.data.errors);
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

                    editReel(reelId) {
                        console.log('Editing reel ID:', reelId);
                        
                        // Construct the edit URL
                        const url = "{{ route('admin.reel.edit', ['reel' => '__ID__']) }}".replace('__ID__', reelId);
                        
                        console.log('Fetching from URL:', url);
                        
                        this.$axios.get(url)
                            .then(response => {
                                console.log('Edit response:', response.data);
                                
                                // Check if data is in response.data or response.data.data
                                const data = response.data.data || response.data;
                                
                                if (!data) {
                                    throw new Error('No data received from server');
                                }
                                console.log('Reel data set before:', this.reel);
                                this.reel = {
                                    id: data.id,
                                    title: data.title || '',
                                    caption: data.caption || '',
                                    video_url: data.video_url || data.video_path || '',
                                    thumbnail_url: data.thumbnail_url || data.thumbnail_path || '',
                                    duration: data.duration || 0,
                                    is_active: data.is_active ?? 1,
                                    sort_order: data.sort_order || 0,
                                };
                                
                                console.log('Reel data set:', this.reel);
                                this.$refs.reelUpdateOrCreateModal.toggle();
                            })
                            .catch(error => {
                                console.error('Edit error details:', error);
                                console.error('Error response:', error.response);
                                
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
                            });
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
                            
                            // Extract duration from video
                            const video = document.createElement('video');
                            video.preload = 'metadata';
                            video.onloadedmetadata = () => {
                                this.reel.duration = Math.round(video.duration);
                                URL.revokeObjectURL(video.src); // Clean up
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
                    },

                    previewVideo(videoUrl) {
                        this.previewVideoUrl = videoUrl;
                        this.$refs.videoPreviewModal.toggle();
                    },

                    resetForm() {
                        this.reel = {
                            id: null,
                            title: '',
                            caption: '',
                            video_url: '',
                            thumbnail_url: '',
                            duration: 0,
                            is_active: 1,
                            sort_order: 0,
                        };
                        
                        // Reset file inputs
                        if (this.$refs.videoInput) {
                            this.$refs.videoInput.value = '';
                        }
                        
                        // Find and reset thumbnail input
                        const thumbnailInput = document.querySelector('input[name="thumbnail"]');
                        if (thumbnailInput) {
                            thumbnailInput.value = '';
                        }
                    }
                }
            });
        </script>
        @endPushOnce
</x-admin::layouts>
