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

        // Redirigir al dashboard de usuario (el que tenías antes)
        return redirect()->route('usuario.dashboard');
    }
}