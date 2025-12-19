# 📋 Flujo Completo de la Aplicación - Sistema de Carpintería

## 🔐 1. AUTENTICACIÓN Y ACCESO INICIAL

### Login (Punto de Entrada)
**Ruta:** `/login`
**Vista:** `Auth/Login.vue`

**Proceso:**
1. Usuario ingresa **email** y **contraseña**
2. Sistema valida credenciales en `AuthController::login()`
3. Si es válido:
   - Crea sesión con Laravel Sanctum
   - Redirige a `/dashboard`
4. Si es inválido:
   - Muestra error "Credenciales incorrectas"

**Validaciones:**
- ✅ Usuario debe existir en tabla `usuario`
- ✅ Contraseña debe coincidir (hash bcrypt)
- ✅ Usuario debe tener `estado = true` (activo)
- ✅ Usuario debe tener `cuenta_no_bloqueada = true`

---

## 🏠 2. DASHBOARD (Después del Login)

**Ruta:** `/dashboard`
**Vista:** `Dashboard.vue`
**Controller:** `DashboardController::index()`

### Información Mostrada por Rol:

#### 👑 Administrador
- **Resumen general del negocio:**
  - Total de ventas del mes
  - Total de productos en stock
  - Total de materiales disponibles
  - Ventas pendientes de pago
  - Gráficos de tendencias

#### 👷 Operario/Empleado
- **Vista limitada:**
  - Solo inventarios que puede ver
  - Productos asignados
  - Tareas pendientes

#### 📊 Estadísticas Visibles:
- Ventas por día/semana/mes
- Productos más vendidos
- Materiales con stock bajo
- Compras recientes
- Pagos pendientes

---

## 🧭 3. MENÚ DE NAVEGACIÓN (Dinámico por Rol)

El menú se genera automáticamente desde `MenuController::getMenuForUser()` basado en:
- Rol del usuario
- Permisos asignados al rol
- Tabla `menu_items` + pivot `menu_item_rol`

### Estructura del Menú:

```
📂 GESTIÓN
├── 👥 Usuarios (si tiene permiso usuarios.ver)
├── 🛡️ Roles (si tiene permiso roles.ver)
├── 📦 Productos (si tiene permiso productos.ver)
├── 🔧 Servicios (si tiene permiso servicios.ver)
├── 🪵 Materiales (si tiene permiso materiales.ver)

📂 OPERACIONES
├── 💰 Ventas (si tiene permiso ventas.ver)
├── 💳 Pagos (si tiene permiso pagos.ver)
├── 📦 Inventarios (si tiene permiso inventarios.ver)

📂 PROVEEDORES Y COMPRAS (NUEVO)
├── 🏢 Proveedores (si tiene permiso proveedores.ver)
├── 🏭 Almacenes (si tiene permiso almacenes.ver)
├── 🛒 Compras (si tiene permiso compras.ver)

📂 REPORTES
├── 📊 Reportes de Ventas
├── 📦 Reportes de Inventario
├── 🛒 Reportes de Compras (NUEVO)
├── 📈 Estadísticas

📂 AUDITORÍA (NUEVO)
├── 📜 Bitácora (si tiene permiso bitacora.ver)

📂 SISTEMA
├── 🔍 Búsqueda Global
├── 🚪 Cerrar Sesión
```

---

## 📦 4. MÓDULOS PRINCIPALES Y SUS FLUJOS

## 4.1 👥 GESTIÓN DE USUARIOS

### Ver Usuarios (`/usuarios`)
**Permisos requeridos:** `usuarios.ver`
**Vista:** `Usuarios/Index.vue`

**Funcionalidades:**
- ✅ Lista paginada de usuarios (15 por página)
- ✅ Búsqueda por nombre, apellido, email (ILIKE - case insensitive)
- ✅ Filtros por rol y estado (activo/inactivo)
- ✅ Ordenamiento por columnas
- ✅ Acciones: Ver | Editar | Eliminar

### Crear Usuario (`/usuarios/create`)
**Permisos requeridos:** `usuarios.crear`
**Vista:** `Usuarios/Create.vue`

**Campos:**
- Nombre* (required)
- Apellido* (required)
- Email* (required, único)
- Teléfono
- Contraseña* (required, min 8)
- Confirmar Contraseña* (must match)
- Rol* (select desde tabla `rol`)
- Estado (activo/inactivo)

