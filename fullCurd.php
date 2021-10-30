<?php

namespace App\Orchid\Screens\Data;

use Orchid\Screen\Screen;
use App\View\Components\Datatable;
use Orchid\Support\Facades\Layout;
use Yajra\Datatables\Datatables;
use App\Models\Customer;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Support\Facades\Toast;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;

class DataCustomerScreen extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Data Customer';

    /**
     * Query data.
     *
     * @return array
     */
    public function query(Datatables $dataTables): array
    {
        $model = Customer::query();
        $data =  $dataTables->eloquent($model)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $actionBtn = ModalToggle::make(__('Edit'))
                        ->icon('pencil')
                        ->modal('customerModal')
                        ->modalTitle('Edit Customer')
                        ->method('edit_action')
                        ->asyncParameters([
                            'id'=>$row->id,
                        ]);
                    $actionBtn .= Button::make(__('Delete'))
                                    ->icon('trash')
                                    ->method('delete_action')
                                    ->confirm(__('Are you sure you want to delete the user?'))
                                    ->parameters([
                                        'id' => $row->id,
                                    ]);
                    return $actionBtn;
                })
                ->rawColumns(['action']);

        if (request()->ajax()) {
            echo $data->make(true)->content();
            die();
        }else{
            $builder = $dataTables->getHtmlBuilder();

            $builder->ajax([
                'url' => route('platform.data.customer'),
                'type' => 'GET',
                'data' => 'function(d) { d.key = "value"; }', // you can add more key and value need to request in here
            ]);

            $builder->columns([
                ["title"=> "ID Data", "data"=> 'id', "name"=> 'id'],
                ["title"=> 'ID Site', "data"=> 'id_site', "name"=> 'id_site'],
                ["title"=> 'ID Customer', "data"=> 'id_customer', "name"=> 'id_customer'],
                ["title"=> 'Gender', "data"=> 'gender', "name"=> 'gender'],
                ["title"=> 'Firstname', "data"=> 'firstname', "name"=> 'firstname'],
                ["title"=> 'Lastname', "data"=> 'lastname', "name"=> 'lastname'],
                ["title"=> 'Email', "data"=> 'email', "name"=> 'email'],
                ["title"=> 'Birthday', "data"=> 'birthday', "name"=> 'birthday'],
                ["title"=> 'Newsletter', "data"=> 'newsletter', "name"=> 'newsletter'],
                ["title"=> 'Date Add', "data"=> 'date_add', "name"=> 'date_add'],
                ["title"=> 'Date Updated', "data"=> 'date_upd', "name"=> 'date_upd'],
                ["title"=> 'System created', "data"=> 'created_at', "name"=> 'created_at'],
                ["title"=> 'System Updated', "data"=> 'updated_at', "name"=> 'updated_at'],
                ["title"=> "Action", "data"=> 'action', "name"=> 'action', "orderable"=> false, "searchable"=> false]
            ]);

            $builder->parameters([
                "scrollX" => true,
                'dom'     => 'Bfrtip',
                'buttons' =>[
                    [
                        'extend'=> 'colvis',
                        'text'=> 'Column',
                    ],
                    // 'copy',
                    [
                        'extend'=> 'csv',
                        'exportOptions'=> [
                            'columns'=> ':visible'
                        ]
                    ],
                    // 'excel',
                    [
                        'extend'=> 'pdf',
                        'exportOptions'=> [
                            'columns'=> ':visible'
                        ]
                    ],
                    [
                        'extend'=> 'print',
                        'exportOptions'=> [
                            'columns'=> ':visible'
                        ]
                    ],
                    [
                        'text'=> 'Reload',
                        'action'=> 'function ( e, dt, node, config ) {
                            dt.ajax.reload();
                        }'
                    ]
                ]
            ]);

            $plugins = ['buttons'];

            $dependencies = [
                'scripts_before' => [], //array of url script will be load before datatable plugin
                'styles_before' => [], //array of url stylesheet will be load before datatable plugin
                'scripts_after' => [
                    '/DataTables/Buttons/js/buttons.colVis.min.js',
                    '/DataTables/JSZip/jszip.min.js',
                    '/DataTables/pdfmake/pdfmake.min.js',
                    '/DataTables/pdfmake/vfs_fonts.js',
                    '/DataTables/Buttons/js/buttons.html5.min.js',
                    '/DataTables/Buttons/js/buttons.print.min.js'
                ], //array of url script will be load after datatable plugin
                'styles_after' => [] //array of url stylesheet will be load after datatable plugin
            ];

            $html = $builder->table(['class' => 'table table-bordered'], true);

            $custom_script = '<script>

            </script>';

            $scripts = $builder->scripts().$custom_script;
            $styles = '';

            //if u want too add more html just add using $html .= 'your html';
            //if u want too add more scripts just add using $scripts .= '<script>your script</script>';
            return [
                'params' => [
                    'html' => $html,
                    'scripts'=> $scripts,
                    'styles' => $styles,
                    'plugins' => $plugins,
                    'dependencies' => $dependencies,
                    'styling'=> 'bootstrap4'
                ],
                // 'table'=> Customer::paginate(),
                // 'customerModal'=> Customer::find(1),
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
        return [
            ModalToggle::make('Add New')
                ->modal('customerModal')
                ->modalTitle('Add Customer')
                ->method('add_action')
                ->icon('note')
                ->asyncParameters([
                    'id'=>0,
                ])
        ];
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
            Layout::modal('customerModal', [
                Layout::rows([
                    Input::make('id_site')->title('ID Site')
                        ->type('number')
                        ->help('Where site id of this data?')
                        ->required()
                        ->canSee($this->hiddenNew()),
                    Input::make('id_customer')->title('ID Customer')
                        ->type('number')
                        ->help('What customer id of this data?')
                        ->required()
                        ->canSee($this->hiddenNew()),
                    Input::make('firstname')->title('First name')
                        ->help('What is customer first name?')
                        ->required(),
                    Input::make('lastname')->title('Last name')
                        ->help('What is customer last name?')
                        ->required(),
                    Input::make('email')->title('Email')
                        ->type('email')
                        ->help('What is customer email?')
                        ->required(),
                    Select::make('gender')
                        ->title('Gender')
                        ->fromModel(Customer::class, 'gender', 'gender')
                        ->empty('No select')
                        ->help('Select customer gender?'),
                    DateTimer::make('birthday')
                        ->title('Birthday')
                        ->format('Y-m-d'),
                    Select::make('newsletter')
                        ->options([
                            0   => 'No Subcribe',
                            1   => 'Subcribe',
                        ])
                        ->title('Newsletter')
                        ->help('Are customer subcribe Newsletter?'),
                    Input::make('date_add')->type('hidden'),
                    Input::make('date_upd')->type('hidden'),
                ]),
            ])->title('Add New')->async('asyncGetData'),
        ];
    }

    public function hiddenNew()
    {
        if(request()->input('id') !== 0){
            return false;
        }else{
            return true;
        }

    }

    /**
     * get data method
     * @return array
     */
    public function asyncGetData(Request $request): array
    {
        $id = $request->input('id');
        if($id !== 0){
            $old_data = Customer::find($id)->toArray();
            return $old_data;
        }else{
            $now = date("Y-m-d H:i:s");
            $default_data = [
                'id' => $id,
                'id_site' => '',
                'id_customer' => '',
                'gender' => '',
                'firstname' => '',
                'lastname' => '',
                'email' => '',
                'birthday' => '',
                'newsletter' => '',
                'date_add' => $now,
                'date_upd' => $now
            ];
            return $default_data;
        }
    }

    /**
     * add action method
     */
    public function add_action(Request $request): void
    {
        $input = $request->all();
        Customer::create($input);
        Toast::info('Add Success');
    }

    public function edit_action(Request $request)
    {
        $id = $request->input('id');
        $customer = Customer::find($id);
        $input = $request->all();
        $customer->update($input);
        // $customer->fill($input)->save();
        Toast::info('Edit Success');
    }

    public function delete_action(Request $request)
    {
        $id = $request->input('id');
        Customer::destroy($id);
        Toast::info('Delete Success');
    }
}
