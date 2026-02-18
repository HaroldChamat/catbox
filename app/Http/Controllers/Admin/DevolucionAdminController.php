<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Devolucion;
use App\Models\Credito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DevolucionAdminController extends Controller
{
    public function index()
    {
        $devoluciones = Devolucion::with(['user', 'orden', 'items.detalleOrden.producto'])
            ->latest()
            ->paginate(15);

        return view('admin.devoluciones.index', compact('devoluciones'));
    }

    public function show($id)
    {
        $devolucion = Devolucion::with(['user', 'orden.detalles.producto', 'items.detalleOrden.producto'])
            ->findOrFail($id);

        return view('admin.devoluciones.show', compact('devolucion'));
    }

    public function aprobar(Request $request, $id)
    {
        $request->validate([
            'respuesta_admin' => 'nullable|string|max:500',
        ]);

        $devolucion = Devolucion::with('items.detalleOrden.producto')->findOrFail($id);

        if ($devolucion->estado !== 'pendiente') {
            return back()->with('error', 'Esta devolución ya fue procesada.');
        }

        try {
            DB::beginTransaction();

            // Devolver stock
            foreach ($devolucion->items as $item) {
                $item->detalleOrden->producto->increment('stock', $item->cantidad);
            }

            // Crear crédito para el usuario
            Credito::create([
                'user_id' => $devolucion->user_id,
                'devolucion_id' => $devolucion->id,
                'monto' => $devolucion->monto_total,
                'saldo' => $devolucion->monto_total,
                'usado' => false,
            ]);

            // Actualizar devolución
            $devolucion->update([
                'estado' => 'aprobada',
                'respuesta_admin' => $request->respuesta_admin,
                'fecha_aprobacion' => now(),
            ]);

            // Cambiar estado de la orden a "devuelto"
            $devolucion->orden->update(['estado' => 'devuelto']);

            // Enviar notificación al usuario
            $devolucion->user->notify(new \App\Notifications\DevolucionResueltaNotification($devolucion));

            DB::commit();

            return back()->with('success', 'Devolución aprobada. Se generó un crédito y se notificó al usuario.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al aprobar devolución: ' . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al aprobar la devolución.');
        }
    }

    public function rechazar(Request $request, $id)
    {
        $request->validate([
            'respuesta_admin' => 'required|string|min:10|max:500',
        ], [
            'respuesta_admin.required' => 'Debes explicar el motivo del rechazo',
            'respuesta_admin.min' => 'La respuesta debe tener al menos 10 caracteres',
        ]);

        $devolucion = Devolucion::findOrFail($id);

        if ($devolucion->estado !== 'pendiente') {
            return back()->with('error', 'Esta devolución ya fue procesada.');
        }

        $devolucion->update([
            'estado' => 'rechazada',
            'respuesta_admin' => $request->respuesta_admin,
            'fecha_rechazo' => now(),
        ]);

        // Enviar notificación al usuario
        $devolucion->user->notify(new \App\Notifications\DevolucionResueltaNotification($devolucion));

        return back()->with('success', 'Devolución rechazada. Se notificó al usuario.');
    }
}