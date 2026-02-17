<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cupon;
use App\Models\CategoriaProducto;
use App\Models\Producto;
use App\Models\User;
use App\Notifications\CuponNotification;
use Illuminate\Http\Request;

class CuponAdminController extends Controller
{
    public function index()
    {
        $cupones = Cupon::with('categoria')->latest()->paginate(15);
        return view('admin.cupones.index', compact('cupones'));
    }

    public function crear()
    {
        $categorias = CategoriaProducto::orderBy('nombre')->get();
        $productos  = Producto::where('activo', true)->orderBy('nombre')->get();
        $codigoSugerido = Cupon::generarCodigo();
        return view('admin.cupones.crear', compact('categorias', 'productos', 'codigoSugerido'));
    }

    public function guardar(Request $request)
    {
        $request->validate([
            'codigo'            => 'required|string|unique:cupones,codigo',
            'tipo'              => 'required|in:porcentaje,monto_fijo',
            'valor'             => 'required|numeric|min:1',
            'alcance'           => 'required|in:tienda,categoria,productos',
            'categoria_id'      => 'nullable|exists:categorias_producto,id',
            'limite_usos'       => 'nullable|integer|min:1',
            'fecha_expiracion'  => 'nullable|date|after:today',
            'productos'         => 'nullable|array',
            'notificar'         => 'nullable|in:todos,especifico',
            'user_id'           => 'nullable|exists:users,id',
        ]);

        $cupon = Cupon::create([
            'codigo'           => strtoupper($request->codigo),
            'tipo'             => $request->tipo,
            'valor'            => $request->valor,
            'alcance'          => $request->alcance,
            'categoria_id'     => $request->categoria_id,
            'limite_usos'      => $request->limite_usos,
            'fecha_expiracion' => $request->fecha_expiracion,
            'activo'           => true,
        ]);

        if ($request->alcance === 'productos' && $request->productos) {
            $cupon->productos()->sync($request->productos);
        }

        // Enviar notificación
        if ($request->notificar === 'todos') {
            User::where('activo', true)->each(function ($user) use ($cupon) {
                $user->notify(new CuponNotification($cupon));
            });
        } elseif ($request->notificar === 'especifico' && $request->user_id) {
            $user = User::findOrFail($request->user_id);
            $user->notify(new CuponNotification($cupon));
        }

        return redirect()->route('admin.cupones.index')
            ->with('success', 'Cupón creado correctamente');
    }

    public function toggleActivo($id)
    {
        $cupon = Cupon::findOrFail($id);
        $cupon->update(['activo' => !$cupon->activo]);
        return back()->with('success', 'Estado del cupón actualizado');
    }

    public function destruir($id)
    {
        Cupon::findOrFail($id)->delete();
        return back()->with('success', 'Cupón eliminado');
    }
}