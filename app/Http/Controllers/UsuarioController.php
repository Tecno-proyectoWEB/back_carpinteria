<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use App\Http\Controllers\MenuController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UsuarioController extends BaseController
{
    public function index(Request $request)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('usuarios.ver')) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No tiene permiso para ver usuarios'], 403);
            }
            abort(403, 'No tiene permiso para ver usuarios');
        }

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

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($query->get());
        }

        // Si es petición web, retornar Inertia con paginación
        $usuarios = $query->paginate($request->get('per_page', 15));
        $roles = Rol::all();

        // Compartir usuario autenticado
        $this->shareAuthUser($request);
        
        // Obtener menú y visitas para web
        $menuController = new MenuController();
        $menuItems = $menuController->getMenuForUser($request->user());
        $pageVisits = \App\Models\PageVisit::obtenerContador($request->path());

        return Inertia::render('Usuarios/Index', [
            'usuarios' => $usuarios,
            'roles' => $roles,
            'menuItems' => $menuItems,
            'pageVisits' => $pageVisits,
            'filters' => $request->only(['search', 'rol_id', 'estado', 'sort_by', 'sort_dir']),
        ]);
    }

    public function create(Request $request)
    {
        // Solo para web
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Use POST /api/usuarios para crear'], 405);
        }

        if (!Auth::user()->tienePermiso('usuarios.crear')) {
            abort(403, 'No tiene permiso para crear usuarios');
        }

        $roles = Rol::all();
        
        // Obtener menú y visitas para web
        $menuController = new MenuController();
        $menuItems = $menuController->getMenuForUser($request->user());
        $pageVisits = \App\Models\PageVisit::obtenerContador($request->path());

        return Inertia::render('Usuarios/Create', [
            'roles' => $roles,
            'menuItems' => $menuItems,
            'pageVisits' => $pageVisits,
        ]);
    }

    public function store(Request $request)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('usuarios.crear')) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No tiene permiso para crear usuarios'], 403);
            }
            abort(403, 'No tiene permiso para crear usuarios');
        }

        // Log para debugging
        \Log::info('Creando usuario', [
            'request_data' => $request->all(),
            'user_id' => Auth::id(),
        ]);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuario',
            'telefono' => 'nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'rol_id' => 'required|integer|exists:rol,id',
        ]);

        try {
            $usuario = Usuario::create([
                'nombre' => $validated['nombre'],
                'apellido' => $validated['apellido'],
                'email' => $validated['email'],
                'telefono' => $validated['telefono'] ?? null,
                'password' => Hash::make($validated['password']),
                'rol_id' => (int) $validated['rol_id'],
                'estado' => $request->boolean('estado', true),
                'disponibilidad' => $request->boolean('disponibilidad', true),
                'cuenta_no_expirada' => $request->boolean('cuenta_no_expirada', true),
                'cuenta_no_bloqueada' => $request->boolean('cuenta_no_bloqueada', true),
                'credenciales_no_expiradas' => $request->boolean('credenciales_no_expiradas', true),
            ]);

            \Log::info('Usuario creado exitosamente', ['usuario_id' => $usuario->id]);

            // Registrar en bitácora
            try {
                \App\Models\Bitacora::create([
                    'accion' => 'Usuario creado',
                    'modulo' => 'Usuario',
                    'tabla_afectada' => 'usuario',
                    'registro_id' => $usuario->id,
                    'datos_nuevos' => $usuario->toArray(),
                    'usuario_id' => Auth::id(),
                    'fecha' => now(),
                ]);
            } catch (\Exception $e) {
                \Log::warning('Error al registrar en bitácora', ['error' => $e->getMessage()]);
            }
        } catch (\Exception $e) {
            \Log::error('Error al crear usuario', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($usuario->load('rol'), 201);
        }

        // Si es petición web, redirigir
        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario creado exitosamente');
    }

    public function show(Request $request, Usuario $usuario)
    {
        // Cargar relaciones necesarias
        $usuario->load(['rol.permisos']);

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($usuario);
        }

        // Si es petición web, retornar Inertia
        $menuController = new MenuController();
        $menuItems = $menuController->getMenuForUser($request->user());
        $pageVisits = \App\Models\PageVisit::obtenerContador($request->path());

        return Inertia::render('Usuarios/Show', [
            'usuario' => $usuario,
            'menuItems' => $menuItems,
            'pageVisits' => $pageVisits,
        ]);
    }

    public function edit(Request $request, Usuario $usuario)
    {
        // Solo para web
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Use PUT /api/usuarios/{id} para actualizar'], 405);
        }

        if (!Auth::user()->tienePermiso('usuarios.editar')) {
            abort(403, 'No tiene permiso para editar usuarios');
        }

        $roles = Rol::all();
        $menuController = new MenuController();
        $menuItems = $menuController->getMenuForUser($request->user());
        $pageVisits = \App\Models\PageVisit::obtenerContador($request->path());

        return Inertia::render('Usuarios/Edit', [
            'usuario' => $usuario->load('rol'),
            'roles' => $roles,
            'menuItems' => $menuItems,
            'pageVisits' => $pageVisits,
        ]);
    }

    public function update(Request $request, Usuario $usuario)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('usuarios.editar')) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No tiene permiso para editar usuarios'], 403);
            }
            abort(403, 'No tiene permiso para editar usuarios');
        }

        // Log para debugging
        \Log::info('Actualizando usuario', [
            'usuario_id' => $usuario->id,
            'request_data' => $request->all(),
            'user_id' => Auth::id(),
        ]);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuario,email,' . $usuario->id,
            'telefono' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8|confirmed',
            'rol_id' => 'required|integer|exists:rol,id',
            'estado' => 'boolean',
            'disponibilidad' => 'boolean',
            'cuenta_no_expirada' => 'boolean',
            'cuenta_no_bloqueada' => 'boolean',
            'credenciales_no_expiradas' => 'boolean',
        ]);

        // Preparar datos para actualizar
        $updateData = [
            'nombre' => $validated['nombre'],
            'apellido' => $validated['apellido'],
            'email' => $validated['email'],
            'telefono' => $validated['telefono'] ?? null,
            'rol_id' => (int) $validated['rol_id'],
            'estado' => $request->boolean('estado', $usuario->estado),
            'disponibilidad' => $request->boolean('disponibilidad', $usuario->disponibilidad),
            'cuenta_no_expirada' => $request->boolean('cuenta_no_expirada', $usuario->cuenta_no_expirada),
            'cuenta_no_bloqueada' => $request->boolean('cuenta_no_bloqueada', $usuario->cuenta_no_bloqueada),
            'credenciales_no_expiradas' => $request->boolean('credenciales_no_expiradas', $usuario->credenciales_no_expiradas),
        ];

        // Solo actualizar password si se proporciona
        if (isset($validated['password']) && !empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $datos_anteriores = $usuario->toArray();
        
        try {
            $usuario->update($updateData);
            \Log::info('Usuario actualizado exitosamente', ['usuario_id' => $usuario->id]);
        } catch (\Exception $e) {
            \Log::error('Error al actualizar usuario', [
                'usuario_id' => $usuario->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }

        // Registrar en bitácora
        \App\Models\Bitacora::create([
            'accion' => 'Usuario actualizado',
            'modulo' => 'Usuario',
            'tabla_afectada' => 'usuario',
            'registro_id' => $usuario->id,
            'datos_anteriores' => $datos_anteriores,
            'datos_nuevos' => $usuario->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($usuario->load('rol'));
        }

        // Si es petición web, redirigir
        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado exitosamente');
    }

    public function destroy(Request $request, Usuario $usuario)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('usuarios.eliminar')) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No tiene permiso para eliminar usuarios'], 403);
            }
            abort(403, 'No tiene permiso para eliminar usuarios');
        }

        // No permitir auto-eliminación
        if ($usuario->id === Auth::id()) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'No puede eliminarse a sí mismo'], 400);
            }
            return back()->withErrors(['error' => 'No puede eliminarse a sí mismo']);
        }

        // Registrar en bitácora antes de eliminar
        \App\Models\Bitacora::create([
            'accion' => 'Usuario eliminado',
            'modulo' => 'Usuario',
            'tabla_afectada' => 'usuario',
            'registro_id' => $usuario->id,
            'datos_anteriores' => $usuario->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        $usuario->delete();

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(null, 204);
        }

        // Si es petición web, redirigir
        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario eliminado exitosamente');
    }
}
