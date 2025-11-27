# 📊 ¿Cuándo Aparecen las Tablas en la Base de Datos?

## ✅ Respuesta Rápida

Las tablas aparecen cuando ejecutas las **migraciones** de Laravel.

## 🚀 Proceso Completo

### 1. **Migraciones Ejecutadas** ✅

Ya ejecutamos las migraciones anteriormente con:
```bash
php artisan migrate:fresh
```

Este comando:
- ✅ Eliminó todas las tablas existentes (si había)
- ✅ Creó todas las tablas desde cero
- ✅ Ejecutó **34 migraciones** exitosamente

### 2. **Tablas Creadas**

Las siguientes tablas deberían estar en tu base de datos PostgreSQL:

**Tablas del Sistema:**
- `migrations` - Control de migraciones
- `sessions` - Sesiones de usuario
- `menu_items` - Elementos del menú
- `menu_item_rol` - Relación menú-roles
- `page_visits` - Visitas a páginas

**Tablas de Usuarios y Roles:**
- `rol` - Roles del sistema
- `permiso` - Permisos
- `tipo_accion` - Tipos de acciones
- `rol_permiso` - Relación roles-permisos
- `usuario` - Usuarios del sistema

**Tablas de Productos y Servicios:**
- `categoria` - Categorías
- `subcategoria` - Subcategorías
- `sector` - Sectores
- `producto` - Productos
- `pre_producto` - Pre-productos
- `plano` - Planos
- `servicio` - Servicios

**Tablas de Materiales:**
- `almacen` - Almacenes
- `material` - Materiales
- `movimiento_inventario` - Movimientos de inventario

**Tablas de Compras:**
- `proveedor` - Proveedores
- `compra` - Compras
- `detalle_pedido_compra` - Detalles de compras

**Tablas de Pedidos:**
- `pedido` - Pedidos
- `detalle_pedido` - Detalles de pedidos
- `devolucion` - Devoluciones
- `detalle_devolucion` - Detalles de devoluciones

**Tablas de Pagos:**
- `metodo_pago` - Métodos de pago
- `pago` - Pagos
- `stripe_payments` - Pagos de Stripe

**Tablas del Sistema:**
- `bitacora` - Registro de actividades

**Vistas:**
- `v_actividad_usuarios` - Vista de actividad
- `v_compras_proveedor` - Vista de compras por proveedor
- `v_resumen_bitacora` - Resumen de bitácora
- `v_stock_bajo_materiales` - Materiales con stock bajo
- `v_stock_bajo_productos` - Productos con stock bajo
- `v_ventas_diarias` - Ventas diarias

## 🔍 Verificar Tablas en PostgreSQL

### Opción 1: Desde pgAdmin

1. Abre **pgAdmin**
2. Conecta a tu servidor PostgreSQL
3. Expande tu base de datos
4. Ve a **Schemas** → **public** → **Tables**
5. Deberías ver todas las tablas listadas

### Opción 2: Desde Terminal (psql)

```bash
# Conectar a PostgreSQL
psql -U postgres -d tu_base_de_datos

# Listar todas las tablas
\dt

# O con SQL
SELECT table_name 
FROM information_schema.tables 
WHERE table_schema = 'public';
```

### Opción 3: Desde Laravel

```bash
php artisan db:show
```

O verificar con:
```bash
php artisan migrate:status
```

## 📝 Comandos Útiles

### Ver Estado de Migraciones:
```bash
php artisan migrate:status
```

### Ejecutar Migraciones Pendientes:
```bash
php artisan migrate
```

### Resetear Base de Datos (CUIDADO: Elimina todo):
```bash
php artisan migrate:fresh
```

### Resetear y Ejecutar Seeders:
```bash
php artisan migrate:fresh --seed
```

## ⚠️ Importante

- Las tablas **ya están creadas** si ejecutaste `migrate:fresh` anteriormente
- Si no ves las tablas, verifica:
  1. Que estés conectado a la base de datos correcta
  2. Que las migraciones se ejecutaron sin errores
  3. Que PostgreSQL esté corriendo

## 🎯 Resumen

**Las tablas aparecen cuando ejecutas:**
```bash
php artisan migrate
```

**Ya lo hicimos anteriormente, así que las tablas deberían estar en tu base de datos ahora.** ✅

---

¿Quieres que verifique si las tablas están realmente creadas en tu base de datos?

