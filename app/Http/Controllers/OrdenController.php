<?php

namespace App\Http\Controllers;

use App\Models\Orden;
use App\Models\DetalleOrden;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Cupon;
use App\Models\CuponUso;

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
        $orden = Orden::with(['detalles.producto.imagenPrincipal', 'pago', 'user', 'direccion', 'comentarios.user'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);
        
        // Obtener direcciones del usuario
        $direcciones = Auth::user()->direcciones;
        
        return view('ordenes.show', compact('orden', 'direcciones'));
    }
    
    /**
     * Actualizar información de la orden (dirección, notas)
     */
    public function actualizarInfo(Request $request, $id)
    {
        $orden = Orden::where('user_id', Auth::id())->findOrFail($id);
        
        // No permitir editar si ya fue enviada
        if (in_array($orden->estado, ['enviado', 'entregado', 'cancelado'])) {
            return back()->with('error', 'No puedes editar una orden que ya fue enviada, entregada o cancelada');
        }
        
        $request->validate([
            'direccion_id' => 'nullable|exists:direcciones_entrega,id',
            'notas' => 'nullable|string|max:500',
        ], [
            'notas.max' => 'Las notas no pueden exceder 500 caracteres',
        ]);
        
        // Preparar datos para actualizar
        $datosActualizar = [];
        
        // Solo actualizar dirección si se envió en el request
        if ($request->has('direccion_id') && $request->filled('direccion_id')) {
            // Verificar que la dirección pertenece al usuario
            $direccion = \App\Models\DireccionEntrega::where('id', $request->direccion_id)
                ->where('user_id', Auth::id())
                ->first();
            
            if (!$direccion) {
                return back()->with('error', 'Dirección no válida');
            }
            
            $datosActualizar['direccion_id'] = $request->direccion_id;
        }
        
        // Solo actualizar notas si se envió en el request (puede ser vacío)
        if ($request->has('notas')) {
            $datosActualizar['notas'] = $request->notas;
        }
        
        // Actualizar solo si hay datos
        if (!empty($datosActualizar)) {
            try {
                $orden->update($datosActualizar);
                return back()->with('success', 'Información actualizada correctamente');
            } catch (\Exception $e) {
                \Log::error('Error al actualizar orden: ' . $e->getMessage());
                return back()->with('error', 'Ocurrió un error al actualizar la información. Por favor, intenta de nuevo.');
            }
        }
        
        return back()->with('info', 'No se realizaron cambios');
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
            'numero_tarjeta' => 'required_if:metodo_pago,tarjeta|nullable|string|min:13|max:19',
            'nombre_titular' => 'required_if:metodo_pago,tarjeta|nullable|string|max:255',
            'fecha_expiracion' => 'required_if:metodo_pago,tarjeta|nullable|string|regex:/^\d{2}\/\d{2}$/',
            'cvv' => 'required_if:metodo_pago,tarjeta|nullable|string|min:3|max:4',
            'paypal_email' => 'required_if:metodo_pago,paypal|nullable|email|max:255',
        ], [
            'numero_tarjeta.required_if' => 'El número de tarjeta es obligatorio',
            'numero_tarjeta.min' => 'El número de tarjeta debe tener al menos 13 dígitos',
            'nombre_titular.required_if' => 'El nombre del titular es obligatorio',
            'fecha_expiracion.required_if' => 'La fecha de expiración es obligatoria',
            'fecha_expiracion.regex' => 'La fecha debe tener el formato MM/AA',
            'cvv.required_if' => 'El CVV es obligatorio',
            'cvv.min' => 'El CVV debe tener al menos 3 dígitos',
            'paypal_email.required_if' => 'El email de PayPal es obligatorio',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Limpiar número de tarjeta (quitar espacios)
            $numeroTarjeta = $request->metodo_pago === 'tarjeta' 
                ? str_replace(' ', '', $request->numero_tarjeta) 
                : null;
            
            // Preparar datos de pago
            $datosPago = [
                'metodo' => $request->metodo_pago,
            ];
            
            if ($request->metodo_pago === 'tarjeta') {
                $datosPago['ultimos_digitos'] = substr($numeroTarjeta, -4);
                $datosPago['nombre_titular'] = $request->nombre_titular;
            } else {
                $datosPago['paypal_email'] = $request->paypal_email;
            }
            
            // Actualizar o crear pago
            if ($orden->pago) {
                $orden->pago->update([
                    'metodo_pago' => $request->metodo_pago,
                    'estado' => 'completado',
                    'monto' => $orden->total,
                    'datos_pago' => $datosPago,
                ]);
            } else {
                Pago::create([
                    'orden_id' => $orden->id,
                    'metodo_pago' => $request->metodo_pago,
                    'monto' => $orden->total,
                    'estado' => 'completado',
                    'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                    'datos_pago' => $datosPago,
                ]);
            }
            
            // Cambiar estado de la orden a procesando
            $orden->update(['estado' => 'procesando']);
            
            DB::commit();
            
            return back()->with('success', '¡Pago completado exitosamente! Tu orden está siendo procesada');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al completar pago: ' . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al procesar el pago. Por favor, intenta de nuevo.');
        }
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
        
        try {
            DB::beginTransaction();
            
            // Devolver stock a los productos
            foreach ($orden->detalles as $detalle) {
                $detalle->producto->increment('stock', $detalle->cantidad);
            }
            
            // Actualizar estado
            $orden->update(['estado' => 'cancelado']);
            
            if ($orden->pago) {
                $orden->pago->update(['estado' => 'reembolsado']);
            }
            
            DB::commit();
            
            return redirect()->route('ordenes.index')
                ->with('success', 'Orden cancelada correctamente. El stock ha sido devuelto.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al cancelar orden: ' . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al cancelar la orden. Por favor, intenta de nuevo.');
        }
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

        $items = $carrito->items()->with(['producto.imagenPrincipal', 'producto.categoria'])->get();
        $total = $carrito->calcularTotal();
        $direcciones = Auth::user()->direcciones;

        // Calcular descuento si hay cupón en sesión
        $descuento = 0;
        $cuponAplicado = null;

        if (session('cupon')) {
            $cuponObj = \App\Models\Cupon::with('productos')->find(session('cupon')['id']);
            if ($cuponObj && $cuponObj->esValido()) {
                $cuponAplicado = $cuponObj;
                foreach ($items as $item) {
                    if ($cuponObj->aplicaA($item->producto)) {
                        $descuento += $cuponObj->calcularDescuento($item->subtotal);
                    }
                }
            }
        }

        $totalConDescuento = max(0, $total - $descuento);
        
        return view('ordenes.checkout', compact('items', 'total', 'direcciones', 'descuento', 'totalConDescuento', 'cuponAplicado'));
    }

    /**
     * Procesar el checkout
     */
    public function procesar(Request $request)
    {
        $request->validate([
            'metodo_pago'  => 'required|in:tarjeta,paypal',
            'direccion_id' => 'nullable|exists:direcciones_entrega,id',
        ]);

        $carrito = Auth::user()->carrito;
        
        if (!$carrito || $carrito->items->count() === 0) {
            return redirect()->route('carrito.index')
                ->with('error', 'Tu carrito está vacío');
        }

        try {
            DB::beginTransaction();

            $totalOriginal = $carrito->calcularTotal();
            $descuento     = 0;
            $cuponObj      = null;

            // Aplicar cupón si existe en sesión
            if (session('cupon')) {
                $cuponObj = \App\Models\Cupon::with('productos')
                    ->find(session('cupon')['id']);

                if ($cuponObj && $cuponObj->esValido()) {
                    // Verificar que el usuario no lo haya usado antes
                    $yaUso = \App\Models\CuponUso::where('cupon_id', $cuponObj->id)
                        ->where('user_id', Auth::id())
                        ->exists();

                    if ($yaUso) {
                        $cuponObj = null;
                        session()->forget('cupon');
                    } else {
                        foreach ($carrito->items as $item) {
                            if ($cuponObj->aplicaA($item->producto)) {
                                $descuento += $cuponObj->calcularDescuento($item->subtotal);
                            }
                        }
                    }
                }
            }

            $totalFinal = max(0, $totalOriginal - $descuento);

            // Crear la orden
            $orden = Orden::create([
                'user_id'                => Auth::id(),
                'total'                  => $totalFinal,
                'estado'                 => 'pendiente',
                'fecha_entrega_estimada' => now()->addDays(5),
                'direccion_id'           => $request->direccion_id,
                'notas'                  => null,
            ]);

            // Crear detalles
            foreach ($carrito->items as $item) {
                DetalleOrden::create([
                    'orden_id'        => $orden->id,
                    'producto_id'     => $item->producto_id,
                    'cantidad'        => $item->cantidad,
                    'precio_unitario' => $item->precio_unitario,
                    'subtotal'        => $item->cantidad * $item->precio_unitario,
                ]);

                $item->producto->decrement('stock', $item->cantidad);
            }

            // Registrar uso del cupón
            if ($cuponObj) {
                \App\Models\CuponUso::create([
                    'cupon_id' => $cuponObj->id,
                    'user_id'  => Auth::id(),
                    'orden_id' => $orden->id,
                ]);

                $cuponObj->increment('usos_actuales');
                session()->forget('cupon');
            }

            // Crear pago
            Pago::create([
                'orden_id'       => $orden->id,
                'metodo_pago'    => $request->metodo_pago,
                'monto'          => $totalFinal,
                'estado'         => 'pendiente',
                'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                'datos_pago'     => [
                    'ip'             => $request->ip(),
                    'user_agent'     => $request->userAgent(),
                    'descuento'      => $descuento,
                    'total_original' => $totalOriginal,
                    'cupon'          => $cuponObj?->codigo,
                ],
            ]);

            // Vaciar carrito
            $carrito->items()->delete();

            DB::commit();

            return redirect()->route('ordenes.show', $orden->id)
                ->with('success', '¡Orden creada exitosamente! Por favor completa la información de pago.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al procesar orden: ' . $e->getMessage());
            return back()->with('error', 'Error al procesar la orden. Por favor, intenta de nuevo.');
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