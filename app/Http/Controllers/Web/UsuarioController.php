<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Traits\HasPermissions;
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UsuarioController extends Controller
{
    use HasPermissions;

    public function index(Request $request)
    {
        $this->autorizarPermiso('usuarios.ver', 'No tiene permiso para ver usuarios');

        $query = Usuario::with('rol');

        // Búsqueda
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'ILIKE', "%{$search}%")
                  ->orWhere('apellido', 'ILIKE', "%{$search}%")
                  ->orWhere('email', 'ILIKE', "%{$search}%");
            });
        }

        // Filtros
        if ($request->has('rol_id')) {
            $query->where('rol_id', $request->rol_id);
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->estado === 'activo');
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'id');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $usuarios = $query->paginate($request->get('per_page', 15));
        $roles = Rol::all();

        return Inertia::render('Usuarios/Index', [
            'usuarios' => $usuarios,
            'roles' => $roles,
            'filters' => $request->only(['search', 'rol_id', 'estado', 'sort_by', 'sort_dir']),
        ]);
    }

    public function create()
    {
        $this->autorizarPermiso('usuarios.crear', 'No tiene permiso para crear usuarios');

        $roles = Rol::all();

        return Inertia::render('Usuarios/Create', [
            'roles' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $this->autorizarPermiso('usuarios.crear', 'No tiene permiso para crear usuarios');

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuario',
            'telefono' => 'nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'rol_id' => 'required|exists:rol,id',
            'estado' => 'boolean',
            'disponibilidad' => 'boolean',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'apellido.required' => 'El apellido es obligatorio.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe ser válido.',
            'email.unique' => 'Este email ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'rol_id.required' => 'El rol es obligatorio.',
        ]);

        $usuario = Usuario::create([
            'nombre' => $validated['nombre'],
            'apellido' => $validated['apellido'],
            'email' => $validated['email'],
            'telefono' => $validated['telefono'] ?? null,
            'password' => Hash::make($validated['password']),
            'rol_id' => $validated['rol_id'],
            'estado' => $validated['estado'] ?? true,
            'disponibilidad' => $validated['disponibilidad'] ?? true,
            'cuenta_no_expirada' => true,
            'cuenta_no_bloqueada' => true,
            'credenciales_no_expiradas' => true,
        ]);

        \App\Models\Bitacora::create([
            'accion' => 'Usuario creado',
            'modulo' => 'Usuario',
            'tabla_afectada' => 'usuario',
            'registro_id' => $usuario->id,
            'datos_nuevos' => $usuario->toArray(),
            'usuario_id' => auth()->id(),
            'fecha' => now(),
        ]);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario creado exitosamente');
    }

    public function show(Usuario $usuario)
    {
        $this->autorizarPermiso('usuarios.ver', 'No tiene permiso para ver usuarios');

        $usuario->load('rol.permisos');

        return Inertia::render('Usuarios/Show', [
            'usuario' => $usuario,
        ]);
    }

    public function edit(Usuario $usuario)
    {
        $this->autorizarPermiso('usuarios.editar', 'No tiene permiso para editar usuarios');

        $roles = Rol::all();

        return Inertia::render('Usuarios/Edit', [
            'usuario' => $usuario->load('rol'),
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, Usuario $usuario)
    {
        $this->autorizarPermiso('usuarios.editar', 'No tiene permiso para editar usuarios');

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuario,email,' . $usuario->id,
            'telefono' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8|confirmed',
            'rol_id' => 'required|exists:rol,id',
            'estado' => 'boolean',
            'disponibilidad' => 'boolean',
            'cuenta_no_expirada' => 'boolean',
            'cuenta_no_bloqueada' => 'boolean',
            'credenciales_no_expiradas' => 'boolean',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'apellido.required' => 'El apellido es obligatorio.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe ser válido.',
            'email.unique' => 'Este email ya está registrado.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'rol_id.required' => 'El rol es obligatorio.',
        ]);

        $datosAnteriores = $usuario->toArray();

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $usuario->update($validated);

        \App\Models\Bitacora::create([
            'accion' => 'Usuario actualizado',
            'modulo' => 'Usuario',
            'tabla_afectada' => 'usuario',
            'registro_id' => $usuario->id,
            'datos_anteriores' => $datosAnteriores,
            'datos_nuevos' => $usuario->toArray(),
            'usuario_id' => auth()->id(),
            'fecha' => now(),
        ]);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado exitosamente');
    }

    public function destroy(Usuario $usuario)
    {
        $this->autorizarPermiso('usuarios.eliminar', 'No tiene permiso para eliminar usuarios');

        if ($usuario->id === auth()->id()) {
            return back()->withErrors(['error' => 'No puede eliminarse a sí mismo']);
        }

        \App\Models\Bitacora::create([
            'accion' => 'Usuario eliminado',
            'modulo' => 'Usuario',
            'tabla_afectada' => 'usuario',
            'registro_id' => $usuario->id,
            'datos_anteriores' => $usuario->toArray(),
            'usuario_id' => auth()->id(),
            'fecha' => now(),
        ]);

        $usuario->delete();

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario eliminado exitosamente');
    }
}


