<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Orden;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdenAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Orden::with(['user', 'detalles.producto']);

        // Filtro por usuario
        if ($request->filled('usuario_id')) {
            $query->where('user_id', $request->usuario_id);
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Filtro por fecha
        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        $ordenes = $query->orderBy('created_at', 'desc')->paginate(20);

        // Usuarios para el filtro
        $usuarios = User::where('is_admin', false)
            ->orderBy('name')
            ->get();

        // Usuarios más recurrentes
        $usuariosRecurrentes = User::where('is_admin', false)
            ->withCount('ordenes')
            ->having('ordenes_count', '>', 0)
            ->orderByDesc('ordenes_count')
            ->limit(10)
            ->get();

        // Estadísticas generales
        $stats = [
            'total_ordenes' => Orden::count(),
            'ordenes_pendientes' => Orden::where('estado', 'pendiente')->count(),
            'ordenes_completadas' => Orden::where('estado', 'entregado')->count(),
            'total_ventas' => Orden::whereIn('estado', ['procesando', 'enviado', 'entregado'])->sum('total'),
        ];

        return view('admin.ordenes.index', compact('ordenes', 'usuarios', 'usuariosRecurrentes', 'stats'));
    }

    public function show($id)
    {
        $orden = Orden::with(['user', 'detalles.producto.imagenPrincipal', 'pago'])
            ->findOrFail($id);

        return view('admin.ordenes.show', compact('orden'));
    }

    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,procesando,enviado,entregado,cancelado'
        ]);

        $orden = Orden::findOrFail($id);
        $orden->update(['estado' => $request->estado]);

        return back()->with('success', 'Estado de la orden actualizado');
    }
}