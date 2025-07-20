<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class BasicCRUDController extends Controller
{
    protected $model;
    protected $basePermission;
    protected $baseViewPath;
    protected $baseRoute;
    protected $baseRouteParams = [];
    protected $resourceName;
    protected $createType = 'modal';
    protected $createScript = null;
    protected $editType = 'modal';
    protected $tableColumns = [];
    protected $createFields = [];
    protected $editFields = [];
    protected $tableActions = [];
    protected $availableFilters = [];
    protected $callbackQuery = null;
    protected $cardFooter = null;
    protected $defaultOrder = null;
    protected $indexTitle = null;
    protected $datatableCallback = null;
    protected $tableLegend = [];
    protected $extraScripts = [];

    public function __construct()
    {
        if ($this->basePermission) {
            $this->middleware('can:' . $this->basePermission . '_list', ['only' => ['index']]);
            $this->middleware('can:' . $this->basePermission . '_view', ['only' => ['show']]);
            $this->middleware('can:' . $this->basePermission . '_create', ['only' => ['store', 'create']]);
            $this->middleware('can:' . $this->basePermission . '_edit', ['only' => ['update', 'edit']]);
            $this->middleware('can:' . $this->basePermission . '_delete', ['only' => ['destroy']]);
        }

        if (!$this->baseViewPath) {
            $this->baseViewPath = 'master.basic-crud';
        }

        if (!$this->baseRoute) {
            $this->baseRoute = str_replace('_', '-', (new $this->model())->getTable()) . '.';
        }

        if (!$this->resourceName) {
            $this->resourceName = ucwords(\Illuminate\Support\Str::singular(str_replace('_', ' ', (new $this->model())->getTable())));
        }
    }

    public function index()
    {
        if (request()->ajax()) {
            $tableName = (new $this->model())->getTable();
            $query = $this->model::select($tableName . '.*')->with(request()->append ?: [])->withCount(request()->withCount ?: []);
            if (is_callable($this->callbackQuery)) {
                call_user_func($this->callbackQuery, $query);
            }

            if (!empty($this->userProjectIdsx)) {
                $query->orderByRaw("FIELD(project_id, " . implode(',', $this->userProjectIdsx) . ") DESC")
                      ->orderBy('id', 'ASC'); // Fallback jika tidak ada aturan default
            }

            if ($this->availableFilters && $filters = request()->filters) {
                foreach ($filters as $key => $value) {
                    if ($value && $filter = $this->availableFilters[$key] ?? null) {
                        if (is_callable($filter['handler'] ?? null)) {
                            $filter['handler']($query, $key, $value);
                        } else {
                            $query->where($key, $value);
                        }
                    }
                }
            }

            $datatable = datatables()->of($query);

            if (is_callable($this->datatableCallback)) {
                call_user_func($this->datatableCallback, $datatable);
            }
            
            //dd($query->toSql(), $query->getBindings());
            return $datatable->make(true);
        }

        return view($this->baseViewPath . '.index', [
            'basePermission' => $this->basePermission,
            'baseRoute' => $this->baseRoute,
            'baseRouteParams' => $this->baseRouteParams,
            'resourceName' => $this->resourceName,
            'createType' => $this->createType,
            'editType' => $this->editType,
            'tableColumns' => $this->tableColumns,
            'createFields' => $this->createFields,
            'createScript' => $this->createScript,
            'editFields' => $this->editFields,
            'tableActions' => $this->tableActions,
            'availableFilters' => $this->availableFilters,
            'cardFooter' => $this->cardFooter,
            'defaultOrder' => $this->defaultOrder ?? [[1, 'asc']],
            'indexTitle' => $this->indexTitle,
            'tableLegend' => $this->tableLegend,
            'extraScripts' => $this->extraScripts,
        ]);
    }

    public function show($resource)
    {
        $resource = $this->model::findOrfail($resource);

        return view($this->baseViewPath . '.show', compact('resource'));
    }

    public function create()
    {
        return view($this->baseViewPath . '.create');
    }

    public function store(Request $request)
    {
        $toCreate = $request->except('_token');
        $result = $this->model::create($toCreate);

        return $result;
    }

    public function edit($resource)
    {
        $resource = $this->model::findOrfail($resource);

        return view($this->baseViewPath . '.edit', compact('resource'));
    }

    public function update(Request $request, $resource)
    {
        $toUpdate = $request->except('_token', '_method');
        $data = $this->model::findOrfail($resource);
        $data->update($toUpdate);

        return $data;
    }

    public function destroy($resource)
    {
        $data = $this->model::findOrfail($resource);
        $data->delete();
        return response()->json(['message' => 'Data berhasil dihapus']);
    }
}
