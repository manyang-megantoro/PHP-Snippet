<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Orchid\Platform\Dashboard;

class Datatable extends Component
{
    /**
     * @var array
     */
    public $params;

    /**
     * Create a new component instance.
     * params to
     * 'html' => ['source'=>'html','html' => $html, "table_id" => $table_id]
     * 'javascript' => ['source'=>'javascript','json' => ['data_json' => $data_json, 'column_json' => $column_json ]
     * 'ajax-client' => ['source'=>'ajax-client', 'html' => $html, "table_id" => $table_id, 'url' => $url]
     * 'ajax-server' => ['source'=>'ajax-server', 'html' => $html, "table_id" => $table_id, 'url' => $url]
     * 'other' => ['html' => $html,'scripts'=>$scripts]
     *
     * TO DO : plugins, styling
     * @return void
     */
    public function __construct(Dashboard $dashboard, array $params){
        $table_id = 'datatable-'.uniqid();
        $default_data = [
            'styling' => 'jquery',
            'jquery' => false,
            'source' => '', // if using native html, javascript, ajax-client(file), ajax-server, api
            'url' => '', // if using source ajax/api
            'json'=> [
                'data_json' => '{}', // if using source javascript
                'column_json' => '[]', // if using source javascript
            ],
            'table_id' => $table_id,
            'html' => '<table id="'.$table_id.'" class="table table-bordered table-responsive"></table>', //if using source builder as type
            'scripts' => '', //must be with <script> tag
            'styles' => '', //optional, if any must be with <style> tag
            'plugins' => [], //optional
            'dependencies' => [
                'scripts_before' => [], //array of url script will be load before datatable plugin
                'styles_before' => [], //array of url stylesheet will be load before datatable plugin
                'scripts_after' => [], //array of url script will be load after datatable plugin
                'styles_after' => [] //array of url stylesheet will be load after datatable plugin
            ] //optional
        ];
        $params = array_merge ($default_data,$params);


        $resouces_url = '';
        if ($mixUrl = config('app.mix_url', false)) {
            $resouces_url = $mixUrl;
        }elseif (file_exists(public_path('/resources'))) {
            $resouces_url = url()->asset("/resources");
        }

        //if jquery needed add jquery script
        if($params['jquery']) $dashboard->registerResource('scripts', $resouces_url."/js/jquery.min.js");

        //add dependencies needed before datatable plugin

        if(!empty($params['dependencies']['styles_before'])){
            foreach ($params['dependencies']['styles_before'] as $dep_style) {
                $dashboard->registerResource('stylesheets', $dep_style);
            }
        }

        if(!empty($params['dependencies']['scripts_before'])){
            foreach ($params['dependencies']['scripts_before'] as $dep_script) {
                $dashboard->registerResource('scripts', $dep_script);
            }
        }

        //add main script and style
        $scripts = ['datatable' => $resouces_url."/js/jquery.dataTables.min.js"];
        $styles = ['datatable' => $resouces_url."/css/dataTables.dataTables.min.css"];

        //add styling
        switch ($params['styling']) {
            case 'bootstrap-4':
                $scripts['datatable_bootstrap_4'] = $resouces_url."/js/dataTables.bootstrap4.min.js";
                $styles['datatable_bootstrap_4'] = $resouces_url."/css/dataTables.bootstrap4.min.css";
                break;
        }

        if(!empty($params['plugins'])){
            foreach ($params['plugins'] as $plugin) {
                switch ($plugin) {
                    case 'bootstrap-4':
                        $scripts['datatable_bootstrap_4'] = $resouces_url."/js/dataTables.bootstrap4.min.js";
                        $styles['datatable_bootstrap_4'] = $resouces_url."/css/dataTables.bootstrap4.min.css";
                        break;
                }
            }
        }

        //add script and style needed for datatable
        $dashboard->resources['scripts'] = array_unique (array_merge ($dashboard->resources['scripts'], $scripts));
        $dashboard->resources['stylesheets'] = array_unique (array_merge ($dashboard->resources['stylesheets'], $styles));

        //add dependencies needed after datatable plugin

        if(!empty($params['dependencies']['styles_after'])){
            foreach ($params['dependencies']['styles_after'] as $dep_style) {
                $dashboard->registerResource('stylesheets', $dep_style);
            }
        }

        if(!empty($params['dependencies']['scripts_after'])){
            foreach ($params['dependencies']['scripts_after'] as $dep_script) {
                $dashboard->registerResource('scripts', $dep_script);
            }
        }

        $this->params = $params;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render(){

        switch ($this->params['source']) {
            case 'html':
                $this->params['scripts'] = '<script type="text/javascript">
                    $(function () {
                        $("#'.$this->params['table_id'].'").DataTable();
                    });
                </script>';
                break;
            case 'javascript':
                $this->params['scripts'] = '<script type="text/javascript">
                    $(function () {
                        $("#'.$this->params['table_id'].'").DataTable( {
                            data: '.$this->params['json']['data'].',
                            columns: '.$this->params['json']['column'].'
                        })
                    });
                </script>';
                break;
            case 'ajax-client':
                $this->params['scripts'] = '<script type="text/javascript">
                    $(function () {
                        $("#'.$this->params['table_id'].'").DataTable( {
                            "ajax": "'.$this->params['url'].'"
                        })
                    });
                </script>';
                break;
            case 'ajax-server':
                $this->params['scripts'] = '<script type="text/javascript">
                    $(function () {
                        $("#'.$this->params['table_id'].'").DataTable( {
                            "processing": true,
                            "serverSide": true,
                            "ajax": "'.$this->params['url'].'"
                        })
                    });
                </script>';
                break;
        }

        return <<<'blade'
            <div class="layout">
                {!! $params["html"] !!}
            </div>
            @push('stylesheets')
                {!! $params["styles"] !!}
            @endpush
            @push('scripts')
                {!! $params["scripts"] !!}
            @endpush
            blade;

    }
}
