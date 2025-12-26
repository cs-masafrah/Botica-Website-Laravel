<?php

namespace Webkul\Reel\DataGrids\Admin;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;
use Webkul\Product\Models\Product;

class ReelDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder()
    {
        // Select all relevant columns from reels table
        // $queryBuilder = DB::table('reels')
        //     ->select(
        //         'id',
        //         'title',
        //         'caption',
        //         'video_path',
        //         'thumbnail_path',
        //         'duration',
        //         'is_active',
        //         'sort_order',
        //         'views_count',
        //         'likes_count',
        //         'created_by',
        //         'product_id',
        //     );

        $locale = app()->getLocale();

        $queryBuilder = DB::table('reels')
            ->leftJoin('admins', 'reels.created_by', '=', 'admins.id')
            ->leftJoin('product_flat', function ($join) use ($locale) {
                $join->on('reels.product_id', '=', 'product_flat.product_id')
                    ->where('product_flat.locale', '=', $locale);
            })

            ->select(
                'reels.id',
                'reels.title',
                'reels.caption',
                'reels.video_path',
                'reels.thumbnail_path',
                'reels.duration',
                'reels.is_active',
                'reels.sort_order',
                'reels.views_count',
                'reels.likes_count',
                'reels.created_by',
                'admins.name as created_by_name',
                'reels.product_id',
                'product_flat.name as product_name',  // <-- singular here!
                'reels.created_at',
                'reels.updated_at',
                'reels.deleted_at'
            );

        // dd($queryBuilder->get());

        return $queryBuilder;
    }

    /**
     * Prepare columns.
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index'      => 'id',
            'label'      => trans('reel::app.admin.reels.datagrid.id'),
            'type'       => 'integer',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => false,
        ]);

        $this->addColumn([
            'index'      => 'title',
            'label'      => trans('reel::app.admin.reels.datagrid.title'),
            'type'       => 'string',
            'searchable' => true,
            'sortable'   => true,
            'filterable' => false,
        ]);

        $this->addColumn([
            'index'      => 'caption',
            'label'      => trans('reel::app.admin.reels.datagrid.caption'),
            'type'       => 'string',
            'searchable' => true,
            'sortable'   => false,
            'filterable' => false,
        ]);

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


        $this->addColumn([
            'index'   => 'thumbnail_path',
            'label'   => trans('reel::app.admin.reels.datagrid.thumbnail'),
            'type'    => 'string',
            'escape'  => false,
            'closure' => function ($row) {
                if ($row->thumbnail_path) {
                    $url = asset('storage/' . $row->thumbnail_path); // or Storage facade URL method
                    return '<img src="' . $url . '" alt="Thumbnail" style="width: 80px; height: auto; border-radius: 4px;">';
                }
                return '-';
            },
        ]);


        $this->addColumn([
            'index'      => 'duration',
            'label'      => trans('reel::app.admin.reels.datagrid.duration'),
            'type'       => 'integer',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
        ]);

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

        $this->addColumn([
            'index'      => 'sort_order',
            'label'      => trans('reel::app.admin.reels.datagrid.sort_order'),
            'type'       => 'integer',
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'views_count',
            'label'      => trans('reel::app.admin.reels.datagrid.views'),
            'type'       => 'integer',
            'sortable'   => true,
            'filterable' => false,
        ]);

        $this->addColumn([
            'index'      => 'likes_count',
            'label'      => trans('reel::app.admin.reels.datagrid.likes'),
            'type'       => 'integer',
            'sortable'   => true,
            'filterable' => false,
        ]);

        $this->addColumn([
            'index'      => 'created_by_name',
            'label'      => trans('reel::app.admin.reels.datagrid.created_by'),
            'type'       => 'string',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => false,
        ]);

        $this->addColumn([
            'index'      => 'product_name',
            'label'      => trans('reel::app.admin.reels.datagrid.product'),
            'type'       => 'string',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => false,
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
            'icon'   => 'icon-view',
            'title'  => trans('reel::app.admin.reels.datagrid.view'),
            'method' => 'GET',
            'url'    => function ($row) {
                return route('admin.reel.show', $row->id);
            },
        ]);

        $this->addAction([
            'index'  => 'delete',  // <-- Add this line
            'icon'   => 'icon-delete',
            'title'  => trans('reel::app.admin.reels.datagrid.delete'),
            'method' => 'DELETE',
            'url'    => function ($row) {
                return route('admin.reel.destroy', ['reel' => $row->id]);
            },
        ]);
    }
}