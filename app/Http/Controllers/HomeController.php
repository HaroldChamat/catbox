<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        
        // Si es admin, redirigir a dashboard admin
        if ($user->esAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        // Estadísticas del usuario
        $creditoDisponible = $user->saldoCreditosTotal();
        $ordenesTotales = $user->ordenes()->count();
        $ordenesRecientes = $user->ordenes()
            ->with(['detalles.producto.imagenPrincipal', 'pago'])
            ->latest()
            ->take(5)
            ->get();
        
        $favoritosCount = $user->favoritos()->count();
        $devolucionesPendientes = $user->devoluciones()->where('estado', 'pendiente')->count();

        return view('home', compact(
            'creditoDisponible',
            'ordenesTotales',
            'ordenesRecientes',
            'favoritosCount',
            'devolucionesPendientes'
        ));
    }
}