**Proceso:**
1. Usuario completa formulario
2. Validación en frontend (Vue)
3. POST a `UsuarioController::store()`
4. Validación en backend
5. Password hasheada con bcrypt
6. **Bitácora registra:** "Usuario creado: [nombre]"
7. Redirige a `/usuarios` con mensaje de éxito

### Editar Usuario (`/usuarios/{id}/edit`)
**Permisos requeridos:** `usuarios.editar`
**Vista:** `Usuarios/Edit.vue`

**Cambios permitidos:**
- Todos los campos excepto:
  - No puede cambiar su propio rol si es el único admin
  - No puede desactivarse a sí mismo
- **Bitácora registra:** datos_anteriores + datos_nuevos

---

## 4.2 📦 GESTIÓN DE PRODUCTOS

### Ver Productos (`/productos`)
**Permisos requeridos:** `productos.ver`
**Vista:** `Productos/Index.vue` (517 líneas - versión completa)

**Funcionalidades avanzadas:**
- ✅ Búsqueda por nombre/descripción (ILIKE)
- ✅ Filtro por categoría
- ✅ Paginación (15 por página, configurable)
- ✅ Ordenamiento por: ID, Nombre, Stock, Precio
- ✅ Vista de cards o tabla
- ✅ Indicadores de stock bajo (alerta cuando stock < stock_minimo)

### Crear Producto (`/productos/create`)
**Permisos requeridos:** `productos.crear`
**Vista:** `Productos/Create.vue`

**Campos:**
- Nombre* (required)
- Descripción
- Categoría* (required, select)
- Stock* (required, integer >= 0)
- Stock mínimo (para alertas)
- Precio unitario* (required, decimal)
- Imagen (opcional, max 2MB, jpg/png)

**Proceso:**
1. Formulario con validación en tiempo real
2. Si hay imagen → se sube a `storage/public/productos/`
3. POST a `ProductoController::store()`
4. **Bitácora registra:** Producto creado
5. Logs detallados en `storage/logs/laravel.log`

**Validaciones especiales:**
- Imagen max 2048KB
- Precio no puede ser negativo
- Stock no puede ser negativo

---

## 4.3 🪵 GESTIÓN DE MATERIALES

### Ver Materiales (`/materiales`)
**Permisos requeridos:** `materiales.ver`
**Vista:** `Materiales/Index.vue` (517 líneas - versión completa)

**Funcionalidades avanzadas:**
- ✅ Búsqueda por nombre/descripción
- ✅ Filtro por categoría
- ✅ **Filtro especial:** Stock bajo (stock_actual <= stock_minimo)
- ✅ Paginación
- ✅ Alertas visuales para stock crítico

### Crear Material (`/materiales/create`)
**Permisos requeridos:** `materiales.crear`
**Vista:** `Materiales/Create.vue`

**Campos:**
- Nombre* (required)
- Descripción
- Categoría* (required)
- Stock actual (integer)
- Stock mínimo (integer)
- Punto de reorden (integer)
- Precio (decimal)
- Unidad de medida (m, kg, unidades, etc.)
- Activo (checkbox)

**Proceso:**
1. Formulario con validación
2. POST a `MaterialController::store()`
3. **Bitácora registra:** Material creado
4. Puede usarse inmediatamente en compras

---

## 4.4 🏢 GESTIÓN DE PROVEEDORES (NUEVO)

### Ver Proveedores (`/proveedores`)
**Permisos requeridos:** `proveedores.ver`
**Vista:** `Proveedores/Index.vue`

**Funcionalidades:**
- ✅ Búsqueda por: nombre, RUC, email, teléfono
- ✅ Filtro por estado (activo/inactivo)
- ✅ Paginación
- ✅ Acciones: Ver | Editar | Eliminar

### Crear Proveedor (`/proveedores/create`)
**Permisos requeridos:** `proveedores.crear`
**Vista:** `Proveedores/Create.vue`

**Campos:**
- Nombre* (required)
- RUC/NIT* (required, único)
- Email
- Teléfono
- Dirección
- Contacto (persona de contacto)
- Activo (checkbox)

**Proceso:**
1. Validación de RUC único
2. POST a `ProveedorController::store()`
3. **Bitácora registra:** Proveedor creado
4. Puede usarse inmediatamente en compras

---

## 4.5 🏭 GESTIÓN DE ALMACENES (NUEVO)

### Ver Almacenes (`/almacenes`)
**Permisos requeridos:** `almacenes.ver`
**Vista:** `Almacenes/Index.vue`

