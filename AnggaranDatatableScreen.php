<?php

namespace App\Orchid\Screens\Anggaran;

use Orchid\Screen\Screen;
use App\View\Components\Datatable;
use Orchid\Support\Facades\Layout;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\User;

class AnggaranDatatableScreen extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'AnggaranDatatableScreen';

    /**
     * Query data.
     *
     * @return array
     */
    public function query(DataTables $dataTables): array
    {
        $model = User::query();
        $data =  $dataTables->eloquent($model)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $actionBtn = '<a href="javascript:void(0)" class="edit btn btn-success btn-sm">Edit</a> <a href="javascript:void(0)" class="delete btn btn-danger btn-sm">Delete</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action']);

        if (request()->ajax()) {
            echo $data->make(true)->content();
            die();
            // return $data_json;
        }else{
            $builder = $dataTables->getHtmlBuilder();

            $builder->ajax([
                'url' => route('platform.anggaran.list'),
                'type' => 'GET',
                'data' => 'function(d) { d.key = "value"; }', // you can add more key and value need to request in here
            ]);

            $builder->columns([
                ["title"=> "No", "data"=> 'id', "name"=> 'id'],
                ["title"=> "Name", "data"=> 'name', "name"=> 'name'],
                ["title"=> "Email", "data"=> 'email', "name"=> 'email'],
                ["title"=> "Action", "data"=> 'action', "name"=> 'action', "orderable"=> false, "searchable"=> false]
            ]);

            $plugins = [];

            $dependencies = [
                'scripts_before' => [], //array of url script will be load before datatable plugin
                'styles_before' => [], //array of url stylesheet will be load before datatable plugin
                'scripts_after' => [], //array of url script will be load after datatable plugin
                'styles_after' => [] //array of url stylesheet will be load after datatable plugin
            ];

            $html = $builder->table(['class' => 'table table-bordered'], true);
            $scripts = $builder->scripts();
            $styles = '';

            //if u want too add more html just add using $html .= 'your html';
            //if u want too add more scripts just add using $scripts .= '<script>your script</script>';

            return [
                'params' => [
                    'html' => $html,
                    'scripts'=> $scripts,
                    'styles' => $styles,
                    'plugins' => $plugins,
                    'dependencies' => $dependencies
                ],
            ];
        }

    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): array
    {
        return [
            Layout::component(Datatable::class),
        ];
    }
}
