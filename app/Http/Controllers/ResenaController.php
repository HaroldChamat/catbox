<?php

namespace App\Http\Controllers;

use App\Models\Resena;
use App\Models\Producto;
use App\Models\Orden;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResenaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function guardar(Request $request, $productoId)
    {
        $request->validate([
            'calificacion' => 'required|integer|min:1|max:5',
            'comentario'   => 'required|string|min:10|max:500',
        ], [
            'calificacion.required' => 'Debes seleccionar una calificación',
            'comentario.required'   => 'El comentario no puede estar vacío',
            'comentario.min'        => 'El comentario debe tener al menos 10 caracteres',
            'comentario.max'        => 'El comentario no puede exceder 500 caracteres',
        ]);

        $producto = Producto::findOrFail($productoId);

        // Verificar que el usuario haya comprado el producto
        $haComprado = Orden::where('user_id', Auth::id())
            ->whereIn('estado', ['entregado', 'completado'])
            ->whereHas('detalles', function ($q) use ($productoId) {
                $q->where('producto_id', $productoId);
            })
            ->exists();

        if (!$haComprado) {
            return back()->withErrors(['comentario' => 'Solo puedes reseñar productos que hayas comprado.']);
        }

        // Verificar que no haya reseñado antes
        $yaReseno = Resena::where('user_id', Auth::id())
            ->where('producto_id', $productoId)
            ->exists();

        if ($yaReseno) {
            return back()->withErrors(['comentario' => 'Ya enviaste una reseña para este producto.']);
        }

        Resena::create([
            'user_id'      => Auth::id(),
            'producto_id'  => $productoId,
            'calificacion' => $request->calificacion,
            'comentario'   => $request->comentario,
            'estado'       => 'pendiente',
        ]);

        return back()->with('success', 'Reseña enviada. Será visible una vez aprobada.');
    }

    public function editar(Request $request, $productoId)
    {
        $request->validate([
            'calificacion' => 'required|integer|min:1|max:5',
            'comentario'   => 'required|string|min:10|max:500',
        ], [
            'calificacion.required' => 'Debes seleccionar una calificación',
            'comentario.required'   => 'El comentario no puede estar vacío',
            'comentario.min'        => 'El comentario debe tener al menos 10 caracteres',
            'comentario.max'        => 'El comentario no puede exceder 500 caracteres',
        ]);

        $resena = Resena::where('user_id', Auth::id())
            ->where('producto_id', $productoId)
            ->firstOrFail();

        $resena->update([
            'calificacion' => $request->calificacion,
            'comentario'   => $request->comentario,
            'estado'       => 'pendiente', // Vuelve a moderación
        ]);

        return back()->with('success', 'Reseña actualizada. Será visible una vez aprobada nuevamente.');
    }
}