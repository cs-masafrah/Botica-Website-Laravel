<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.settings.themes.index.title')
        </x-slot>

        <div class="flex items-center justify-between">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('admin::app.settings.themes.index.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <div class="flex items-center gap-x-2.5">
                    {!! view_render_event('bagisto.admin.settings.themes.create.before') !!}

                    <!-- Create Button -->
                    <v-create-theme-form>
                        <button type="button" class="primary-button">
                            @lang('admin::app.settings.themes.index.create-btn')
                        </button>
                    </v-create-theme-form>

                    {!! view_render_event('bagisto.admin.settings.themes.create.after') !!}

                    <!-- Save Sort Order Button -->
                    <button type="button" id="save-sort-order" class="hidden secondary-button">
                        @lang('admin::app.settings.themes.index.save-sort-order')
                    </button>
                </div>
            </div>
        </div>

        {!! view_render_event('bagisto.admin.settings.themes.list.before') !!}

        <!-- Datagrid Container -->
        <div id="themes-datagrid-container">
            <x-admin::datagrid :src="route('admin.settings.themes.index')" />
        </div>

        {!! view_render_event('bagisto.admin.settings.themes.list.after') !!}

        @pushOnce('scripts')
        <!-- Sortable.js Library -->
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

        <script type="text/x-template" id="v-create-theme-form-template">
            <div>
                <!-- Theme Create Button -->
                @if (bouncer()->hasPermission('settings.themes.create'))
                    <button
                        type="button"
                        class="primary-button"
                        @click="$refs.themeCreateModal.toggle()"
                    >
                        @lang('admin::app.settings.themes.index.create-btn')
                    </button>
                @endif

                <!-- Modal Form -->
                <x-admin::form
                    v-slot="{ meta, errors, handleSubmit }"
                    as="div"
                >
                    <form @submit="handleSubmit($event, create)">
                        <!-- Customer Create Modal -->
                        <x-admin::modal ref="themeCreateModal">
                            <!-- Modal Header -->
                            <x-slot:header>
                                <p class="text-lg font-bold text-gray-800 dark:text-white">
                                    @lang('admin::app.settings.themes.create.title')
                                </p>
                            </x-slot>

                            <!-- Modal Content -->
                            <x-slot:content>
                                <!-- Name -->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        @lang('admin::app.settings.themes.create.name')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="name"
                                        rules="required"
                                        :label="trans('admin::app.settings.themes.create.name')"
                                        :placeholder="trans('admin::app.settings.themes.create.name')"
                                    />

                                    <x-admin::form.control-group.error control-name="name" />
                                </x-admin::form.control-group>

                                <!-- Sort Order -->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        @lang('admin::app.settings.themes.create.sort-order')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="sort_order"
                                        rules="required|numeric"
                                        :label="trans('admin::app.settings.themes.create.sort-order')"
                                        :placeholder="trans('admin::app.settings.themes.create.sort-order')"
                                    />

                                    <x-admin::form.control-group.error control-name="sort_order" />
                                </x-admin::form.control-group>

                                <!-- Type -->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        @lang('admin::app.settings.themes.create.type.title')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="select"
                                        name="type"
                                        rules="required"
                                        value="product_carousel"
                                    >
                                        <option
                                            v-for="(type, key) in themeTypes"
                                            :value="key"
                                            :text="type"
                                        >
                                        </option>
                                    </x-admin::form.control-group.control>

                                    <x-admin::form.control-group.error control-name="type" />
                                </x-admin::form.control-group>

                                <!-- Channels -->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        @lang('admin::app.settings.themes.edit.channels')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="select"
                                        name="channel_id"
                                        rules="required"
                                        :value="1"
                                    >
                                        @foreach (core()->getAllChannels() as $channel)
                                            <option value="{{ $channel->id }}">{{ $channel->name }}</option>
                                        @endforeach
                                    </x-admin::form.control-group.control>

                                    <x-admin::form.control-group.error control-name="type" />
                                </x-admin::form.control-group>

                                 <!-- Theme Selector -->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        @lang('admin::app.settings.themes.create.themes')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="select"
                                        id="theme_code"
                                        name="theme_code"
                                        :value="config('themes.admin-default')"
                                        :label="trans('admin::app.settings.themes.create.themes')"
                                    >
                                        @foreach (config('themes.shop') as $themeCode => $theme)
                                            <option value="{{ $themeCode }}" {{ old('theme') == $themeCode ? 'selected' : '' }}>
                                                {{ $theme['name'] }}
                                            </option>
                                        @endforeach
                                    </x-admin::form.control-group.control>

                                    <x-admin::form.control-group.error control-name="theme" />
                                </x-admin::form.control-group>
                            </x-slot>

                             <!-- Modal Footer -->
                            <x-slot:footer>
                                <!-- Save Button -->
                                <x-admin::button
                                    button-type="submit"
                                    class="primary-button"
                                    :title="trans('admin::app.settings.themes.create.save-btn')"
                                    ::loading="isLoading"
                                    ::disabled="isLoading"
                                />
                            </x-slot>
                        </x-admin::modal>
                    </form>
                </x-admin::form>
            </div>
        </script>

        <script type="module">
            app.component('v-create-theme-form', {
                template: '#v-create-theme-form-template',

                data() {
                    return {
                        themeTypes: {
                            product_carousel: "@lang('admin::app.settings.themes.create.type.product-carousel')",
                            category_carousel: "@lang('admin::app.settings.themes.create.type.category-carousel')",
                            product_by_brand: "@lang('admin::app.settings.themes.create.type.product-by-brand')",
                            static_content: "@lang('admin::app.settings.themes.create.type.static-content')",
                            image_carousel: "@lang('admin::app.settings.themes.create.type.image-carousel')",
                            footer_links: "@lang('admin::app.settings.themes.create.type.footer-links')",
                            services_content: "@lang('admin::app.settings.themes.create.type.services-content')",
                        },

                        isLoading: false,
                    };
                },

                methods: {
                    create(params, { setErrors }) {
                        this.isLoading = true;

                        this.$axios.post('{{ route('admin.settings.themes.store') }}', params)
                            .then((response) => {
                                this.isLoading = false;

                                if (response.data.redirect_url) {
                                    window.location.href = response.data.redirect_url;
                                }
                            })
                            .catch((error) => {
                                this.isLoading = false;

                                if (error.response.status == 422) {
                                    setErrors(error.response.data.errors);
                                }
                            });
                    },
                },
            });
        </script>

        <script type="module">
            (function() {
        'use strict';

        console.log('=== THEME DRAG & DROP INITIALIZATION ===');

        let sortableInstance = null;
        let isInitialized = false;

        // ============================================
        // 1. SETUP SAVE BUTTON
        // ============================================
        function setupSaveButton() {
            const saveButton = document.getElementById('save-sort-order');

            if (!saveButton) {
                console.error('❌ Save button not found!');
                return;
            }

            console.log('✅ Save button found');

            saveButton.addEventListener('click', async function() {
                console.log('💾 Save button clicked');

                const sortData = collectSortData();
                console.log('📦 Sort data to send:', sortData);

                if (sortData.length === 0) {
                    alert('No data to save');
                    return;
                }

                // Show loading
                const button = this;
                const originalText = button.innerHTML;
                button.innerHTML = '⏳ Saving...';
                button.disabled = true;

                try {
                    // Get CSRF token
                    let csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

                    if (!csrfToken) {
                        csrfToken = document.querySelector('input[name="_token"]')?.value;
                    }

                    console.log('🔐 CSRF Token:', csrfToken ? 'Found' : 'Not found');

                    if (!csrfToken) {
                        throw new Error('CSRF token not found. Please refresh the page.');
                    }

                    // Send request as JSON
                    console.log('📤 Sending request to:', '{{ route('admin.settings.themes.sort') }}');

                    const response = await fetch('{{ route('admin.settings.themes.sort') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            _token: csrfToken,
                            sort_order: sortData
                        })
                    });

                    console.log('📥 Response status:', response.status);

                    if (!response.ok) {
                        const errorText = await response.text();
                        console.error('❌ Response error:', errorText);
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const data = await response.json();
                    console.log('📥 Response data:', data);

                    if (data.success) {
                        alert('✅ ' + (data.message || 'Sort order saved successfully!'));
                        button.classList.add('hidden');
                        window.location.reload(); // Reload to see updated order
                    } else {
                        throw new Error(data.message || 'Save failed');
                    }
                } catch (error) {
                    console.error('❌ Error:', error);
                    alert('❌ Error: ' + error.message);
                } finally {
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            });
        }

        // ============================================
        // 2. GET ALL DRAGGABLE ROWS
        // ============================================
        function getDraggableRows() {
            const allRows = document.querySelectorAll('.row');
            const draggableRows = [];

            allRows.forEach(row => {
                // Skip header rows
                const isHeader = row.classList.contains('font-semibold') ||
                                row.classList.contains('bg-gray-50') ||
                                row.textContent.includes('ID') ||
                                row.textContent.includes('Actions') ||
                                row.textContent.includes('Sort Order') ||
                                row.querySelector('.font-semibold');

                // Check if it has a drag handle (should be data rows)
                const hasDragHandle = row.querySelector('.drag-handle');

                if (!isHeader && hasDragHandle) {
                    draggableRows.push(row);
                }
            });

            console.log(`📊 Found ${draggableRows.length} draggable rows`);
            return draggableRows;
        }

        // ============================================
        // 3. GET ROWS CONTAINER
        // ============================================
        function getRowsContainer() {
            // Try to find the container with all the rows
            const container = document.querySelector('#themes-datagrid-container .table-responsive > div');

            if (container && container.querySelector('.row')) {
                console.log('✅ Found rows container');
                return container;
            }

            // Alternative: look for div that contains multiple .row elements
            const rows = document.querySelectorAll('.row');
            if (rows.length > 0) {
                // Find common parent of all rows
                const firstRowParent = rows[0].parentElement;
                let allSameParent = true;

                for (let i = 1; i < rows.length; i++) {
                    if (rows[i].parentElement !== firstRowParent) {
                        allSameParent = false;
                        break;
                    }
                }

                if (allSameParent && firstRowParent.querySelectorAll('.row').length === rows.length) {
                    console.log('✅ Found common parent container');
                    return firstRowParent;
                }
            }

            console.log('❌ No rows container found');
            return null;
        }

        // ============================================
        // 4. EXTRACT THEME ID FROM ROW
        // ============================================
        function extractThemeIdFromRow(row) {
            // Method 1: From checkbox value (most reliable)
            const checkbox = row.querySelector('input[type="checkbox"][name^="mass_action_select_record_"]');
            if (checkbox && checkbox.value) {
                console.log(`✅ Found ID from checkbox: ${checkbox.value}`);
                return checkbox.value;
            }

            // Method 2: Look for ID in cells
            const cells = row.querySelectorAll('p.break-words');
            if (cells.length >= 2) {
                // ID is usually in the 3rd cell (index 2 if we count from 0)
                // Let's check all cells for a number that looks like an ID
                for (let i = 0; i < cells.length; i++) {
                    const cell = cells[i];
                    const text = cell.textContent.trim();
                    if (/^\d+$/.test(text)) {
                        // Check if it's likely an ID (not sort order)
                        // IDs are usually lower numbers, but this is heuristic
                        console.log(`✅ Found ID from cell ${i}: ${text}`);
                        return text;
                    }
                }
            }

            console.log('❌ Could not extract theme ID from row');
            return null;
        }

        // ============================================
        // 5. INITIALIZE SORTABLE.JS
        // ============================================
        function initializeSortable() {
            console.log('🚀 Initializing Sortable.js...');

            const rowsContainer = getRowsContainer();
            if (!rowsContainer) {
                console.log('❌ Rows container not found');
                return;
            }

            // Destroy existing instance
            if (sortableInstance) {
                sortableInstance.destroy();
                sortableInstance = null;
            }

            try {
                // Initialize Sortable.js
                sortableInstance = Sortable.create(rowsContainer, {
                    animation: 150,
                    handle: '.drag-handle',
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    dragClass: 'sortable-drag',
                    draggable: '.row',
                    filter: '.font-semibold, .bg-gray-50', // Filter out header rows
                    preventOnFilter: true,
                    onStart: function(evt) {
                        console.log('👆 Drag started');
                        document.body.classList.add('dragging-active');
                        evt.item.classList.add('dragging');
                    },
                    onUpdate: function(evt) {
                        console.log('🔄 Order changed');
                        document.getElementById('save-sort-order').classList.remove('hidden');
                        updateSortOrderDisplay();
                    },
                    onEnd: function(evt) {
                        console.log('✅ Drag ended');
                        document.body.classList.remove('dragging-active');
                        evt.item.classList.remove('dragging');
                    }
                });

                console.log('🎉 Sortable.js initialized successfully!');
                isInitialized = true;

                // Show save button
                const saveButton = document.getElementById('save-sort-order');
                if (saveButton) {
                    saveButton.classList.remove('hidden');
                }

                // Show success message
                showNotification('Drag & drop is ready! Drag rows by the handle icon (☰) to reorder.', 'success');

            } catch (error) {
                console.error('❌ Sortable.js error:', error);
                showNotification('Failed to initialize drag & drop: ' + error.message, 'error');
            }
        }

        // ============================================
        // 6. UPDATE SORT ORDER DISPLAY
        // ============================================
        function updateSortOrderDisplay() {
            const draggableRows = getDraggableRows();

            draggableRows.forEach((row, index) => {
                // Find all number cells
                const cells = row.querySelectorAll('p.break-words');

                // Look for sort order cell (usually the 4th number cell)
                let numberCount = 0;
                for (let i = 0; i < cells.length; i++) {
                    const cell = cells[i];
                    const text = cell.textContent.trim();

                    if (/^\d+$/.test(text)) {
                        numberCount++;

                        // The 4th number is usually the sort order
                        if (numberCount === 4) {
                            const newValue = (index + 1).toString();
                            if (text !== newValue) {
                                cell.textContent = newValue;
                                console.log(`Updated row ${index + 1} sort order from ${text} to ${newValue}`);
                            }
                            break;
                        }
                    }
                }
            });

            console.log('📊 Updated sort order display');
        }

        // ============================================
        // 7. COLLECT SORT DATA
        // ============================================
        function collectSortData() {
            console.log('📦 Collecting sort data...');

            const draggableRows = getDraggableRows();
            const sortData = [];

            draggableRows.forEach((row, index) => {
                const themeId = extractThemeIdFromRow(row);

                if (themeId) {
                    const sortItem = {
                        id: parseInt(themeId),
                        sort_order: index + 1
                    };
                    sortData.push(sortItem);
                    console.log(`📝 Position ${index + 1}: ID=${themeId}, sort_order=${index + 1}`);
                } else {
                    console.warn(`⚠️ Could not find ID for row at position ${index + 1}`);
                }
            });

            console.log('📊 Final sort data:', sortData);

            if (sortData.length === 0) {
                console.error('❌ No sort data collected!');
                return [];
            }

            return sortData;
        }

        // ============================================
        // 8. NOTIFICATION HELPER
        // ============================================
        function showNotification(message, type = 'info') {
            const existing = document.querySelector('.drag-drop-notification');
            if (existing) existing.remove();

            const notification = document.createElement('div');
            notification.className = `drag-drop-notification fixed bottom-4 right-4 px-4 py-2 rounded-lg shadow-lg z-50 ${
                type === 'success' ? 'bg-green-100 text-green-800 border border-green-200' :
                type === 'error' ? 'bg-red-100 text-red-800 border border-red-200' :
                'bg-blue-100 text-blue-800 border border-blue-200'
            }`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <span class="mr-2">${type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️'}</span>
                    <span>${message}</span>
                </div>
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 5000);
        }

        // ============================================
        // 9. MAIN INITIALIZATION
        // ============================================
        function initDragAndDrop() {
            if (isInitialized) {
                console.log('⚠️ Already initialized, skipping...');
                return;
            }

            console.log('🎬 Starting drag & drop initialization...');

            // Setup save button
            setupSaveButton();

            // Initialize Sortable.js
            setTimeout(() => {
                initializeSortable();
            }, 500);
        }

        // ============================================
        // 10. START EVERYTHING
        // ============================================
        document.addEventListener('DOMContentLoaded', function() {
            console.log('📄 DOM loaded, starting initialization...');

            // Start after a short delay
            setTimeout(initDragAndDrop, 1000);
        });

    })();
