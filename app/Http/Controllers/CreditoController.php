<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CreditoController extends Controller
{
    public function aplicar()
    {
        $saldo = Auth::user()->saldoCreditosTotal();

        if ($saldo <= 0) {
            return back()->withErrors(['credito' => 'No tienes crédito disponible.']);
        }

        Session::put('usar_credito', true);

        return back()->with('success', 'Crédito aplicado a tu compra');
    }

    public function quitar()
    {
        Session::forget('usar_credito');
        return back()->with('success', 'Crédito removido');
    }

    public function misSaldos()
    {
        $creditos = Auth::user()->creditos()
            ->with('devolucion.orden')
            ->latest()
            ->get();

        $saldoTotal = Auth::user()->saldoCreditosTotal();

        return view('creditos.index', compact('creditos', 'saldoTotal'));
    }
}