# Correcciones de Base de Datos - Coherencia Lógica

## Resumen de Correcciones Realizadas

### 1. Eliminación de Foreign Keys a Tablas Eliminadas

#### Migración: `remove_sector_id_from_material_table.php`
- **Problema**: La tabla `material` tenía una foreign key a `sector_id` que referencia a la tabla `sector` (eliminada)
- **Solución**: Eliminada la columna `sector_id`, su índice y su foreign key
- **Impacto**: Los materiales ya no están asociados a sectores (que no existen)

#### Migración: `remove_subcategoria_id_from_categoria_table.php`
- **Problema**: La tabla `categoria` tenía una foreign key a `subcategoria_id` que referencia a la tabla `subcategoria` (eliminada)
- **Solución**: Eliminada la columna `subcategoria_id`, su índice y su foreign key
- **Impacto**: Las categorías ya no tienen subcategorías (estructura simplificada)

#### Migración: `remove_compra_id_from_movimiento_inventario_table.php`
- **Problema**: La tabla `movimiento_inventario` tenía una foreign key a `compra_id` que referencia a la tabla `compra` (eliminada)
- **Solución**: Eliminada la columna `compra_id`, su índice y su foreign key
- **Impacto**: Los movimientos de inventario ya no están asociados a compras (módulo eliminado)

#### Migración: `remove_timestamps_from_metodo_pago_table.php`
- **Problema**: La tabla `metodo_pago` tenía timestamps pero el modelo tiene `$timestamps = false`
- **Solución**: Eliminados los timestamps de la tabla
- **Impacto**: Coherencia entre modelo y tabla de base de datos

## Relaciones de Base de Datos - Verificación de Coherencia

### Relaciones Principales

#### Usuario
- `usuario.rol_id` → `rol.id` (onDelete: restrict) ✓
  - **Lógica**: No se puede eliminar un rol si hay usuarios asignados
- `usuario` → `pedido` (hasMany) ✓
- `usuario` → `movimiento_inventario` (hasMany) ✓
- `usuario` → `pago` (hasMany) ✓

#### Pedido
- `pedido.usuario_id` → `usuario.id` (onDelete: cascade) ✓
  - **Lógica**: Si se elimina un usuario, se eliminan sus pedidos
- `pedido.metodo_pago_id` → `metodo_pago.id` (onDelete: restrict) ✓
  - **Lógica**: No se puede eliminar un método de pago si hay pedidos
- `pedido` → `detalle_pedido` (hasMany) ✓
- `pedido` → `pago` (hasMany) ✓
- `pedido` → `movimiento_inventario` (hasMany) ✓

#### DetallePedido
- `detalle_pedido.pedido_id` → `pedido.id` (onDelete: cascade) ✓
  - **Lógica**: Si se elimina un pedido, se eliminan sus detalles
- `detalle_pedido.producto_id` → `producto.id` (onDelete: set null) ✓
  - **Lógica**: Si se elimina un producto, el detalle puede quedar sin producto
- `detalle_pedido.servicio_id` → `servicio.id` (onDelete: set null) ✓
  - **Lógica**: Si se elimina un servicio, el detalle puede quedar sin servicio
- **Nota**: Un detalle debe tener `producto_id` O `servicio_id`, pero no ambos

#### Pago
- `pago.pedido_id` → `pedido.id` (onDelete: cascade) ✓
  - **Lógica**: Si se elimina un pedido, se eliminan sus pagos
- `pago.metodo_pago_id` → `metodo_pago.id` (onDelete: restrict) ✓
  - **Lógica**: No se puede eliminar un método de pago si hay pagos
- `pago.usuario_id` → `usuario.id` (onDelete: set null) ✓
  - **Lógica**: Si se elimina un usuario, el pago puede quedar sin usuario (auditoría)

#### MovimientoInventario
- `movimiento_inventario.material_id` → `material.id` (onDelete: set null) ✓
  - **Lógica**: Si se elimina un material, el movimiento puede quedar sin material (auditoría)
- `movimiento_inventario.producto_id` → `producto.id` (onDelete: set null) ✓
  - **Lógica**: Si se elimina un producto, el movimiento puede quedar sin producto (auditoría)
- `movimiento_inventario.usuario_id` → `usuario.id` (onDelete: restrict) ✓
  - **Lógica**: No se puede eliminar un usuario si hay movimientos (auditoría)
- `movimiento_inventario.pedido_id` → `pedido.id` (onDelete: set null) ✓
  - **Lógica**: Si se elimina un pedido, el movimiento puede quedar sin pedido (auditoría)
- **Nota**: Un movimiento debe tener `material_id` O `producto_id`, pero no ambos

#### Producto
- `producto.categoria_id` → `categoria.id` (onDelete: set null) ✓
  - **Lógica**: Si se elimina una categoría, el producto puede quedar sin categoría
- `producto` → `detalle_pedido` (hasMany) ✓
- `producto` → `movimiento_inventario` (hasMany) ✓

#### Servicio
- `servicio.categoria_id` → `categoria.id` (onDelete: set null) ✓
  - **Lógica**: Si se elimina una categoría, el servicio puede quedar sin categoría
- `servicio` → `detalle_pedido` (hasMany) ✓

#### Material
- `material.categoria_id` → `categoria.id` (onDelete: set null) ✓
  - **Lógica**: Si se elimina una categoría, el material puede quedar sin categoría
- `material` → `movimiento_inventario` (hasMany) ✓

#### Categoria
- `categoria` → `producto` (hasMany) ✓
- `categoria` → `servicio` (hasMany) ✓
- `categoria` → `material` (hasMany) ✓

#### MetodoPago
- `metodo_pago` → `pedido` (hasMany) ✓
- `metodo_pago` → `pago` (hasMany) ✓

#### Rol y Permisos
- `usuario.rol_id` → `rol.id` (onDelete: restrict) ✓
- `rol` → `usuario` (hasMany) ✓
- `rol` ↔ `permiso` (belongsToMany a través de `rol_permiso`) ✓
- `rol_permiso.rol_id` → `rol.id` ✓
- `rol_permiso.permiso_id` → `permiso.id` ✓

## Validaciones Lógicas Implementadas

### En Controladores
1. **DetallePedido**: Se valida que tenga `producto_id` O `servicio_id`, pero no ambos
2. **MovimientoInventario**: Se valida que tenga `material_id` O `producto_id`, pero no ambos
3. **Stock**: Se valida stock disponible antes de crear pedidos o movimientos de salida
4. **Permisos**: Se validan permisos en todos los controladores antes de realizar operaciones

### En Modelos
1. **Timestamps**: Todos los modelos tienen `$timestamps = false` excepto los que usan timestamps por defecto
2. **Relaciones**: Todas las relaciones están correctamente definidas con las foreign keys apropiadas
3. **Casts**: Los tipos de datos están correctamente definidos en los modelos

## Estado Final

✅ Todas las foreign keys están correctamente definidas
✅ Todas las relaciones son coherentes y lógicas
✅ No hay referencias a tablas eliminadas
✅ Las restricciones de eliminación son apropiadas para cada caso
✅ Los modelos y las migraciones están sincronizados

## Próximos Pasos

1. Ejecutar las migraciones: `php artisan migrate`
2. Verificar que no haya errores de integridad referencial
3. Probar las operaciones CRUD en cada módulo
4. Verificar que las validaciones funcionen correctamente