**Funcionalidades:**
- ✅ Búsqueda por nombre, ubicación
- ✅ Filtro por estado
- ✅ Muestra capacidad en m²

### Crear Almacén (`/almacenes/create`)
**Permisos requeridos:** `almacenes.crear`
**Vista:** `Almacenes/Create.vue`

**Campos:**
- Nombre* (required)
- Ubicación* (required)
- Descripción
- Capacidad (m², decimal)
- Activo (checkbox)

**Proceso:**
1. POST a `AlmacenController::store()`
2. **Bitácora registra:** Almacén creado
3. Puede usarse para asignar materiales

---

## 4.6 🛒 GESTIÓN DE COMPRAS (NUEVO - FLUJO COMPLETO)

### Ver Compras (`/compras`)
**Permisos requeridos:** `compras.ver`
**Vista:** `Compras/Index.vue`

**Información mostrada:**
- Fecha de compra
- Proveedor
- Almacén destino
- Total
- Estado
- Acciones: Ver detalles

### Crear Compra (`/compras/create`) ⭐ IMPORTANTE
**Permisos requeridos:** `compras.crear`
**Vista:** `Compras/Create.vue`

**Flujo detallado:**

#### Paso 1: Datos Generales
- Seleccionar Proveedor* (select con búsqueda)
- Seleccionar Almacén destino*
- Fecha de compra* (default: hoy)
- Observaciones (opcional)

#### Paso 2: Agregar Detalles (Materiales)
- Botón "➕ Agregar Material"
- Por cada material:
  - Seleccionar Material* (select)
  - Cantidad* (integer > 0)
  - Precio unitario* (decimal)
  - Subtotal se calcula automáticamente

#### Paso 3: Cálculos Automáticos
```
Subtotal = Suma de (cantidad × precio_unitario) de cada detalle
IVA = Subtotal × 0.13 (si aplica)
Total = Subtotal + IVA
```

#### Paso 4: Confirmación
Al hacer clic en "Guardar Compra":

**Backend procesa (`CompraController::store()`):**
1. **Inicia transacción DB**
2. Valida datos de compra
3. Crea registro en tabla `compra`:
   ```php
   - proveedor_id
   - almacen_id
   - fecha_compra
   - subtotal
   - total
   - usuario_id (quien registró)
   ```
4. Por cada detalle:
   - Crea registro en `detalle_compra`
   - **Actualiza stock:** `Material::stock_actual += cantidad`
   - **Crea movimiento:** en `movimiento_inventario`:
     ```php
     - tipo: 'ENTRADA'
     - cantidad: X
     - motivo: 'COMPRA'
     - material_id: Y
     - fecha: now()
     ```
5. **Bitácora registra:** Compra creada con todos los detalles
6. **Commit transacción**
7. Redirige a `/compras` con mensaje de éxito

**Si hay error:**
- Rollback de toda la transacción
- No se actualiza stock
- No se crean movimientos
- Muestra error al usuario

---

## 4.7 💰 GESTIÓN DE VENTAS

### Ver Ventas (`/ventas`)
**Permisos requeridos:** `ventas.ver`
**Vista:** `Ventas/Index.vue`

**Información mostrada:**
- Fecha
- Cliente
- Total
- Estado (confirmada/pendiente)
- Tipo (contado/crédito)
- Acciones: Ver | Editar

### Crear Venta - CONTADO (`/ventas/contado`)
**Permisos requeridos:** `ventas.crear`

**Flujo:**
1. Seleccionar cliente
2. Agregar productos/servicios con cantidades
3. Calcular totales automáticamente
4. Seleccionar método de pago
5. Al confirmar:
   - Crea venta en estado `confirmada = true`
   - **Descuenta stock inmediatamente**
   - Crea pago con estado `PAGADO`
   - Crea movimientos de inventario tipo `SALIDA`
   - **Bitácora registra:** Venta contado completada

### Crear Venta - CRÉDITO (`/ventas/credito`)
**Permisos requeridos:** `ventas.crear`

**Flujo:**
1. Similar a contado, pero:
   - Venta se crea con `confirmada = false` (pendiente)
   - **Stock NO se descuenta aún**
   - Crea pagos con estado `PENDIENTE`
2. Opciones:
   - **Pago único:** 1 pago por el total
   - **Plan de pagos:** Usar `/pagos/plan` (ver siguiente sección)

