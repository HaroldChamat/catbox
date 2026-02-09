<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = auth()->user();
        
        // Si es admin, redirigir al panel admin
        if ($user->esAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        
        // Usuario normal - mostrar dashboard
        $ordenesRecientes = $user->ordenes()
            ->with(['detalles.producto', 'pago'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('home', compact('ordenesRecientes'));
    }
}