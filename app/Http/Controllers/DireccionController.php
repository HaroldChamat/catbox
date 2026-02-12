<?php

namespace App\Http\Controllers;

use App\Models\DireccionEntrega;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DireccionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Guardar nueva dirección
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'direccion' => 'required|string|max:500',
            'ciudad' => 'required|string|max:100',
            'codigo_postal' => 'required|string|max:20',
            'telefono' => 'required|string|max:20',
        ]);

        // Si es la primera dirección, hacerla principal automáticamente
        $esPrincipal = Auth::user()->direcciones()->count() === 0;

        $direccion = DireccionEntrega::create([
            'user_id' => Auth::id(),
            'direccion' => $request->direccion,
            'ciudad' => $request->ciudad,
            'codigo_postal' => $request->codigo_postal,
            'telefono' => $request->telefono,
            'es_principal' => $esPrincipal,
        ]);

        return back()->with('success', 'Dirección agregada correctamente');
    }

    /**
     * Establecer como dirección principal
     */
    public function establecerPrincipal($id)
    {
        $direccion = DireccionEntrega::where('user_id', Auth::id())
            ->findOrFail($id);

        // Quitar el flag de principal a todas
        Auth::user()->direcciones()->update(['es_principal' => false]);

        // Establecer esta como principal
        $direccion->update(['es_principal' => true]);

        return back()->with('success', 'Dirección principal actualizada');
    }

    /**
     * Eliminar dirección
     */
    public function eliminar($id)
    {
        $direccion = DireccionEntrega::where('user_id', Auth::id())
            ->findOrFail($id);

        $eraPrincipal = $direccion->es_principal;
        $direccion->delete();

        // Si era la principal, asignar otra
        if ($eraPrincipal) {
            $otra = Auth::user()->direcciones()->first();
            if ($otra) {
                $otra->update(['es_principal' => true]);
            }
        }

        return back()->with('success', 'Dirección eliminada');
    }
}