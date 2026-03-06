<?php

namespace Webkul\Reel\DataGrids\Admin;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class ReelDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder()
    {
        $locale = app()->getLocale();

        $queryBuilder = DB::table('reels')
            ->leftJoin('reel_translations', function ($join) use ($locale) {
                $join->on('reels.id', '=', 'reel_translations.reel_id')
                    ->where('reel_translations.locale', '=', $locale);
            })
            ->leftJoin('admins', 'reels.created_by', '=', 'admins.id')
            ->leftJoin('product_flat', function ($join) use ($locale) {
                $join->on('reels.product_id', '=', 'product_flat.product_id')
                    ->where('product_flat.locale', '=', $locale);
            })
            ->select(
                'reels.id',
                'reels.sort_order',
                'reel_translations.title as title',
                'reel_translations.caption as caption',
                'reels.video_path',
                'reels.thumbnail_path',
                'reels.duration',
                'reels.is_active',
                'reels.views_count',
                'reels.likes_count',
                'reels.created_by',
                'admins.name as created_by_name',
                'reels.product_id',
                'product_flat.name as product_name',
                'reels.created_at',
                'reels.updated_at',
                'reels.deleted_at'
            )
            ->orderBy('reels.sort_order', 'asc');

        return $queryBuilder;
    }

    /**
     * Prepare columns.
     */
    public function prepareColumns()
    {
        // Add drag handle column as FIRST column
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

        // ID column
        $this->addColumn([
            'index'      => 'id',
            'label'      => trans('reel::app.admin.reels.datagrid.id'),
            'type'       => 'integer',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => false,
        ]);

        // Title column - now from translations
        $this->addColumn([
            'index'      => 'title',
            'label'      => trans('reel::app.admin.reels.datagrid.title'),
            'type'       => 'string',
            'searchable' => true,
            'sortable'   => true,
            'filterable' => false,
        ]);

        // Caption column - now from translations
        $this->addColumn([
            'index'      => 'caption',
            'label'      => trans('reel::app.admin.reels.datagrid.caption'),
            'type'       => 'string',
            'searchable' => true,
            'sortable'   => false,
            'filterable' => false,
            'closure'    => function ($row) {
                return $row->caption ? substr($row->caption, 0, 50) . (strlen($row->caption) > 50 ? '...' : '') : '—';
            },
        ]);

        // Video column
        $this->addColumn([
            'index'   => 'video_path',
            'label'   => trans('reel::app.admin.reels.datagrid.video'),
            'type'    => 'string',
            'escape'  => false,
            'closure' => function ($row) {
                if ($row->video_path) {
                    $url = asset('storage/' . $row->video_path);
                    return '<video width="120" height="90" controls>
                        <source src="' . $url . '" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>';
                }
                return '-';
            },
        ]);

        // Thumbnail column
        $this->addColumn([
            'index'   => 'thumbnail_path',
            'label'   => trans('reel::app.admin.reels.datagrid.thumbnail'),
            'type'    => 'string',
            'escape'  => false,
            'closure' => function ($row) {
                if ($row->thumbnail_path) {
                    $url = asset('storage/' . $row->thumbnail_path);
                    return '<img src="' . $url . '" alt="Thumbnail" style="width: 80px; height: auto; border-radius: 4px;">';
                }
                return '-';
            },
        ]);

        // Duration column
        $this->addColumn([
            'index'      => 'duration',
            'label'      => trans('reel::app.admin.reels.datagrid.duration'),
            'type'       => 'integer',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
            'closure'    => function ($row) {
                if ($row->duration) {
                    $minutes = floor($row->duration / 60);
                    $seconds = $row->duration % 60;
                    return sprintf('%d:%02d', $minutes, $seconds);
                }
                return '—';
            },
        ]);

        // FIXED: Status column - Changed type to 'boolean' and fixed filter options
        $this->addColumn([
            'index'              => 'is_active',
            'label'              => trans('reel::app.admin.reels.fields.status'),
            'type'               => 'string',
            'sortable'           => true,
            'filterable'         => true,
            'filterable_type'    => 'dropdown',
            'filterable_options' => [
                [
                    'label' => trans('reel::app.admin.reels.status.active'),
                    'value' => 1,
                ],
                [
                    'label' => trans('reel::app.admin.reels.status.inactive'),
                    'value' => 0,
                ],
            ],
            'escape' => false, // render HTML
            'closure' => function ($row) {
                return $row->is_active ? 'Active' : 'In Active';
            },
        ]);


        // Sort order column
        $this->addColumn([
            'index'      => 'sort_order',
            'label'      => trans('reel::app.admin.reels.datagrid.sort_order'),
            'type'       => 'integer',
            'sortable'   => true,
            'filterable' => true,
        ]);

        // Views count column
        $this->addColumn([
            'index'      => 'views_count',
            'label'      => trans('reel::app.admin.reels.datagrid.views'),
            'type'       => 'integer',
            'sortable'   => true,
            'filterable' => false,
            'closure'    => function ($row) {
                return number_format($row->views_count ?? 0);
            },
        ]);

        // Likes count column
        $this->addColumn([
            'index'      => 'likes_count',
            'label'      => trans('reel::app.admin.reels.datagrid.likes'),
            'type'       => 'integer',
            'sortable'   => true,
            'filterable' => false,
            'closure'    => function ($row) {
                return number_format($row->likes_count ?? 0);
            },
        ]);

        // Created by column
        $this->addColumn([
            'index'      => 'created_by_name',
            'label'      => trans('reel::app.admin.reels.datagrid.created_by'),
            'type'       => 'string',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => false,
            'closure'    => function ($row) {
                return $row->created_by_name ?? '—';
            },
        ]);

        // Product name column
        $this->addColumn([
            'index'      => 'product_name',
            'label'      => trans('reel::app.admin.reels.datagrid.product'),
            'type'       => 'string',
            'searchable' => true,
            'sortable'   => true,
            'filterable' => false,
            'closure'    => function ($row) {
                return $row->product_name ?? '—';
            },
        ]);

        // Created at column
        $this->addColumn([
            'index'      => 'created_at',
            'label'      => trans('reel::app.admin.reels.datagrid.created_at'),
            'type'       => 'datetime',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
        ]);
    }

    /**
     * Prepare actions.
     */
    public function prepareActions()
    {
        $this->addAction([
            'index'  => 'edit',
            'icon'   => 'icon-edit',
            'title'  => trans('reel::app.admin.reels.datagrid.edit'),
            'method' => 'GET',
            'url'    => function ($row) {
                return route('admin.reel.edit', $row->id);
            },
        ]);

        $this->addAction([
            'index'  => 'view',
            'icon'   => 'icon-view',
            'title'  => trans('reel::app.admin.reels.datagrid.view'),
            'method' => 'GET',
            'url'    => function ($row) {
                return route('admin.reel.show', $row->id);
            },
        ]);

        $this->addAction([
            'index'  => 'delete',
            'icon'   => 'icon-delete',
            'title'  => trans('reel::app.admin.reels.datagrid.delete'),
            'method' => 'DELETE',
            'url'    => function ($row) {
                return route('admin.reel.destroy', ['reel' => $row->id]);
            },
        ]);
    }

    /**
     * Prepare mass actions.
     */
    public function prepareMassActions()
    {
        // Uncomment these when you have the routes defined
        /*
        $this->addMassAction([
            'icon'   => 'icon-delete',
            'title'  => trans('reel::app.admin.reels.datagrid.delete'),
            'method' => 'DELETE',
            'url'    => route('admin.reel.mass_delete'),
        ]);

        $this->addMassAction([
            'icon'   => 'icon-eye',
            'title'  => trans('reel::app.admin.reels.datagrid.update_status'),
            'method' => 'POST',
            'url'    => route('admin.reel.mass_update_status'),
        ]);
        */
    }
}
