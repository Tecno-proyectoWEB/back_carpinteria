<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProveedorController extends BaseController
{
    public function index(Request $request)
    {
        // Verificar permisos
        if (!Auth::user()->tienePermiso('proveedores.ver')) {
            return $this->respondError('No tiene permiso para ver proveedores', 403);
        }

        $query = Proveedor::query();

        // Búsqueda
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'ILIKE', "%{$search}%")
                  ->orWhere('ruc', 'ILIKE', "%{$search}%")
                  ->orWhere('email', 'ILIKE', "%{$search}%");
            });
        }

        // Filtro por estado
        if ($request->has('activo')) {
            $query->where('activo', $request->activo === 'activo');
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'id');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Si es petición API, retornar JSON
        if ($this->isApiRequest($request)) {
            return response()->json($query->get());
        }

        // Si es petición web, retornar Inertia con paginación
        $proveedores = $query->paginate($request->get('per_page', 15));

        return $this->respond($proveedores, 'Proveedores/Index', [
            'proveedores' => $proveedores,
            'filters' => $request->only(['search', 'activo', 'sort_by', 'sort_dir']),
        ]);
    }

    public function store(Request $request)
    {
        if (!Auth::user()->tienePermiso('proveedores.crear')) {
            return $this->respondError('No tiene permiso para crear proveedores', 403);
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'ruc' => 'nullable|string|max:255',
            'direccion' => 'nullable|string',
            'telefono' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'persona_contacto' => 'nullable|string|max:255',
            'activo' => 'boolean',
        ]);

        $proveedor = Proveedor::create($validated);

        // Registrar en bitácora
        Bitacora::create([
            'accion' => 'Proveedor creado',
            'modulo' => 'Proveedores',
            'tabla_afectada' => 'proveedor',
            'registro_id' => $proveedor->id,
            'datos_nuevos' => $validated,
            'usuario_id' => Auth::id(),
            'direccion_ip' => $request->ip(),
            'navegador' => $request->userAgent(),
            'fecha' => now(),
        ]);

        if ($this->isApiRequest($request)) {
            return response()->json($proveedor, 201);
        }

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor creado exitosamente');
    }

    public function show(Request $request, Proveedor $proveedor)
    {
        if (!Auth::user()->tienePermiso('proveedores.ver')) {
            return $this->respondError('No tiene permiso para ver proveedores', 403);
        }

        return $this->respond($proveedor, 'Proveedores/Show', [
            'proveedor' => $proveedor,
        ]);
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        if (!Auth::user()->tienePermiso('proveedores.editar')) {
            return $this->respondError('No tiene permiso para editar proveedores', 403);
        }

        $datosAnteriores = $proveedor->toArray();

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'ruc' => 'nullable|string|max:255',
            'direccion' => 'nullable|string',
            'telefono' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'persona_contacto' => 'nullable|string|max:255',
            'activo' => 'boolean',
        ]);

        $proveedor->update($validated);

        // Registrar en bitácora
        Bitacora::create([
            'accion' => 'Proveedor actualizado',
            'modulo' => 'Proveedores',
            'tabla_afectada' => 'proveedor',
            'registro_id' => $proveedor->id,
            'datos_anteriores' => $datosAnteriores,
            'datos_nuevos' => $validated,
            'usuario_id' => Auth::id(),
            'direccion_ip' => $request->ip(),
            'navegador' => $request->userAgent(),
            'fecha' => now(),
        ]);

        if ($this->isApiRequest($request)) {
            return response()->json($proveedor);
        }

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor actualizado exitosamente');
    }

    public function destroy(Request $request, Proveedor $proveedor)
    {
        if (!Auth::user()->tienePermiso('proveedores.eliminar')) {
            return $this->respondError('No tiene permiso para eliminar proveedores', 403);
        }

        $datosAnteriores = $proveedor->toArray();

        $proveedor->delete();

        // Registrar en bitácora
        Bitacora::create([
            'accion' => 'Proveedor eliminado',
            'modulo' => 'Proveedores',
            'tabla_afectada' => 'proveedor',
            'registro_id' => $proveedor->id,
            'datos_anteriores' => $datosAnteriores,
            'usuario_id' => Auth::id(),
            'direccion_ip' => $request->ip(),
            'navegador' => $request->userAgent(),
            'fecha' => now(),
        ]);

        if ($this->isApiRequest($request)) {
            return response()->json(null, 204);
        }

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor eliminado exitosamente');
    }
}
