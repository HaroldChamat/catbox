<?php

namespace App\Http\Controllers;

use App\Models\Favorito;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoritoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $favoritos = Favorito::with('producto.imagenes', 'producto.categoria')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('favoritos.index', compact('favoritos'));
    }

    public function toggle($productoId)
    {
        $producto = Producto::findOrFail($productoId);

        $existente = Favorito::where('user_id', Auth::id())
            ->where('producto_id', $productoId)
            ->first();

        if ($existente) {
            $existente->delete();
            $esFavorito = false;
            $mensaje = 'Eliminado de favoritos';
        } else {
            Favorito::create([
                'user_id' => Auth::id(),
                'producto_id' => $productoId,
            ]);
            $esFavorito = true;
            $mensaje = 'Agregado a favoritos';
        }

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'esFavorito' => $esFavorito,
                'mensaje' => $mensaje,
            ]);
        }

        return back()->with('success', $mensaje);
    }
}