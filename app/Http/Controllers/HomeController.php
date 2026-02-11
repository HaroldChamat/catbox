<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();

        // Admin → redirige a su panel
        if ($user->esAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        // Usuario normal → su dashboard
        $ordenesRecientes = $user->ordenes()
            ->with(['detalles.producto'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $totalGastado = $user->ordenes()
            ->whereIn('estado', ['procesando', 'enviado', 'entregado'])
            ->sum('total');

        $totalOrdenes = $user->ordenes()->count();

        return view('usuario.dashboard', compact('ordenesRecientes', 'totalGastado', 'totalOrdenes'));
    }
}