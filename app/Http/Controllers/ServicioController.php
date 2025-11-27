<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ServicioController extends Controller
{
    public function index()
    {
        if (!Auth::user()->tienePermiso('servicios.ver')) {
            return back()->withErrors(['message' => 'No tiene permiso para ver servicios']);
        }

        return Inertia::render('Servicios/Index', [
            'servicios' => Servicio::with('categoria')->get(),
        ]);
    }

    public function create()
    {
        if (!Auth::user()->tienePermiso('servicios.crear')) {
            return back()->withErrors(['message' => 'No tiene permiso para crear servicios']);
        }

        return Inertia::render('Servicios/Create', [
            'categorias' => Categoria::where('activo', true)->get(),
        ]);
    }

    public function store(Request $request)
    {
        if (!Auth::user()->tienePermiso('servicios.crear')) {
            return back()->withErrors(['message' => 'No tiene permiso para crear servicios']);
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio_base' => 'required|numeric|min:0',
            'tiempo_estimado' => 'nullable|integer|min:1',
            'activo' => 'boolean',
            'categoria_id' => 'nullable|exists:categoria,id',
        ]);

        Servicio::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'precio_base' => $request->precio_base,
            'tiempo_estimado' => $request->tiempo_estimado,
            'activo' => $request->activo ?? true,
            'categoria_id' => $request->categoria_id,
        ]);

        return redirect()->route('servicios.index')->with('success', 'Servicio creado correctamente');
    }

    public function edit(Servicio $servicio)
    {
        if (!Auth::user()->tienePermiso('servicios.editar')) {
            return back()->withErrors(['message' => 'No tiene permiso para editar servicios']);
        }

        return Inertia::render('Servicios/Edit', [
            'servicio' => $servicio->load('categoria'),
            'categorias' => Categoria::where('activo', true)->get(),
        ]);
    }

    public function update(Request $request, Servicio $servicio)
    {
        if (!Auth::user()->tienePermiso('servicios.editar')) {
            return back()->withErrors(['message' => 'No tiene permiso para editar servicios']);
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio_base' => 'required|numeric|min:0',
            'tiempo_estimado' => 'nullable|integer|min:1',
            'activo' => 'boolean',
            'categoria_id' => 'nullable|exists:categoria,id',
        ]);

        $servicio->update($request->all());

        return redirect()->route('servicios.index')->with('success', 'Servicio actualizado correctamente');
    }

    public function destroy(Servicio $servicio)
    {
        if (!Auth::user()->tienePermiso('servicios.eliminar')) {
            return back()->withErrors(['message' => 'No tiene permiso para eliminar servicios']);
        }

        $servicio->delete();

        return redirect()->route('servicios.index')->with('success', 'Servicio eliminado correctamente');
    }
}
