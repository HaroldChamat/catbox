<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class UsuarioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dashboard()
    {
        $user = Auth::user();
        
        $totalOrdenes = $user->ordenes()->count();
        $totalGastado = $user->ordenes()->sum('total');
        $ordenesRecientes = $user->ordenes()
            ->with(['detalles.producto.imagenPrincipal', 'pago'])
            ->latest()
            ->take(5)
            ->get();

        return view('usuario.dashboard', compact('totalOrdenes', 'totalGastado', 'ordenesRecientes'));
    }
}