---

## 4.8 💳 GESTIÓN DE PAGOS (MEJORADO CON PLAN DE PAGOS)

### Ver Pagos (`/pagos`)
**Permisos requeridos:** `pagos.ver`
**Vista:** `Pagos/Index.vue` (267 líneas - versión mejorada)

**Filtros disponibles:**
- Por venta
- Por estado (PENDIENTE, PAGADO, VENCIDO, CANCELADO)
- Por tipo (CONTADO, CREDITO, CUOTA)
- Por fecha de vencimiento

### Crear Plan de Pagos ⭐ NUEVO
**Ruta:** `POST /pagos/plan`
**Controller:** `PagoController::crearPlanPagos()`

**Proceso:**

#### Entrada:
```json
{
  "venta_id": 123,
  "numero_cuotas": 6,  // min: 2, max: 12
  "fecha_primera_cuota": "2025-01-15",
  "metodo_pago_id": 1  // opcional
}
```

#### Backend procesa:
1. Valida que la venta no tenga pagos existentes
2. Calcula monto por cuota:
   ```php
   $montoPorCuota = floor(($total / $numeroCuotas) * 100) / 100;
   $ultimaCuota = $total - ($montoPorCuota × ($numeroCuotas - 1));
   ```
3. Crea registros de pago:
   - Cuota 1: Vence en `fecha_primera_cuota`
   - Cuota 2: Vence en `fecha_primera_cuota + 1 mes`
   - ...
   - Cuota N: Vence en `fecha_primera_cuota + (N-1) meses`
4. Todos con:
   - `tipo = 'CUOTA'`
   - `estado = 'PENDIENTE'`
   - `numero_cuota = 1, 2, 3...`
5. **Bitácora registra:** Plan de pagos creado

#### Ejemplo:
```
Venta: $1000
Cuotas: 3
Resultado:
- Cuota 1: $333.33 (vence 15/01)
- Cuota 2: $333.33 (vence 15/02)
- Cuota 3: $333.34 (vence 15/03, ajustada)
Total: $1000.00 ✅
```

### Registrar Pago de una Cuota
**Ruta:** `POST /pagos/{id}/registrar`
**Vista:** `Pagos/Show.vue`

**Proceso:**
1. Usuario hace clic en "Registrar Pago" de una cuota
2. Backend marca pago como `PAGADO`
3. Asigna `fecha_pago = now()`
4. **Bitácora registra:** Pago registrado
5. Verifica si todas las cuotas están pagadas:
   - Si SÍ:
     - Marca venta como `confirmada = true`
     - **Descuenta stock** de productos
     - Crea movimientos de inventario
     - **Bitácora registra:** Venta confirmada por pago completo

### Integración con PagoFácil QR
**Flujo automático:**
1. Sistema genera QR con `PaymentGatewayService`
2. Cliente escanea y paga
3. PagoFácil envía callback a `/payment/callback`
4. `PaymentController::callback()` procesa:
   - Verifica estado `APROBADO`
   - Marca pago como `PAGADO`
   - Actualiza `fecha_confirmacion`
   - Guarda método de pago usado
   - **Bitácora registra:** Pago confirmado vía PagoFácil
5. Si es última cuota → Descuenta stock automáticamente

---

## 4.9 📊 REPORTES

### Reportes de Ventas (`/reportes/ventas`)
**Permisos requeridos:** `reportes.ver`
**Vista:** `Reportes/Ventas.vue`

**Métricas:**
- Total vendido por período
- Ventas por producto
- Ventas por cliente
- Tendencias
- Gráficos de barras/líneas

### Reportes de Compras (`/reportes/compras`) ⭐ NUEVO
**Permisos requeridos:** `reportes.ver`
**Vista:** `Reportes/Compras.vue`
**Controller:** `ReporteCompraController::index()`

**Métricas disponibles:**
- **Resumen:**
  - Total de compras en período
  - Número de compras
  - Promedio por compra
  - Total de materiales comprados
  
- **Por Proveedor:**
  - Compras agrupadas por proveedor
  - Gasto total por proveedor
  - Ranking de proveedores
  
- **Top 5 Materiales Más Comprados:**
  - Material
  - Total de veces comprado
  - Cantidad total
  - Monto total invertido

**Filtros:**
- Rango de fechas
- Proveedor específico
- Almacén

