<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\ItemCarrito;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CarritoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar el carrito
     */
    public function index()
    {
        $carrito = Auth::user()->carrito;
        
        if (!$carrito) {
            $items = collect([]);
            $total = 0;
        } else {
            $items = $carrito->items()->with(['producto.imagenPrincipal', 'producto.categoria'])->get();
            $total = $carrito->calcularTotal();
        }

        // Créditos disponibles del usuario
        $creditosDisponibles = Auth::user()->creditosDisponibles();
        $saldoCreditosTotal = Auth::user()->saldoCreditosTotal();

        return view('carrito.index', compact('carrito', 'items', 'total', 'creditosDisponibles', 'saldoCreditosTotal'));
    }

    /**
     * Agregar producto al carrito
     */
    public function agregar(Request $request, $productoId)
    {
        $request->validate([
            'cantidad' => 'required|integer|min:1'
        ]);

        $producto = Producto::findOrFail($productoId);
        
        // Verificar stock
        if ($producto->stock < $request->cantidad) {
            return back()->with('error', 'Stock insuficiente');
        }

        $carrito = Auth::user()->obtenerOCrearCarrito();
        
        // Verificar si el producto ya está en el carrito
        $itemExistente = ItemCarrito::where('carrito_id', $carrito->id)
            ->where('producto_id', $productoId)
            ->first();

        if ($itemExistente) {
            $nuevaCantidad = $itemExistente->cantidad + $request->cantidad;
            
            if ($producto->stock < $nuevaCantidad) {
                return back()->with('error', 'Stock insuficiente');
            }
            
            $itemExistente->update(['cantidad' => $nuevaCantidad]);
        } else {
            ItemCarrito::create([
                'carrito_id' => $carrito->id,
                'producto_id' => $productoId,
                'cantidad' => $request->cantidad,
                'precio_unitario' => $producto->precio
            ]);
        }

        return back()->with('success', 'Producto agregado al carrito');
    }

    /**
     * Actualizar cantidad de un item
     */
    public function actualizar(Request $request, $itemId)
    {
        $request->validate([
            'cantidad' => 'required|integer|min:1'
        ]);

        $item = ItemCarrito::findOrFail($itemId);
        
        // Verificar que el item pertenece al carrito del usuario
        if ($item->carrito->user_id !== Auth::id()) {
            abort(403);
        }

        // Verificar stock
        if ($item->producto->stock < $request->cantidad) {
            return back()->with('error', 'Stock insuficiente');
        }

        $item->update(['cantidad' => $request->cantidad]);

        return back()->with('success', 'Carrito actualizado');
    }

    /**
     * Eliminar item del carrito
     */
    public function eliminar($itemId)
    {
        $item = ItemCarrito::findOrFail($itemId);
        
        // Verificar que el item pertenece al carrito del usuario
        if ($item->carrito->user_id !== Auth::id()) {
            abort(403);
        }

        $item->delete();

        return back()->with('success', 'Producto eliminado del carrito');
    }

    /**
     * Vaciar carrito completo
     */
    public function vaciar()
    {
        $carrito = Auth::user()->carrito;
        
        if ($carrito) {
            $carrito->items()->delete();
        }

        return back()->with('success', 'Carrito vaciado');
    }
}