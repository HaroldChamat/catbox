<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class PerfilController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ════════════════════════════════════════
    // INFORMACIÓN GENERAL
    // ════════════════════════════════════════
    public function index()
    {
        return view('perfil.index');
    }

    public function actualizarInfo(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'telefono' => 'nullable|string|max:20',
            'fecha_nacimiento' => 'nullable|date|before:today',
        ], [
            'name.required' => 'El nombre es obligatorio',
            'email.required' => 'El email es obligatorio',
            'email.unique' => 'Este email ya está en uso',
            'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy',
        ]);

        Auth::user()->update([
            'name' => $request->name,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'fecha_nacimiento' => $request->fecha_nacimiento,
        ]);

        return back()->with('success', 'Información actualizada correctamente');
    }

    // ════════════════════════════════════════
    // AVATAR
    // ════════════════════════════════════════
    public function avatar()
    {
        return view('perfil.avatar');
    }

    public function actualizarAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'avatar.required' => 'Debes seleccionar una imagen',
            'avatar.image' => 'El archivo debe ser una imagen',
            'avatar.mimes' => 'Solo se permiten archivos: jpeg, png, jpg, gif',
            'avatar.max' => 'La imagen no puede pesar más de 2MB',
        ]);

        $user = Auth::user();

        // Eliminar avatar anterior si existe
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Guardar nuevo avatar
        $path = $request->file('avatar')->store('avatars', 'public');

        $user->update(['avatar' => $path]);

        return back()->with('success', 'Avatar actualizado correctamente');
    }

    public function eliminarAvatar()
    {
        $user = Auth::user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->update(['avatar' => null]);

        return back()->with('success', 'Avatar eliminado correctamente');
    }

    // ════════════════════════════════════════
    // CONTRASEÑA
    // ════════════════════════════════════════
    public function password()
    {
        return view('perfil.password');
    }

    public function actualizarPassword(Request $request)
    {
        $request->validate([
            'password_actual' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'password_actual.required' => 'Debes ingresar tu contraseña actual',
            'password.required' => 'La nueva contraseña es obligatoria',
            'password.confirmed' => 'Las contraseñas no coinciden',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
        ]);

        // Verificar contraseña actual
        if (!Hash::check($request->password_actual, Auth::user()->password)) {
            return back()->withErrors(['password_actual' => 'La contraseña actual es incorrecta']);
        }

        Auth::user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Contraseña actualizada correctamente');
    }

    // ════════════════════════════════════════
    // DIRECCIONES
    // ════════════════════════════════════════
    public function direcciones()
    {
        $direcciones = Auth::user()->direcciones()->latest()->get();
        return view('perfil.direcciones', compact('direcciones'));
    }

    public function guardarDireccion(Request $request)
    {
        $request->validate([
            'direccion' => 'required|string|max:255',
            'ciudad' => 'required|string|max:100',
            'codigo_postal' => 'required|string|max:20',
            'telefono' => 'required|string|max:20',
            'es_principal' => 'boolean',
        ]);

        // Si se marca como principal, desmarcar las demás
        if ($request->boolean('es_principal')) {
            Auth::user()->direcciones()->update(['es_principal' => false]);
        }

        Auth::user()->direcciones()->create([
            'direccion' => $request->direccion,
            'ciudad' => $request->ciudad,
            'codigo_postal' => $request->codigo_postal,
            'telefono' => $request->telefono,
            'es_principal' => $request->boolean('es_principal'),
        ]);

        return back()->with('success', 'Dirección agregada correctamente');
    }

    public function editarDireccion(Request $request, $id)
    {
        $request->validate([
            'direccion' => 'required|string|max:255',
            'ciudad' => 'required|string|max:100',
            'codigo_postal' => 'required|string|max:20',
            'telefono' => 'required|string|max:20',
            'es_principal' => 'boolean',
        ]);

        $direccion = Auth::user()->direcciones()->findOrFail($id);

        // Si se marca como principal, desmarcar las demás
        if ($request->boolean('es_principal') && !$direccion->es_principal) {
            Auth::user()->direcciones()->update(['es_principal' => false]);
        }

        $direccion->update([
            'direccion' => $request->direccion,
            'ciudad' => $request->ciudad,
            'codigo_postal' => $request->codigo_postal,
            'telefono' => $request->telefono,
            'es_principal' => $request->boolean('es_principal'),
        ]);

        return back()->with('success', 'Dirección actualizada correctamente');
    }

    public function eliminarDireccion($id)
    {
        $direccion = Auth::user()->direcciones()->findOrFail($id);
        
        // Si era principal y hay más direcciones, marcar otra como principal
        if ($direccion->es_principal) {
            $otraDireccion = Auth::user()->direcciones()
                ->where('id', '!=', $id)
                ->first();
            
            if ($otraDireccion) {
                $otraDireccion->update(['es_principal' => true]);
            }
        }

        $direccion->delete();

        return back()->with('success', 'Dirección eliminada correctamente');
    }
}