### Reportes de Inventario (`/reportes/inventario`)
**Permisos requeridos:** `reportes.ver`
**Vista:** `Reportes/Inventario.vue`

**Métricas:**
- Materiales con stock bajo (crítico)
- Movimientos recientes
- Valorización de inventario
- Productos más rotados

---

## 4.10 📜 BITÁCORA (AUDITORÍA) ⭐ NUEVO

### Ver Bitácora (`/bitacora`)
**Permisos requeridos:** `bitacora.ver` (solo admin)
**Vista:** `Bitacora/Index.vue`
**Controller:** `BitacoraController::index()`

**Información registrada:**
- ¿Quién? (usuario_id)
- ¿Qué? (tipo_accion: CREAR, ACTUALIZAR, ELIMINAR, etc.)
- ¿Cuándo? (timestamp)
- ¿Dónde? (tabla_afectada, registro_id)
- ¿Desde dónde? (dirección IP)
- ¿Qué cambió? (datos_anteriores vs datos_nuevos en JSONB)
- Descripción legible

**Filtros disponibles:**
- Por usuario
- Por tipo de acción
- Por tabla afectada
- Por rango de fechas

**Operaciones auditadas:**
- ✅ Usuarios: crear, editar, eliminar
- ✅ Productos: crear, editar, eliminar
- ✅ Materiales: crear, editar, eliminar
- ✅ Proveedores: crear, editar, eliminar
- ✅ Almacenes: crear, editar, eliminar
- ✅ Compras: crear
- ✅ Ventas: crear, confirmar
- ✅ Pagos: registrar, confirmar, plan de pagos
- ✅ Callbacks de PagoFácil

### Ver Detalle de Bitácora (`/bitacora/{id}`)
**Vista:** `Bitacora/Show.vue`

**Muestra:**
- Comparación lado a lado de:
  - Datos anteriores (JSON formateado)
  - Datos nuevos (JSON formateado)
- Campos que cambiaron resaltados
- Usuario que realizó la acción
- Fecha y hora exacta
- IP desde donde se realizó

---

## 5. 🔍 BÚSQUEDA GLOBAL

**Ruta:** `/buscar?q=texto`
**Vista:** `Busqueda/Resultados.vue`
**Controller:** `BusquedaController::index()`

**Búsqueda en:**
- Productos (nombre, descripción)
- Materiales (nombre, descripción)
- Servicios (nombre, descripción)
- Usuarios (nombre, apellido, email)
- Proveedores (nombre, RUC, email)
- Ventas (por cliente)

**Resultados agrupados por categoría**

---

## 6. 🛡️ SISTEMA DE PERMISOS

### Estructura de Permisos

Cada acción requiere un permiso específico:

| Módulo | Ver | Crear | Editar | Eliminar |
|--------|-----|-------|--------|----------|
| Usuarios | `usuarios.ver` | `usuarios.crear` | `usuarios.editar` | `usuarios.eliminar` |
| Productos | `productos.ver` | `productos.crear` | `productos.editar` | `productos.eliminar` |
| Materiales | `materiales.ver` | `materiales.crear` | `materiales.editar` | `materiales.eliminar` |
| Proveedores | `proveedores.ver` | `proveedores.crear` | `proveedores.editar` | `proveedores.eliminar` |
| Almacenes | `almacenes.ver` | `almacenes.crear` | `almacenes.editar` | `almacenes.eliminar` |
| Compras | `compras.ver` | `compras.crear` | - | - |
| Ventas | `ventas.ver` | `ventas.crear` | `ventas.editar` | - |
| Pagos | `pagos.ver` | `pagos.registrar` | - | - |
| Reportes | `reportes.ver` | - | - | - |
| Bitácora | `bitacora.ver` | - | - | - |

### Verificación de Permisos

En **Backend (Controllers):**
```php
if (!Auth::user()->tienePermiso('productos.ver')) {
    abort(403, 'No tiene permiso');
}
```

En **Frontend (Vistas):**
```vue
<Link v-if="$page.props.auth.user.permisos.includes('productos.crear')" 
      :href="route('productos.create')">
    Crear Producto
</Link>
```

---

## 7. ❌ RESTRICCIONES Y LIMITACIONES

### Lo que NO se puede hacer:

1. **Usuarios:**
   - ❌ Eliminar usuario con ventas asociadas
   - ❌ Cambiar propio rol si es único admin
   - ❌ Desactivarse a sí mismo

