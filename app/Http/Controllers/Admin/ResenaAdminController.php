<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resena;

class ResenaAdminController extends Controller
{
    public function index()
    {
        $pendientes = Resena::with('user', 'producto')
            ->pendientes()
            ->latest()
            ->paginate(15);

        return view('admin.resenas.index', compact('pendientes'));
    }

    public function aprobar($id)
    {
        $resena = Resena::findOrFail($id);
        $resena->update(['estado' => 'aprobada']);
        return back()->with('success', 'Reseña aprobada');
    }

    public function rechazar($id)
    {
        $resena = Resena::findOrFail($id);
        $resena->update(['estado' => 'rechazada']);
        return back()->with('success', 'Reseña rechazada');
    }

    public function destruir($id)
    {
        Resena::findOrFail($id)->delete();
        return back()->with('success', 'Reseña eliminada');
    }
}