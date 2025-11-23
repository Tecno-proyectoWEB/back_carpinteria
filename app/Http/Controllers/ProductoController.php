<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductoController extends Controller
{
    public function index()
    {
        return response()->json(Producto::with('categoria')->get());
    }

    public function store(Request $request)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('productos.crear')) {
            return response()->json(['message' => 'No tiene permiso para crear productos'], 403);
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'categoria_id' => 'required|exists:categoria,id',
            'stock' => 'required|integer|min:0',
            'precio_unitario' => 'required|numeric|min:0',
        ]);

        $producto = Producto::create($request->all());

        // Registrar en bitácora
        \App\Models\Bitacora::create([
            'accion' => 'Producto creado',
            'modulo' => 'Producto',
            'tabla_afectada' => 'producto',
            'registro_id' => $producto->id,
            'datos_nuevos' => $producto->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        return response()->json($producto->load('categoria'), 201);
    }

    public function show(Producto $producto)
    {
        return response()->json($producto->load('categoria'));
    }

    public function update(Request $request, Producto $producto)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('productos.editar')) {
            return response()->json(['message' => 'No tiene permiso para editar productos'], 403);
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

        // Registrar en bitácora
        \App\Models\Bitacora::create([
            'accion' => 'Producto actualizado',
            'modulo' => 'Producto',
            'tabla_afectada' => 'producto',
            'registro_id' => $producto->id,
            'datos_anteriores' => $datos_anteriores,
            'datos_nuevos' => $producto->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        return response()->json($producto->load('categoria'));
    }

    public function destroy(Producto $producto)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('productos.eliminar')) {
            return response()->json(['message' => 'No tiene permiso para eliminar productos'], 403);
        }

        // Registrar en bitácora antes de eliminar
        \App\Models\Bitacora::create([
            'accion' => 'Producto eliminado',
            'modulo' => 'Producto',
            'tabla_afectada' => 'producto',
            'registro_id' => $producto->id,
            'datos_anteriores' => $producto->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        $producto->delete();
        return response()->json(null, 204);
    }
}
