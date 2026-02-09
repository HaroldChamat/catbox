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
        $orden = Orden::with(['detalles.producto.imagenPrincipal', 'pago'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);
        
        return view('ordenes.show', compact('orden'));
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