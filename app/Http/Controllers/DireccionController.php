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
        ], [
            'direccion.required' => 'La dirección es obligatoria',
            'direccion.max' => 'La dirección no puede tener más de 500 caracteres',
            'ciudad.required' => 'La ciudad es obligatoria',
            'ciudad.max' => 'La ciudad no puede tener más de 100 caracteres',
            'codigo_postal.required' => 'El código postal es obligatorio',
            'codigo_postal.max' => 'El código postal no puede tener más de 20 caracteres',
            'telefono.required' => 'El teléfono es obligatorio',
            'telefono.max' => 'El teléfono no puede tener más de 20 caracteres',
        ]);

        // Si es la primera dirección, hacerla principal automáticamente
        $esPrincipal = Auth::user()->direcciones()->count() === 0;

        // Si el usuario marca explícitamente como principal, quitar el flag de las demás
        if ($request->has('es_principal') && $request->es_principal) {
            Auth::user()->direcciones()->update(['es_principal' => false]);
            $esPrincipal = true;
        }

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

        // Verificar si la dirección está siendo usada en alguna orden activa
        $ordenesActivas = \App\Models\Orden::where('direccion_id', $id)
            ->whereIn('estado', ['pendiente', 'procesando', 'enviado'])
            ->count();

        if ($ordenesActivas > 0) {
            return back()->with('error', 'No puedes eliminar esta dirección porque está siendo usada en órdenes activas');
        }

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

    /**
     * Actualizar una dirección existente
     */
    public function actualizar(Request $request, $id)
    {
        $direccion = DireccionEntrega::where('user_id', Auth::id())
            ->findOrFail($id);

        $request->validate([
            'direccion' => 'required|string|max:500',
            'ciudad' => 'required|string|max:100',
            'codigo_postal' => 'required|string|max:20',
            'telefono' => 'required|string|max:20',
        ], [
            'direccion.required' => 'La dirección es obligatoria',
            'ciudad.required' => 'La ciudad es obligatoria',
            'codigo_postal.required' => 'El código postal es obligatorio',
            'telefono.required' => 'El teléfono es obligatorio',
        ]);

        $direccion->update([
            'direccion' => $request->direccion,
            'ciudad' => $request->ciudad,
            'codigo_postal' => $request->codigo_postal,
            'telefono' => $request->telefono,
        ]);

        return back()->with('success', 'Dirección actualizada correctamente');
    }
}