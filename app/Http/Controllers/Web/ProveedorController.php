<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->tienePermiso('proveedores.ver')) {
            abort(403, 'No tiene permiso para ver proveedores');
        }

        $query = Proveedor::query();

        // Búsqueda
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'ILIKE', "%{$search}%")
                  ->orWhere('ruc', 'ILIKE', "%{$search}%")
                  ->orWhere('email', 'ILIKE', "%{$search}%")
                  ->orWhere('telefono', 'ILIKE', "%{$search}%");
            });
        }

        // Filtro por estado
        if ($request->has('activo')) {
            $query->where('activo', $request->activo === 'activo');
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'nombre');
        $sortDir = $request->get('sort_dir', 'asc');
        $query->orderBy($sortBy, $sortDir);

        $proveedores = $query->paginate($request->get('per_page', 15));

        return Inertia::render('Proveedores/Index', [
            'proveedores' => $proveedores,
            'filters' => $request->only(['search', 'activo', 'sort_by', 'sort_dir']),
        ]);
    }

    public function create()
    {
        if (!Auth::user()->tienePermiso('proveedores.crear')) {
            abort(403, 'No tiene permiso para crear proveedores');
        }

        return Inertia::render('Proveedores/Create');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->tienePermiso('proveedores.crear')) {
            abort(403, 'No tiene permiso para crear proveedores');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'ruc' => 'nullable|string|max:255|unique:proveedor,ruc',
            'direccion' => 'nullable|string',
            'telefono' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'persona_contacto' => 'nullable|string|max:255',
            'activo' => 'boolean',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'ruc.unique' => 'Este RUC ya está registrado.',
            'email.email' => 'El email debe ser válido.',
        ]);

        $proveedor = Proveedor::create($validated);

        \App\Models\Bitacora::create([
            'accion' => 'Proveedor creado',
            'modulo' => 'Proveedor',
            'tabla_afectada' => 'proveedor',
            'registro_id' => $proveedor->id,
            'datos_nuevos' => $proveedor->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor creado exitosamente');
    }

    public function show(Proveedor $proveedor)
    {
        if (!Auth::user()->tienePermiso('proveedores.ver')) {
            abort(403, 'No tiene permiso para ver proveedores');
        }

        $proveedor->load('compras');

        return Inertia::render('Proveedores/Show', [
            'proveedor' => $proveedor,
        ]);
    }

    public function edit(Proveedor $proveedor)
    {
        if (!Auth::user()->tienePermiso('proveedores.editar')) {
            abort(403, 'No tiene permiso para editar proveedores');
        }

        return Inertia::render('Proveedores/Edit', [
            'proveedor' => $proveedor,
        ]);
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        if (!Auth::user()->tienePermiso('proveedores.editar')) {
            abort(403, 'No tiene permiso para editar proveedores');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'ruc' => 'nullable|string|max:255|unique:proveedor,ruc,' . $proveedor->id,
            'direccion' => 'nullable|string',
            'telefono' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'persona_contacto' => 'nullable|string|max:255',
            'activo' => 'boolean',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'ruc.unique' => 'Este RUC ya está registrado.',
            'email.email' => 'El email debe ser válido.',
        ]);

        $datosAnteriores = $proveedor->toArray();
        $proveedor->update($validated);

        \App\Models\Bitacora::create([
            'accion' => 'Proveedor actualizado',
            'modulo' => 'Proveedor',
            'tabla_afectada' => 'proveedor',
            'registro_id' => $proveedor->id,
            'datos_anteriores' => $datosAnteriores,
            'datos_nuevos' => $proveedor->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor actualizado exitosamente');
    }

    public function destroy(Proveedor $proveedor)
    {
        if (!Auth::user()->tienePermiso('proveedores.eliminar')) {
            abort(403, 'No tiene permiso para eliminar proveedores');
        }

        \App\Models\Bitacora::create([
            'accion' => 'Proveedor eliminado',
            'modulo' => 'Proveedor',
            'tabla_afectada' => 'proveedor',
            'registro_id' => $proveedor->id,
            'datos_anteriores' => $proveedor->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        $proveedor->delete();

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor eliminado exitosamente');
    }
}