</script>

        <style>
            /* Drag & Drop Styles */
            .sortable-ghost {
                opacity: 0.4;
                background-color: #f3f4f6 !important;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            }

            .dark .sortable-ghost {
                background-color: #374151 !important;
            }

            .sortable-chosen {
                background-color: #f9fafb !important;
                transform: scale(1.02);
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                z-index: 999;
            }

            .dark .sortable-chosen {
                background-color: #1f2937 !important;
            }

            .sortable-drag {
                opacity: 0.9 !important;
                background-color: white !important;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
                z-index: 9999 !important;
                transform: rotate(2deg);
            }

            .dark .sortable-drag {
                background-color: #111827 !important;
            }

            /* Drag Handle Styles */
            .drag-handle {
                cursor: move !important;
                user-select: none !important;
                transition: all 0.2s ease !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
            }

            .drag-handle:hover {
                color: #6b7280 !important;
                transform: scale(1.1) !important;
            }

            .dark .drag-handle:hover {
                color: #d1d5db !important;
            }

            .drag-handle:active {
                color: #3b82f6 !important;
                transform: scale(1.2) !important;
            }

            .dark .drag-handle:active {
                color: #60a5fa !important;
            }

            /* Row Styles */
            .dragging-active {
                cursor: grabbing !important;
            }

            .dragging-active * {
                cursor: grabbing !important;
            }

            .row {
                transition: background-color 0.2s, transform 0.2s, box-shadow 0.2s !important;
                position: relative !important;
            }

            .row:hover {
                background-color: #f9fafb !important;
            }

            .dark .row:hover {
                background-color: #1f2937 !important;
            }

            .row.dragging {
                background-color: #e5e7eb !important;
            }

            .dark .row.dragging {
                background-color: #374151 !important;
            }

            /* Ensure header row is not draggable */
            .row.font-semibold .drag-handle,
            .row.bg-gray-50 .drag-handle {
                cursor: default !important;
                opacity: 0.3 !important;
            }

            .row.font-semibold .drag-handle:hover,
            .row.bg-gray-50 .drag-handle:hover {
                transform: none !important;
                color: inherit !important;
            }

            /* Visual cue for draggable rows */
            .row:not(.font-semibold):not(.bg-gray-50) {
                cursor: move !important;
            }

        </style>
        @endPushOnce
</x-admin::layouts>