2. **Productos/Materiales:**
   - ❌ Eliminar si hay ventas/compras pendientes
   - ❌ Stock negativo (validado)
   - ❌ Precio negativo (validado)

3. **Proveedores:**
   - ❌ Eliminar si tiene compras asociadas
   - ❌ Duplicar RUC/NIT

4. **Compras:**
   - ❌ Modificar compra ya registrada (solo crear nuevas)
   - ❌ Cantidad negativa
   - ❌ Eliminar compra (auditabilidad)

5. **Ventas:**
   - ❌ Vender sin stock disponible
   - ❌ Eliminar venta confirmada
   - ❌ Modificar venta con pagos registrados

6. **Pagos:**
   - ❌ Crear plan de pagos si ya existen pagos
   - ❌ Modificar pago ya PAGADO
   - ❌ Eliminar pagos (auditabilidad)

7. **Bitácora:**
   - ❌ Editar registros (solo lectura)
   - ❌ Eliminar registros (inmutable)

---

## 8. 🔄 FLUJOS COMPLETOS DE NEGOCIO

### Flujo 1: Compra de Materiales → Stock

```
1. Usuario → /proveedores/create → Registra proveedor
2. Usuario → /almacenes/create → Registra almacén
3. Usuario → /compras/create
   ├─ Selecciona proveedor
   ├─ Selecciona almacén
   ├─ Agrega materiales con cantidades y precios
   ├─ Confirma compra
   └─ Sistema:
       ├─ Guarda compra en DB
       ├─ SUMA stock a materiales: Material.stock_actual += cantidad
       ├─ Crea movimientos tipo ENTRADA
       ├─ Registra en bitácora
       └─ Muestra confirmación
4. Usuario → /materiales → Ve stock actualizado ✅
5. Usuario → /reportes/compras → Ve estadísticas
```

### Flujo 2: Venta a Crédito → Plan de Pagos → Confirmación

```
1. Usuario → /ventas/credito
   ├─ Selecciona cliente
   ├─ Agrega productos
   ├─ Confirma venta
   └─ Sistema:
       ├─ Crea venta con confirmada=false
       ├─ Stock NO se descuenta aún
       └─ Crea 1 pago PENDIENTE

2. Usuario → /pagos/plan
   ├─ Selecciona venta
   ├─ Define: 6 cuotas, primera cuota 15/enero
   └─ Sistema:
       ├─ Crea 6 pagos tipo CUOTA
       ├─ Distribuye monto (última ajustada)
       ├─ Asigna fechas de vencimiento
       └─ Registra en bitácora

3. Cliente paga Cuota 1 (15/enero)
   ├─ Usuario → /pagos → Click "Registrar Pago"
   └─ Sistema:
       ├─ Marca cuota 1 como PAGADA
       ├─ Registra fecha_pago
       ├─ Stock aún NO se descuenta (faltan cuotas)
       └─ Bitácora: Pago registrado

4. ... Cliente paga Cuotas 2-5 (proceso similar)

5. Cliente paga Cuota 6 (última) (15/junio)
   ├─ Usuario → /pagos → Click "Registrar Pago"
   └─ Sistema:
       ├─ Marca cuota 6 como PAGADA
       ├─ Detecta: todas las cuotas PAGADAS ✅
       ├─ Marca venta.confirmada = true
       ├─ DESCUENTA stock: Producto.stock -= cantidad
       ├─ Crea movimientos tipo SALIDA
       ├─ Registra en bitácora: "Venta confirmada por pago completo"
       └─ Notifica al usuario

6. Usuario → /reportes/ventas → Ve venta confirmada
7. Usuario → /bitacora → Ve todo el historial del proceso
```

### Flujo 3: Venta Contado con PagoFácil QR

```
1. Usuario → /ventas/contado
   ├─ Agrega productos
   ├─ Total: Bs. 500
   ├─ Método pago: PagoFácil QR
   └─ Confirma venta

2. Sistema (PaymentGatewayService):
   ├─ Genera QR con PagoFácil API
   ├─ Crea pago con estado PENDIENTE
   ├─ Muestra QR al cliente
   └─ Espera confirmación

3. Cliente escanea QR y paga en app PagoFácil

4. PagoFácil → Callback POST /payment/callback
   {
     "VentaID": "12345",
     "Estado": "APROBADO",
     "Fecha": "2025-01-15",
     "MetodoPago": "QR_TIGO"
   }

5. Sistema (PaymentController::callback):
   ├─ Recibe notificación
   ├─ Valida estado APROBADO
   ├─ Busca pago por nro_pago
   ├─ Actualiza:
   │   ├─ estado = PAGADO
   │   ├─ fecha_confirmacion
   │   └─ metodo_pago_facil
   ├─ Confirma venta (confirmPayment)
   ├─ DESCUENTA stock inmediatamente
   ├─ Crea movimientos SALIDA
   ├─ Registra en bitácora
   └─ Responde a PagoFácil: {"status": 1}

6. Usuario → /pagos → Ve pago confirmado ✅
7. Usuario → /productos → Ve stock descontado ✅
```

