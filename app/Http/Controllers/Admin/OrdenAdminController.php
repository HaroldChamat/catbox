<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Orden;
use App\Models\User;
use Illuminate\Http\Request;

class OrdenAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Orden::with(['user', 'detalles.producto'])->latest();
        
        // Filtro por usuario (puede venir de estadísticas)
        $usuarioFiltrado = null;
        if ($request->has('usuario')) {
            $usuarioId = $request->usuario;
            $query->where('user_id', $usuarioId);
            $usuarioFiltrado = User::find($usuarioId);
        }
        
        // Filtro por estado
        if ($request->has('estado') && $request->estado != '') {
            $query->where('estado', $request->estado);
        }
        
        // Filtro por fechas (desde estadísticas o manual)
        $fechaDesde = $request->get('desde', $request->get('fecha_desde'));
        $fechaHasta = $request->get('hasta', $request->get('fecha_hasta'));
        
        if ($fechaDesde) {
            $query->whereDate('created_at', '>=', $fechaDesde);
        }
        
        if ($fechaHasta) {
            $query->whereDate('created_at', '<=', $fechaHasta);
        }
        
        $ordenes = $query->paginate(15);
        
        return view('admin.ordenes.index', compact('ordenes', 'usuarioFiltrado', 'fechaDesde', 'fechaHasta'));
    }

    public function show($id)
    {
        $orden = Orden::with(['user', 'detalles.producto.imagenPrincipal', 'comentarios.user'])->findOrFail($id);
        return view('admin.ordenes.show', compact('orden'));
    }

    public function updateEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,procesando,enviado,entregado,cancelado'
        ]);

        $orden = Orden::findOrFail($id);
        $orden->estado = $request->estado;
        $orden->save();

        return redirect()->back()->with('success', 'Estado de la orden actualizado correctamente');
    }

    public function destroy($id)
    {
        $orden = Orden::findOrFail($id);
        
        // Restaurar stock de productos
        foreach ($orden->detalles as $detalle) {
            $detalle->producto->increment('stock', $detalle->cantidad);
        }
        
        $orden->delete();
        
        return redirect()->route('admin.ordenes.index')
            ->with('success', 'Orden eliminada correctamente y stock restaurado');
    }
}