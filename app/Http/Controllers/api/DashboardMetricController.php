<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DashboardMetric;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DashboardMetricController extends Controller
{
    protected PermissionService $permissionService;
    public $routeName;
    public $sec;
    public function __construct(PermissionService $permissionService)
    {
        $this->routeName = request()->route()->getName();
        $this->permissionService = $permissionService;
        $this->sec = request()->segment(3);
    }
    public function index(Request $request)
    {
        // return DashboardMetric::all();
        $query = DashboardMetric::query();

        if ($request->filled('year')) {
            $query->whereYear('period', $request->year);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('investment', 'like', "%$search%")
                ->orWhere('visitors', 'like', "%$search%")
                ->orWhere('proposals', 'like', "%$search%")
                ->orWhere('closed_deals', 'like', "%$search%");
            });
        }

        return response()->json($query->paginate(10));
    }
    // public function filter(Request $request)
    // {
    //     $query = DashboardMetric::query();

    //     // filtro por ano
    //     if ($request->filled('ano')) {
    //         $query->whereYear('period', $request->ano);
    //     }

    //     // filtro por mês
    //     if ($request->filled('mes')) {
    //         $query->whereMonth('period', $request->mes);
    //     }

    //     // filtro por semana ISO (começando na segunda-feira)
    //     if ($request->filled('semana')) {
    //         $query->whereRaw('WEEK(period, 1) = ?', [$request->semana]);
    //     }

    //     // filtro por intervalo de datas
    //     if ($request->filled('data_inicio') && $request->filled('data_fim')) {
    //         $query->whereBetween('period', [$request->data_inicio, $request->data_fim]);
    //     }

    //     $metrics = $query->orderBy('period', 'asc')->get();

    //     return response()->json($metrics);
    // }
    public function filter(Request $request)
    {
        $query = DashboardMetric::query();

        // filtros opcionais
        if ($request->filled('ano')) {
            $query->whereYear('period', $request->ano);
        }
        if ($request->filled('mes')) {
            $query->whereMonth('period', $request->mes);
        }
        if ($request->filled('semana')) {
            $query->whereRaw('WEEK(period, 1) = ?', [$request->semana]); // semana ISO
        }
        if ($request->filled('data_inicio') && $request->filled('data_fim')) {
            $query->whereBetween('period', [$request->data_inicio, $request->data_fim]);
        }

        // registros detalhados filtrados
        $registros = $query->get();

        // ano alvo (default = ano atual)
        $ano = $request->ano ?? now()->year;

        // agrupamento por mês
        $porMes = DashboardMetric::selectRaw('MONTH(period) as mes, SUM(visitors) as total_visitors')
            ->whereYear('period', $ano)
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        // agrupamento por semana
        $porSemana = DashboardMetric::selectRaw('WEEK(period, 1) as semana, SUM(visitors) as total_visitors')
            ->whereYear('period', $ano)
            ->groupBy('semana')
            ->orderBy('semana')
            ->get();

        // agrupamento por ano
        $porAno = DashboardMetric::selectRaw('YEAR(period) as ano, SUM(visitors) as total_visitors')
            ->groupBy('ano')
            ->orderBy('ano')
            ->get();

        // 🔹 Totais agregados com base nos mesmos filtros aplicados
        $agregado = DashboardMetric::query();

        if ($request->filled('ano')) {
            $agregado->whereYear('period', $request->ano);
        }
        if ($request->filled('mes')) {
            $agregado->whereMonth('period', $request->mes);
        }
        if ($request->filled('semana')) {
            $agregado->whereRaw('WEEK(period, 1) = ?', [$request->semana]);
        }
        if ($request->filled('data_inicio') && $request->filled('data_fim')) {
            $agregado->whereBetween('period', [$request->data_inicio, $request->data_fim]);
        }

       $totaisFiltrados = $agregado->selectRaw("
            SUM(bot_conversations) as total_bot_conversations,
            SUM(human_conversations) as total_human_conversations,
            SUM(closed_deals) as total_closed_deals,
            SUM(investment) as total_investment,
            SUM(proposals) as total_proposals,
            SUM(visitors) as total_visitors
        ")->first();


        return response()->json([
            'registros' => $registros,
            'agregados' => [
                'por_mes' => $porMes,
                'por_semana' => $porSemana,
                'por_ano' => $porAno,
            ],
            'totais_filtrados' => $totaisFiltrados, // 👈 sempre retorna baseado no filtro aplicado
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if(!$user){
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        if (! $this->permissionService->can($user, 'settings.'.$this->sec.'.view', 'create')) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $validator = Validator::make($request->all(), [
            'period' => 'required|string|max:50',
            'investment' => 'required|numeric',
            'visitors' => 'required|integer',
            'bot_conversations' => 'required|integer',
            'human_conversations' => 'required|integer',
            'proposals' => 'required|integer',
            'closed_deals' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'exec'    => false,
                'message' => 'Erro de validação',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        // $data = $request->validate();

        $metric = DashboardMetric::create([
            ...$data,
            'user_id' => Auth::id(), // se vinculado ao usuário logado
        ]);

        return response()->json($metric, 201);
    }

    public function show(DashboardMetric $dashboardMetric)
    {
        return $dashboardMetric;
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if(!$user){
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        if (! $this->permissionService->can($user, 'settings.'.$this->sec.'.view', 'edit')) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $metric = DashboardMetric::find($id);
        // dd($metric->count());

        if (!$metric->count()) {
            return response()->json(['message' => 'Cadastro não encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'period' => 'sometimes|string|max:50',
            'investment' => 'sometimes|numeric',
            'visitors' => 'sometimes|integer',
            'bot_conversations' => 'sometimes|integer',
            'human_conversations' => 'sometimes|integer',
            'proposals' => 'sometimes|integer',
            'closed_deals' => 'sometimes|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors'  => $validator->errors(),
            ], 422);
        }
        $data = $validator->validated();
        $dashboardMetric = DashboardMetric::where('id',$id)->update($data);

        return response()->json($dashboardMetric,201);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        if(!$user){
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        if (! $this->permissionService->can($user, 'settings.'.$this->sec.'.view', 'edit')) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        $metric = DashboardMetric::find($id);

        if (!$metric) {
            return response()->json(['message' => 'Cadastro não encontrada'], 404);
        }
        $dashboardMetric = DashboardMetric::where('id',$id)->delete();
        // dd($dashboardMetric);
        // $dashboardMetric->delete();
        return response()->json($dashboardMetric, 204);
    }
}
