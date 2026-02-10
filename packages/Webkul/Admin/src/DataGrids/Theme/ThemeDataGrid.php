<?php

namespace Webkul\Admin\DataGrids\Theme;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class ThemeDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    // public function prepareQueryBuilder()
    // {
    //     // Simplify the query to avoid any join issues
    //     $queryBuilder = DB::table('theme_customizations')
    //         ->select(
    //             'id',
    //             'type',
    //             'sort_order',
    //             'status',
    //             'name as theme_customization_name',
    //             'theme_code',
    //             'channel_id'
    //         );

    //     $this->addFilter('id', 'theme_customizations.id');
    //     $this->addFilter('type', 'theme_customizations.type');
    //     $this->addFilter('theme_customization_name', 'theme_customizations.name');
    //     $this->addFilter('sort_order', 'theme_customizations.sort_order');
    //     $this->addFilter('status', 'theme_customizations.status');
    //     $this->addFilter('theme_code', 'theme_customizations.theme_code');

    //     return $queryBuilder;
    // }

    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('theme_customizations')
            ->select(
                'id',
                'type',
                'sort_order',
                'status',
                'name as theme_customization_name',
                'theme_code',
                'channel_id'
            )
            ->orderBy('sort_order', 'asc'); // Add this line to sort by sort_order

        $this->addFilter('id', 'theme_customizations.id');
        $this->addFilter('type', 'theme_customizations.type');
        $this->addFilter('theme_customization_name', 'theme_customizations.name');
        $this->addFilter('sort_order', 'theme_customizations.sort_order');
        $this->addFilter('status', 'theme_customizations.status');
        $this->addFilter('theme_code', 'theme_customizations.theme_code');

        return $queryBuilder;
    }

    /**
     * Add columns.
     *
     * @return void
     */
    public function prepareColumns()
    {
        $themes = config('themes.shop', []);

        // Drag Handle Column - MUST BE FIRST
        $this->addColumn([
            'index'      => 'drag_handle',
            'label'      => ' ',
            'type'       => 'string',
            'searchable' => false,
            'filterable' => false,
            'sortable'   => false,
            'width'      => '50px',
            'closure'    => function ($row) {
                return '<div class="drag-handle cursor-move text-gray-400 hover:text-gray-600 dark:text-gray-300 dark:hover:text-gray-100 p-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                    </svg>
                </div>';
            },
        ]);

        $this->addColumn([
            'index'      => 'id',
            'label'      => 'ID',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
            'width'      => '80px',
        ]);

        $this->addColumn([
            'index'      => 'sort_order',
            'label'      => trans('admin::app.settings.themes.index.datagrid.sort-order'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
            'width'      => '100px',
        ]);

        $this->addColumn([
            'index'      => 'theme_customization_name',
            'label'      => trans('admin::app.settings.themes.index.datagrid.name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'type',
            'label'      => trans('admin::app.settings.themes.index.datagrid.type'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'              => 'theme_code',
            'label'              => trans('admin::app.settings.themes.index.datagrid.theme'),
            'type'               => 'string',
            'filterable'         => true,
            'filterable_type'    => 'dropdown',
            'filterable_options' => collect($themes)
                ->map(fn($theme, $code) => ['label' => $theme['name'] ?? $code, 'value' => $code])
                ->values()
                ->toArray(),
            'closure' => function ($row) use ($themes) {
                return $themes[$row->theme_code]['name'] ?? $row->theme_code ?? 'N/A';
            },
            'sortable'           => true,
        ]);

        $this->addColumn([
            'index'              => 'status',
            'label'              => trans('admin::app.settings.themes.index.datagrid.status'),
            'type'               => 'boolean',
            'searchable'         => true,
            'filterable'         => true,
            'filterable_options' => [
                [
                    'label' => trans('admin::app.settings.themes.index.datagrid.active'),
                    'value' => 1,
                ],
                [
                    'label' => trans('admin::app.settings.themes.index.datagrid.inactive'),
                    'value' => 0,
                ],
            ],
            'sortable'   => true,
            'closure'    => function ($value) {
                if ($value->status) {
                    return '<p class="label-active">' . trans('admin::app.settings.themes.index.datagrid.active') . '</p>';
                }

                return '<p class="label-pending">' . trans('admin::app.settings.themes.index.datagrid.inactive') . '</p>';
            },
        ]);
    }

    public function prepareActions()
    {
        if (bouncer()->hasPermission('settings.themes.edit')) {
            $this->addAction([
                'icon'   => 'icon-edit',
                'title'  => trans('admin::app.settings.themes.index.datagrid.view'),
                'method' => 'GET',
                'url'    => function ($row) {
                    return route('admin.settings.themes.edit', $row->id);
                },
            ]);
        }

        if (bouncer()->hasPermission('settings.themes.delete')) {
            $this->addAction([
                'icon'   => 'icon-delete',
                'title'  => trans('admin::app.settings.themes.index.datagrid.delete'),
                'method' => 'DELETE',
                'url'    => function ($row) {
                    return route('admin.settings.themes.delete', $row->id);
                },
            ]);
        }
    }

    /**
     * Prepare mass actions.
     *
     * @return void
     */
    public function prepareMassActions()
    {
        if (bouncer()->hasPermission('settings.themes.edit')) {
            $this->addMassAction([
                'title'   => trans('admin::app.settings.themes.index.datagrid.change-status'),
                'url'     => route('admin.settings.themes.mass_update'),
                'method'  => 'POST',
                'options' => [
                    [
                        'label'  => trans('admin::app.settings.themes.index.datagrid.active'),
                        'value'  => 1,
                    ],
                    [
                        'label'  => trans('admin::app.settings.themes.index.datagrid.inactive'),
                        'value'  => 0,
                    ],
                ],
            ]);
        }

        if (bouncer()->hasPermission('settings.themes.delete')) {
            $this->addMassAction([
                'title'  => trans('admin::app.settings.themes.index.datagrid.delete'),
                'url'    => route('admin.settings.themes.mass_delete'),
                'method' => 'POST',
            ]);
        }
    }
}