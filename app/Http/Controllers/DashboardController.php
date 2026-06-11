<?php

namespace App\Http\Controllers;

use App\Models\Poliza;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $query = Poliza::query();

        if (Auth::user()->hasRole('Gestor Tesorería Ambiente')) {
            $query->where('categoria_poliza', 'ambiental')
                  ->where('subtipo_poliza', 'fiel_cumplimiento_ambiental');
        }

        $totalPolizas = (clone $query)->count();
        $activas = (clone $query)->where('estado', 'vigente')->count();
        $vencidas = (clone $query)->where('estado', 'liquidada')->count();

        $proximasVencer = (clone $query)->where('estado', 'vigente')
            ->whereBetween('fecha_vencimiento', [now(), now()->addDays(8)])
            ->with(['sucursal.aseguradora'])
            ->orderBy('fecha_vencimiento')
            ->get();

        // Obtener pólizas vencidas
        $polizasVencidas = (clone $query)->where('estado', 'vencida')
            ->with(['sucursal.aseguradora'])
            ->orderBy('fecha_vencimiento', 'desc')
            ->limit(10)
            ->get();

        $valorTotal = (clone $query)->where('estado', 'vigente')->sum('valor_asegurado');

        return Inertia::render('Dashboard', [
            'stats' => [
                'total' => $totalPolizas,
                'activas' => $activas,
                'vencidas' => $vencidas,
                'proximas_vencer_count' => $proximasVencer->count(),
                'valor_total' => $valorTotal,
            ],
            'proximas_vencer' => $proximasVencer,
            'polizas_vencidas' => $polizasVencidas,
        ]);
    }
}
