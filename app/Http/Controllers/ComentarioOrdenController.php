<?php

namespace App\Http\Controllers;

use App\Models\ComentarioOrden;
use App\Models\Orden;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComentarioOrdenController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function guardar(Request $request, $ordenId)
    {
        $request->validate([
            'comentario' => 'required|string|max:1000',
        ]);

        $orden = Orden::findOrFail($ordenId);

        // Verificar permisos
        if (!Auth::user()->esAdmin() && $orden->user_id !== Auth::id()) {
            abort(403);
        }

        ComentarioOrden::create([
            'orden_id' => $ordenId,
            'user_id' => Auth::id(),
            'comentario' => $request->comentario,
            'es_admin' => Auth::user()->esAdmin(),
            'leido' => false,
        ]);

        return back()->with('success', 'Comentario enviado');
    }

    public function marcarComoLeidos($ordenId)
    {
        $orden = Orden::findOrFail($ordenId);

        if (!Auth::user()->esAdmin() && $orden->user_id !== Auth::id()) {
            abort(403);
        }

        $esAdmin = Auth::user()->esAdmin();
        
        ComentarioOrden::where('orden_id', $ordenId)
            ->where('es_admin', '!=', $esAdmin)
            ->where('leido', false)
            ->update(['leido' => true]);

        return response()->json(['success' => true]);
    }
}