---

## 9. 📱 FUNCIONALIDADES ADICIONALES

### 9.1 Alertas Automáticas
- 🔴 Stock bajo: Cuando `stock_actual <= stock_minimo`
- 🟡 Pagos próximos a vencer: 3 días antes
- 🔴 Pagos vencidos: `fecha_vencimiento < HOY`

### 9.2 Validaciones en Tiempo Real
- ✅ Email único al crear usuario
- ✅ RUC único al crear proveedor
- ✅ Stock disponible al crear venta
- ✅ Suma de cuotas = Total venta (plan de pagos)

### 9.3 Logs Detallados
Archivo: `storage/logs/laravel.log`
- Todas las operaciones CRUD
- Errores de validación
- Callbacks de PagoFácil
- Transacciones DB (inicio, commit, rollback)

### 9.4 Manejo de Errores
- Frontend muestra errores amigables
- Backend retorna JSON con:
  - `message`: Descripción del error
  - `errors`: Errores de validación por campo
- Transacciones DB con rollback automático

---

## 10. 🚀 TECNOLOGÍAS Y ARQUITECTURA

### Backend
- **Laravel 12**: Framework PHP
- **PostgreSQL**: Base de datos con JSONB
- **Sanctum**: Autenticación API
- **Inertia.js**: Bridge entre Laravel y Vue

### Frontend
- **Vue 3**: Framework JavaScript
- **Tailwind CSS**: Estilos utility-first
- **Inertia.js**: SPA sin API

### Integraciones
- **PagoFácil QR**: Pagos electrónicos Bolivia

### Patrones Implementados
- **BaseController**: API/Web dual response
- **Bitácora**: Auditoría completa
- **Soft Deletes**: No eliminación física
- **Transacciones DB**: Integridad de datos
- **JSONB**: Datos flexibles (bitácora)

---

## 11. 📊 RESUMEN ESTADÍSTICO

**Módulos Totales:** 15
- 👥 Usuarios y Roles
- 📦 Productos
- 🔧 Servicios
- 🪵 Materiales
- 💰 Ventas
- 💳 Pagos (con plan de pagos)
- 📦 Inventarios
- 🏢 Proveedores ⭐
- 🏭 Almacenes ⭐
- 🛒 Compras ⭐
- 📊 Reportes (Ventas, Inventario, Compras ⭐)
- 📜 Bitácora ⭐
- 🔍 Búsqueda Global

**Tablas en Base de Datos:** 25+
**Vistas Vue.js:** 48
**Controladores:** 18
**Migraciones Ejecutadas:** 10 nuevas + originales

**Funcionalidades Críticas:**
- ✅ Sistema de permisos granular
- ✅ Auditoría completa (bitácora)
- ✅ Plan de pagos automático
- ✅ Integración PagoFácil
- ✅ Control de stock en tiempo real
- ✅ Reportes avanzados
- ✅ Búsqueda global

---

## 📝 NOTAS FINALES

### Para Desarrolladores:
- Todos los cambios están auditados en bitácora
- Usar transacciones DB para operaciones críticas
- Validar permisos en controller Y vista
- Logs detallados para debugging

### Para Usuarios Finales:
- Interfaz intuitiva con Tailwind CSS
- Búsqueda en tiempo real
- Alertas visuales para acciones importantes
- Confirmaciones antes de eliminar

### Próximos Pasos Sugeridos:
1. Crear vistas Vue para Almacenes (si faltan)
2. Agregar notificaciones push
3. Dashboard con gráficos Chart.js
4. Exportar reportes a PDF/Excel
5. Módulo de cotizaciones
6. Gestión de categorías avanzada

---

**Última actualización:** 18 de diciembre de 2025
**Versión del sistema:** 2.0 (con módulos de compras y bitácora)
