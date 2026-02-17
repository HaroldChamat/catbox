<?php

namespace App\Http\Controllers;

use App\Models\Cupon;
use App\Models\CuponUso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CuponController extends Controller
{
    public function aplicar(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string',
        ]);

        $cupon = Cupon::with('productos')
            ->where('codigo', strtoupper($request->codigo))
            ->first();

        // Validaciones
        if (!$cupon) {
            return back()->withErrors(['cupon' => 'El código ingresado no existe.']);
        }

        if (!$cupon->esValido()) {
            return back()->withErrors(['cupon' => 'Este cupón no está disponible o ha expirado.']);
        }

        // Verificar si el usuario ya lo usó
        $yaUso = CuponUso::where('cupon_id', $cupon->id)
            ->where('user_id', Auth::id())
            ->exists();

        if ($yaUso) {
            return back()->withErrors(['cupon' => 'Ya utilizaste este cupón anteriormente.']);
        }

        // Guardar cupón en sesión
        Session::put('cupon', [
            'id'     => $cupon->id,
            'codigo' => $cupon->codigo,
            'tipo'   => $cupon->tipo,
            'valor'  => $cupon->valor,
            'alcance'=> $cupon->alcance,
        ]);

        return back()->with('success', "Cupón {$cupon->codigo} aplicado correctamente.");
    }

    public function quitar()
    {
        Session::forget('cupon');
        return back()->with('success', 'Cupón eliminado.');
    }
}