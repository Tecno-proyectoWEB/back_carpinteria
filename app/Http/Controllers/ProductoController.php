<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProductoController extends Controller
{
    public function index()
    {
        return Inertia::render('Productos/Index', [
            'productos' => Producto::with('categoria')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Productos/Create', [
            'categorias' => Categoria::where('activo', true)->get(),
        ]);
    }

    public function store(Request $request)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('productos.crear')) {
            return back()->withErrors(['message' => 'No tiene permiso para crear productos']);
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'categoria_id' => 'required|exists:categoria,id',
            'stock' => 'required|integer|min:0',
            'precio_unitario' => 'required|numeric|min:0',
        ]);

        $producto = Producto::create($request->all());

        return redirect()->route('productos.index')->with('success', 'Producto creado exitosamente');
    }

    public function show(Producto $producto)
    {
        return Inertia::render('Productos/Show', [
            'producto' => $producto->load('categoria'),
        ]);
    }

    public function edit(Producto $producto)
    {
        return Inertia::render('Productos/Edit', [
            'producto' => $producto->load('categoria'),
            'categorias' => Categoria::where('activo', true)->get(),
        ]);
    }

    public function update(Request $request, Producto $producto)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('productos.editar')) {
            return back()->withErrors(['message' => 'No tiene permiso para editar productos']);
        }

        $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'categoria_id' => 'sometimes|required|exists:categoria,id',
            'stock' => 'sometimes|required|integer|min:0',
            'precio_unitario' => 'sometimes|required|numeric|min:0',
        ]);

        $datos_anteriores = $producto->toArray();
        $producto->update($request->all());

        return redirect()->route('productos.index')->with('success', 'Producto actualizado exitosamente');
    }

    public function destroy(Producto $producto)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('productos.eliminar')) {
            return back()->withErrors(['message' => 'No tiene permiso para eliminar productos']);
        }

        $producto->delete();
        return redirect()->route('productos.index')->with('success', 'Producto eliminado exitosamente');
    }
}
