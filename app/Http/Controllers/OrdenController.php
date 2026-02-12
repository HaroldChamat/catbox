<?php

namespace App\Http\Controllers;

use App\Models\Orden;
use App\Models\DetalleOrden;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrdenController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar historial de órdenes
     */
    public function index()
    {
        $ordenes = Auth::user()->ordenes()
            ->with(['detalles.producto', 'pago'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('ordenes.index', compact('ordenes'));
    }

    /**
     * Mostrar detalle de una orden
     */
    public function show($id)
    {
        $orden = Orden::with(['detalles.producto.imagenPrincipal', 'pago', 'user'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);
        
        // Obtener direcciones del usuario para poder agregar/cambiar
        $direcciones = Auth::user()->direcciones;
        
        return view('ordenes.show', compact('orden', 'direcciones'));
    }
    
    /**
     * Actualizar información de la orden (dirección, notas)
     */
    public function actualizarInfo(Request $request, $id)
    {
        $orden = Orden::where('user_id', Auth::id())->findOrFail($id);
        
        // Solo permitir actualizar si la orden está pendiente o procesando
        if (!in_array($orden->estado, ['pendiente', 'procesando'])) {
            return back()->with('error', 'No puedes editar una orden que ya fue enviada o entregada');
        }
        
        $request->validate([
            'direccion_id' => 'nullable|exists:direcciones_entrega,id',
            'notas' => 'nullable|string|max:500',
        ]);
        
        $orden->update([
            'direccion_id' => $request->direccion_id,
            'notas' => $request->notas,
        ]);
        
        return back()->with('success', 'Información de la orden actualizada correctamente');
    }
    
    /**
     * Completar pago de una orden pendiente
     */
    public function completarPago(Request $request, $id)
    {
        $orden = Orden::where('user_id', Auth::id())->findOrFail($id);
        
        // Solo permitir si la orden está pendiente
        if ($orden->estado !== 'pendiente') {
            return back()->with('error', 'Esta orden ya no puede ser modificada');
        }
        
        $request->validate([
            'metodo_pago' => 'required|in:tarjeta,paypal',
            'numero_tarjeta' => 'required_if:metodo_pago,tarjeta|nullable|string',
            'nombre_titular' => 'required_if:metodo_pago,tarjeta|nullable|string',
            'fecha_expiracion' => 'required_if:metodo_pago,tarjeta|nullable|string',
            'cvv' => 'required_if:metodo_pago,tarjeta|nullable|string',
            'paypal_email' => 'required_if:metodo_pago,paypal|nullable|email',
        ]);
        
        // Actualizar o crear pago
        if ($orden->pago) {
            $orden->pago->update([
                'metodo_pago' => $request->metodo_pago,
                'estado' => 'completado',
                'datos_pago' => [
                    'metodo' => $request->metodo_pago,
                    'ultimos_digitos' => $request->metodo_pago === 'tarjeta' ? substr($request->numero_tarjeta, -4) : null,
                    'paypal_email' => $request->paypal_email,
                ],
            ]);
        } else {
            Pago::create([
                'orden_id' => $orden->id,
                'metodo_pago' => $request->metodo_pago,
                'monto' => $orden->total,
                'estado' => 'completado',
                'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                'datos_pago' => [
                    'metodo' => $request->metodo_pago,
                    'ultimos_digitos' => $request->metodo_pago === 'tarjeta' ? substr($request->numero_tarjeta, -4) : null,
                    'paypal_email' => $request->paypal_email,
                ],
            ]);
        }
        
        // Cambiar estado de la orden a procesando
        $orden->update(['estado' => 'procesando']);
        
        return back()->with('success', '¡Pago completado! Tu orden está siendo procesada');
    }
    
    /**
     * Cancelar una orden
     */
    public function cancelar($id)
    {
        $orden = Orden::where('user_id', Auth::id())->findOrFail($id);
        
        // Solo permitir cancelar si está pendiente o procesando
        if (!in_array($orden->estado, ['pendiente', 'procesando'])) {
            return back()->with('error', 'No puedes cancelar una orden que ya fue enviada');
        }
        
        // Devolver stock a los productos
        foreach ($orden->detalles as $detalle) {
            $detalle->producto->increment('stock', $detalle->cantidad);
        }
        
        // Actualizar estado
        $orden->update(['estado' => 'cancelado']);
        
        if ($orden->pago) {
            $orden->pago->update(['estado' => 'reembolsado']);
        }
        
        return redirect()->route('ordenes.index')->with('success', 'Orden cancelada correctamente');
    }

    /**
     * Mostrar formulario de checkout
     */
    public function checkout()
    {
        $carrito = Auth::user()->carrito;
        
        if (!$carrito || $carrito->items->count() === 0) {
            return redirect()->route('carrito.index')
                ->with('error', 'Tu carrito está vacío');
        }

        $items = $carrito->items()->with(['producto.imagenPrincipal'])->get();
        $total = $carrito->calcularTotal();
        $direcciones = Auth::user()->direcciones;
        
        return view('ordenes.checkout', compact('items', 'total', 'direcciones'));
    }

    /**
     * Procesar el checkout
     */
    public function procesar(Request $request)
    {
        $request->validate([
            'metodo_pago' => 'required|in:tarjeta,paypal',
            'direccion_id' => 'nullable|exists:direcciones_entrega,id',
        ]);

        $carrito = Auth::user()->carrito;
        
        if (!$carrito || $carrito->items->count() === 0) {
            return redirect()->route('carrito.index')
                ->with('error', 'Tu carrito está vacío');
        }

        try {
            DB::beginTransaction();

            // Crear la orden
            $orden = Orden::create([
                'user_id' => Auth::id(),
                'total' => $carrito->calcularTotal(),
                'estado' => 'pendiente',
                'fecha_entrega_estimada' => now()->addDays(5)
            ]);

            // Crear detalles de la orden
            foreach ($carrito->items as $item) {
                DetalleOrden::create([
                    'orden_id' => $orden->id,
                    'producto_id' => $item->producto_id,
                    'cantidad' => $item->cantidad,
                    'precio_unitario' => $item->precio_unitario,
                    'subtotal' => $item->cantidad * $item->precio_unitario
                ]);

                // Reducir stock
                $item->producto->decrement('stock', $item->cantidad);
            }

            // Crear registro de pago
            $pago = Pago::create([
                'orden_id' => $orden->id,
                'metodo_pago' => $request->metodo_pago,
                'monto' => $orden->total,
                'estado' => 'pendiente',
                'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                'datos_pago' => [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]
            ]);

            // Vaciar el carrito
            $carrito->items()->delete();

            DB::commit();

            return redirect()->route('ordenes.confirmacion', $orden->id)
                ->with('success', 'Orden creada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al procesar la orden: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar confirmación de orden
     */
    public function confirmacion($id)
    {
        $orden = Orden::with(['detalles.producto.imagenPrincipal', 'pago'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);
        
        return view('ordenes.confirmacion', compact('orden'));
    }
}