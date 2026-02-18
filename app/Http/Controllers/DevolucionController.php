<?php

namespace App\Http\Controllers;

use App\Models\Devolucion;
use App\Models\DevolucionItem;
use App\Models\Orden;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DevolucionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar formulario para solicitar devolución
     */
    public function crear($ordenId)
    {
        $orden = Orden::with(['detalles.producto.imagenPrincipal'])
            ->where('user_id', Auth::id())
            ->findOrFail($ordenId);

        // Verificar que la orden pueda ser devuelta
        if (!$orden->puedeSerDevuelta()) {
            return back()->with('error', 'Esta orden no puede ser devuelta. Debe estar entregada y dentro de los 30 días.');
        }

        if ($orden->tieneSolicitudDevolucionPendiente()) {
            return back()->with('error', 'Ya tienes una solicitud de devolución pendiente para esta orden.');
        }

        return view('devoluciones.crear', compact('orden'));
    }

    /**
     * Guardar solicitud de devolución
     */
    public function guardar(Request $request, $ordenId)
    {
         // DEBUG: Log de lo que llega
        \Log::info('=== DATOS RECIBIDOS EN DEVOLUCIÓN ===');
        \Log::info('Request completo:', $request->all());
        \Log::info('Items:', $request->input('items', []));
        
        $request->validate([
            'motivo' => 'required|string|min:20|max:500',
            'items' => 'required|array|min:1',
            'items.*.detalle_id' => 'required|exists:detalles_orden,id',
            'items.*.cantidad' => 'required|integer|min:1',
        ], [
            'motivo.required' => 'Debes explicar el motivo de la devolución',
            'motivo.min' => 'El motivo debe tener al menos 20 caracteres',
            'items.required' => 'Debes seleccionar al menos un producto',
        ]);

        $orden = Orden::where('user_id', Auth::id())->findOrFail($ordenId);

        if (!$orden->puedeSerDevuelta()) {
            return back()->with('error', 'Esta orden no puede ser devuelta.');
        }

        try {
            DB::beginTransaction();

            // Crear devolución
            $devolucion = Devolucion::create([
                'orden_id' => $orden->id,
                'user_id' => Auth::id(),
                'motivo' => $request->motivo,
                'estado' => 'pendiente',
                'monto_total' => 0, // Se calcula después
            ]);

            $montoTotal = 0;

            // Crear items de la devolución
            foreach ($request->items as $item) {
                $detalle = $orden->detalles()->findOrFail($item['detalle_id']);
                $cantidadDevolver = min($item['cantidad'], $detalle->cantidad);
                $montoItem = $detalle->precio_unitario * $cantidadDevolver;

                DevolucionItem::create([
                    'devolucion_id' => $devolucion->id,
                    'detalle_orden_id' => $detalle->id,
                    'cantidad' => $cantidadDevolver,
                    'monto' => $montoItem,
                ]);

                $montoTotal += $montoItem;
            }

            // Actualizar monto total
            $devolucion->update(['monto_total' => $montoTotal]);

            DB::commit();

            return redirect()->route('ordenes.show', $orden->id)
                ->with('success', 'Solicitud de devolución enviada. Será revisada por el administrador.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear devolución: ' . $e->getMessage());
            return back()->with('error', 'Ocurrió un error. Por favor, intenta de nuevo.');
        }
    }

    /**
     * Ver mis devoluciones
     */
    public function misDevoluciones()
    {
        $devoluciones = Devolucion::with(['orden', 'items.detalleOrden.producto'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('devoluciones.index', compact('devoluciones'));
    }
}