# API Documentation - Carpintería Jorge

## Información General

**Base URL**: `http://localhost:8000/api`  
**Versión**: 1.0  
**Formato**: JSON  
**Autenticación**: Bearer Token (Sanctum)

---

## Autenticación

La mayoría de los endpoints requieren autenticación mediante Bearer Token. Para obtener el token, use el endpoint de login.

**Header requerido**:
```
Authorization: Bearer {token}
```

---

## Endpoints de Autenticación

### 1. Login

Autentica un usuario y retorna un token de acceso.

**Endpoint**: `POST /api/login`

**Autenticación**: No requerida

**Request Body**:
```json
{
  "email": "propietario@carpinteria.com",
  "password": "password123"
}
```

**Response 200 OK**:
```json
{
  "user": {
    "id": 1,
    "nombre": "Juan",
    "apellido": "Perez",
    "email": "propietario@carpinteria.com",
    "rol": {
      "id": 1,
      "nombre": "PROPIETARIO"
    }
  },
  "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
}
```

**Response 401 Unauthorized**:
```json
{
  "message": "Credenciales inválidas"
}
```

**Response 403 Forbidden** (Usuario inactivo/bloqueado):
```json
{
  "message": "Usuario inactivo"
}
```

---

### 2. Register

Registra un nuevo usuario en el sistema.

**Endpoint**: `POST /api/register`

**Autenticación**: No requerida

**Request Body**:
```json
{
  "nombre": "Carlos",
  "apellido": "Ruiz",
  "email": "nuevo@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "telefono": "444444444",
  "rol_id": 5
}
```

**Response 201 Created**:
```json
{
  "user": {
    "id": 6,
    "nombre": "Carlos",
    "apellido": "Ruiz",
    "email": "nuevo@example.com",
    "rol": {
      "id": 5,
      "nombre": "CLIENTE"
    }
  },
  "token": "2|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
}
```

---

### 3. Obtener Usuario Actual

Obtiene la información del usuario autenticado.

**Endpoint**: `GET /api/user`

**Autenticación**: Requerida

**Response 200 OK**:
```json
{
  "id": 1,
  "nombre": "Juan",
  "apellido": "Perez",
  "email": "propietario@carpinteria.com",
  "rol": {
    "id": 1,
    "nombre": "PROPIETARIO"
  }
}
```

---

### 4. Logout

Cierra la sesión del usuario actual.

**Endpoint**: `POST /api/logout`

**Autenticación**: Requerida

**Response 200 OK**:
```json
{
  "message": "Sesión cerrada exitosamente"
}
```

---

## Gestión de Productos

### 1. Listar Productos

Obtiene la lista de todos los productos.

**Endpoint**: `GET /api/productos`

**Autenticación**: Requerida

**Permisos**: No requeridos (público para usuarios autenticados)

**Response 200 OK**:
```json
[
  {
    "id": 1,
    "nombre": "Mesa de Roble",
    "descripcion": "Mesa de comedor de roble macizo",
    "stock": 5,
    "precio_unitario": 1500.00,
    "categoria": {
      "id": 1,
      "nombre": "Muebles"
    }
  }
]
```

---

### 2. Crear Producto

Crea un nuevo producto.

**Endpoint**: `POST /api/productos`

**Autenticación**: Requerida

**Permisos**: `productos.crear`

**Request Body**:
```json
{
  "nombre": "Silla de Comedor",
  "descripcion": "Silla ergonómica de madera",
  "categoria_id": 1,
  "stock": 10,
  "precio_unitario": 250.00
}
```

**Response 201 Created**:
```json
{
  "id": 2,
  "nombre": "Silla de Comedor",
  "descripcion": "Silla ergonómica de madera",
  "stock": 10,
  "precio_unitario": 250.00,
  "categoria": {
    "id": 1,
    "nombre": "Muebles"
  }
}
```

**Response 403 Forbidden**:
```json
{
  "message": "No tiene permiso para crear productos"
}
```

---

### 3. Obtener Producto

Obtiene los detalles de un producto específico.

**Endpoint**: `GET /api/productos/{id}`

**Autenticación**: Requerida

**Parámetros**:
- `id` (path, required): ID del producto

**Response 200 OK**:
```json
{
  "id": 1,
  "nombre": "Mesa de Roble",
  "descripcion": "Mesa de comedor de roble macizo",
  "stock": 5,
  "precio_unitario": 1500.00,
  "categoria": {
    "id": 1,
    "nombre": "Muebles"
  }
}
```

---

### 4. Actualizar Producto

Actualiza un producto existente.

**Endpoint**: `PUT /api/productos/{id}`  
**Endpoint**: `PATCH /api/productos/{id}`

**Autenticación**: Requerida

**Permisos**: `productos.editar`

**Request Body**:
```json
{
  "nombre": "Mesa de Roble Premium",
  "stock": 8,
  "precio_unitario": 1800.00
}
```

**Response 200 OK**:
```json
{
  "id": 1,
  "nombre": "Mesa de Roble Premium",
  "stock": 8,
  "precio_unitario": 1800.00,
  "categoria": {
    "id": 1,
    "nombre": "Muebles"
  }
}
```

---

### 5. Eliminar Producto

Elimina un producto.

**Endpoint**: `DELETE /api/productos/{id}`

**Autenticación**: Requerida

**Permisos**: `productos.eliminar`

**Response 204 No Content**

**Response 403 Forbidden**:
```json
{
  "message": "No tiene permiso para eliminar productos"
}
```

---

## Gestión de Servicios

### 1. Listar Servicios

Obtiene la lista de todos los servicios activos.

**Endpoint**: `GET /api/servicios`

**Autenticación**: Requerida

**Response 200 OK**:
```json
[
  {
    "id": 1,
    "nombre": "Instalación de Muebles",
    "descripcion": "Servicio de instalación profesional",
    "precio_base": 150.00,
    "tiempo_estimado": 2,
    "activo": true,
    "categoria": {
      "id": 2,
      "nombre": "Servicios"
    }
  }
]
```

---

### 2. Crear Servicio

Crea un nuevo servicio.

**Endpoint**: `POST /api/servicios`

**Autenticación**: Requerida

**Permisos**: `servicios.crear`

**Request Body**:
```json
{
  "nombre": "Reparación de Muebles",
  "descripcion": "Servicio de reparación y restauración",
  "precio_base": 200.00,
  "tiempo_estimado": 4,
  "categoria_id": 2,
  "activo": true
}
```

**Response 201 Created**:
```json
{
  "id": 2,
  "nombre": "Reparación de Muebles",
  "precio_base": 200.00,
  "categoria": {
    "id": 2,
    "nombre": "Servicios"
  }
}
```

---

### 3. Obtener Servicio

**Endpoint**: `GET /api/servicios/{id}`

**Autenticación**: Requerida

---

### 4. Actualizar Servicio

**Endpoint**: `PUT /api/servicios/{id}`

**Autenticación**: Requerida

**Permisos**: `servicios.editar`

---

### 5. Eliminar/Desactivar Servicio

**Endpoint**: `DELETE /api/servicios/{id}`

**Autenticación**: Requerida

**Permisos**: `servicios.eliminar`

**Nota**: No elimina el servicio, solo lo desactiva.

---

## Gestión de Materiales/Insumos

### 1. Listar Materiales

Obtiene la lista de todos los materiales.

**Endpoint**: `GET /api/materiales`

**Autenticación**: Requerida

**Permisos**: `materiales.ver`

**Response 200 OK**:
```json
[
  {
    "id": 1,
    "nombre": "Madera de Roble",
    "descripcion": "Tablas de roble de primera calidad",
    "stock_actual": 100,
    "stock_minimo": 20,
    "precio": 50.00,
    "unidad_medida": "m²",
    "sector": {
      "id": 1,
      "nombre": "Almacén Principal"
    },
    "categoria": {
      "id": 1,
      "nombre": "Materia Prima"
    }
  }
]
```

---

### 2. Crear Material

**Endpoint**: `POST /api/materiales`

**Autenticación**: Requerida

**Permisos**: `materiales.crear`

**Request Body**:
```json
{
  "nombre": "Clavos de Acero",
  "descripcion": "Clavos de acero inoxidable",
  "stock_actual": 500,
  "stock_minimo": 100,
  "punto_reorden": 150,
  "precio": 2.50,
  "unidad_medida": "kg",
  "sector_id": 1,
  "categoria_id": 1,
  "activo": true
}
```

---

## Gestión de Pedidos/Ventas

### 1. Listar Pedidos

Obtiene la lista de todos los pedidos.

**Endpoint**: `GET /api/pedidos`

**Autenticación**: Requerida

**Permisos**: `pedidos.ver`

**Response 200 OK**:
```json
[
  {
    "id": 1,
    "fecha": "2025-11-22T10:00:00.000000Z",
    "descripcion": "Pedido de cliente",
    "importe_total": 2000.00,
    "importe_total_desc": 1900.00,
    "estado": true,
    "usuario": {
      "id": 5,
      "nombre": "Carlos",
      "email": "cliente@example.com"
    },
    "metodoPago": {
      "id": 1,
      "nombre": "Efectivo"
    },
    "detalles": [
      {
        "id": 1,
        "producto_id": 1,
        "cantidad": 2,
        "precio_unitario": 1000.00,
        "importe_total": 2000.00
      }
    ]
  }
]
```

---

### 2. Crear Venta al Contado

Crea un pedido con pago inmediato y genera movimientos de inventario automáticamente.

**Endpoint**: `POST /api/pedidos/storeContado`

**Autenticación**: Requerida

**Permisos**: `pedidos.crear`

**Request Body**:
```json
{
  "fecha": "2025-11-22",
  "descripcion": "Venta al contado",
  "usuario_id": 5,
  "metodo_pago_id": 1,
  "detalles": [
    {
      "producto_id": 1,
      "cantidad": 2,
      "precio_unitario": 1000.00,
      "importe_total_desc": 1900.00
    },
    {
      "servicio_id": 1,
      "cantidad": 1,
      "precio_unitario": 150.00
    }
  ]
}
```

**Response 201 Created**:
```json
{
  "id": 1,
  "fecha": "2025-11-22T10:00:00.000000Z",
  "estado": true,
  "importe_total": 2150.00,
  "importe_total_desc": 2050.00,
  "detalles": [
    {
      "id": 1,
      "producto_id": 1,
      "cantidad": 2
    }
  ],
  "pagos": [
    {
      "id": 1,
      "monto": 2050.00,
      "estado": "PAGADO",
      "tipo": "CONTADO"
    }
  ]
}
```

**Response 400 Bad Request** (Stock insuficiente):
```json
{
  "error": "Stock insuficiente para producto Mesa de Roble"
}
```

---

### 3. Crear Venta a Crédito

Crea un pedido con pagos a plazos (cuotas).

**Endpoint**: `POST /api/pedidos/storeCredito`

**Autenticación**: Requerida

**Permisos**: `pedidos.crear`

**Request Body**:
```json
{
  "fecha": "2025-11-22",
  "descripcion": "Venta a crédito",
  "usuario_id": 5,
  "metodo_pago_id": 2,
  "numero_cuotas": 3,
  "fecha_primera_cuota": "2025-12-01",
  "detalles": [
    {
      "producto_id": 1,
      "cantidad": 1,
      "precio_unitario": 1000.00,
      "importe_total_desc": 950.00
    }
  ]
}
```

**Response 201 Created**:
```json
{
  "id": 2,
  "fecha": "2025-11-22T10:00:00.000000Z",
  "estado": false,
  "importe_total": 1000.00,
  "importe_total_desc": 950.00,
  "pagos": [
    {
      "id": 2,
      "monto": 316.67,
      "fecha_vencimiento": "2025-12-01",
      "estado": "PENDIENTE",
      "numero_cuota": 1
    },
    {
      "id": 3,
      "monto": 316.67,
      "fecha_vencimiento": "2026-01-01",
      "estado": "PENDIENTE",
      "numero_cuota": 2
    },
    {
      "id": 4,
      "monto": 316.66,
      "fecha_vencimiento": "2026-02-01",
      "estado": "PENDIENTE",
      "numero_cuota": 3
    }
  ]
}
```

---

### 4. Confirmar Pedido a Crédito

Confirma un pedido a crédito y genera movimientos de inventario.

**Endpoint**: `POST /api/pedidos/{pedido}/confirmar-credito`

**Autenticación**: Requerida

**Permisos**: `pedidos.aprobar`

**Parámetros**:
- `pedido` (path, required): ID del pedido

**Response 200 OK**:
```json
{
  "id": 2,
  "estado": true,
  "detalles": [
    {
      "id": 2,
      "producto_id": 1,
      "cantidad": 1,
      "estado": true
    }
  ]
}
```

**Response 400 Bad Request**:
```json
{
  "error": "El pedido ya está confirmado"
}
```

---

### 5. Salida de Producto

Crea un pedido y genera salida de inventario inmediatamente.

**Endpoint**: `POST /api/pedidos/salida-producto`

**Autenticación**: Requerida

**Permisos**: `pedidos.crear`

**Request Body**:
```json
{
  "fecha": "2025-11-22",
  "descripcion": "Salida de producto",
  "usuario_id": 5,
  "metodo_pago_id": 1,
  "detalles": [
    {
      "producto_id": 1,
      "cantidad": 1,
      "precio_unitario": 1000.00
    }
  ]
}
```

---

### 6. Obtener Pedido

**Endpoint**: `GET /api/pedidos/{id}`

**Autenticación**: Requerida

**Permisos**: `pedidos.ver`

---

### 7. Actualizar Pedido

**Endpoint**: `PUT /api/pedidos/{id}`

**Autenticación**: Requerida

**Permisos**: `pedidos.editar`

---

### 8. Eliminar Pedido

**Endpoint**: `DELETE /api/pedidos/{id}`

**Autenticación**: Requerida

**Permisos**: `pedidos.eliminar`

**Nota**: Solo se pueden eliminar pedidos no confirmados.

---

## Gestión de Compras

### 1. Listar Compras

Obtiene la lista de todas las compras.

**Endpoint**: `GET /api/compras`

**Autenticación**: Requerida

**Permisos**: `compras.ver`

**Response 200 OK**:
```json
[
  {
    "id": 1,
    "fecha": "2025-11-22T10:00:00.000000Z",
    "estado": "COMPLETADA",
    "importe_total": 5000.00,
    "importe_descuento": 200.00,
    "proveedor": {
      "id": 1,
      "nombre": "Proveedor ABC"
    },
    "detalles": [
      {
        "id": 1,
        "material_id": 1,
        "cantidad": 100,
        "precio": 50.00,
        "importe": 5000.00
      }
    ]
  }
]
```

---

### 2. Crear Compra

Crea una nueva compra de materiales.

**Endpoint**: `POST /api/compras`

**Autenticación**: Requerida

**Permisos**: `compras.crear`

**Request Body**:
```json
{
  "fecha": "2025-11-22",
  "estado": "PENDIENTE",
  "proveedor_id": 1,
  "importe_descuento": 100.00,
  "detalles": [
    {
      "material_id": 1,
      "cantidad": 50,
      "precio": 50.00,
      "importe_desc": 2400.00
    }
  ]
}
```

**Nota**: Si el estado es "COMPLETADA", se generan movimientos de inventario automáticamente.

**Response 201 Created**:
```json
{
  "id": 1,
  "fecha": "2025-11-22T10:00:00.000000Z",
  "estado": "PENDIENTE",
  "importe_total": 2400.00,
  "proveedor": {
    "id": 1,
    "nombre": "Proveedor ABC"
  },
  "detalles": [
    {
      "id": 1,
      "material_id": 1,
      "cantidad": 50,
      "precio": 50.00
    }
  ]
}
```

---

### 3. Confirmar Compra

Confirma una compra y genera movimientos de inventario INGRESO automáticamente.

**Endpoint**: `POST /api/compras/{compra}/confirmar`

**Autenticación**: Requerida

**Permisos**: `compras.editar`

**Parámetros**:
- `compra` (path, required): ID de la compra

**Response 200 OK**:
```json
{
  "id": 1,
  "estado": "COMPLETADA",
  "detalles": [
    {
      "id": 1,
      "material_id": 1,
      "cantidad": 50,
      "estado": "RECIBIDO"
    }
  ]
}
```

**Response 400 Bad Request**:
```json
{
  "error": "La compra ya está completada"
}
```

---

### 4. Obtener Compra

**Endpoint**: `GET /api/compras/{id}`

**Autenticación**: Requerida

**Permisos**: `compras.ver`

---

### 5. Actualizar Compra

**Endpoint**: `PUT /api/compras/{id}`

**Autenticación**: Requerida

**Permisos**: `compras.editar`

**Nota**: Si se cambia el estado a "COMPLETADA", se generan movimientos automáticamente.

---

### 6. Eliminar Compra

**Endpoint**: `DELETE /api/compras/{id}`

**Autenticación**: Requerida

**Permisos**: `compras.eliminar`

**Nota**: Solo se pueden eliminar compras no completadas.

---

## Gestión de Pagos

### 1. Listar Pagos

Obtiene la lista de pagos con filtros opcionales.

**Endpoint**: `GET /api/pagos`

**Autenticación**: Requerida

**Permisos**: `pagos.ver`

**Query Parameters**:
- `pedido_id` (optional): Filtrar por pedido
- `estado` (optional): Filtrar por estado (PENDIENTE, PAGADO, VENCIDO, CANCELADO)
- `tipo` (optional): Filtrar por tipo (CONTADO, CREDITO, CUOTA)

**Response 200 OK**:
```json
{
  "data": [
    {
      "id": 1,
      "monto": 2050.00,
      "fecha_pago": "2025-11-22T10:00:00.000000Z",
      "fecha_vencimiento": null,
      "estado": "PAGADO",
      "tipo": "CONTADO",
      "pedido": {
        "id": 1,
        "importe_total_desc": 2050.00
      },
      "metodoPago": {
        "id": 1,
        "nombre": "Efectivo"
      }
    }
  ],
  "current_page": 1,
  "per_page": 20
}
```

---

### 2. Crear Pago

Crea un nuevo registro de pago.

**Endpoint**: `POST /api/pagos`

**Autenticación**: Requerida

**Permisos**: `pagos.registrar`

**Request Body**:
```json
{
  "monto": 500.00,
  "fecha_pago": "2025-11-22",
  "fecha_vencimiento": "2025-12-01",
  "estado": "PENDIENTE",
  "tipo": "CUOTA",
  "numero_cuota": 1,
  "observaciones": "Primera cuota",
  "pedido_id": 2,
  "metodo_pago_id": 2
}
```

**Response 201 Created**:
```json
{
  "id": 5,
  "monto": 500.00,
  "estado": "PENDIENTE",
  "tipo": "CUOTA",
  "pedido": {
    "id": 2
  }
}
```

---

### 3. Registrar Pago

Marca un pago como PAGADO. Si todas las cuotas están pagadas, confirma el pedido y genera movimientos de inventario.

**Endpoint**: `POST /api/pagos/{pago}/registrar`

**Autenticación**: Requerida

**Permisos**: `pagos.registrar`

**Parámetros**:
- `pago` (path, required): ID del pago

**Response 200 OK**:
```json
{
  "id": 5,
  "estado": "PAGADO",
  "fecha_pago": "2025-11-22T10:00:00.000000Z",
  "pedido": {
    "id": 2,
    "estado": true
  }
}
```

**Response 400 Bad Request**:
```json
{
  "error": "El pago ya está registrado"
}
```

---

### 4. Obtener Pago

**Endpoint**: `GET /api/pagos/{id}`

**Autenticación**: Requerida

**Permisos**: `pagos.ver`

---

## Gestión de Inventario

### 1. Listar Movimientos de Inventario

Obtiene la lista de movimientos de inventario con filtros.

**Endpoint**: `GET /api/movimientos-inventario`

**Autenticación**: Requerida

**Permisos**: `inventario.ver`

**Query Parameters**:
- `tipo` (optional): INGRESO o SALIDA
- `material_id` (optional): Filtrar por material
- `producto_id` (optional): Filtrar por producto
- `fecha_desde` (optional): Fecha desde
- `fecha_hasta` (optional): Fecha hasta

**Response 200 OK**:
```json
{
  "data": [
    {
      "id": 1,
      "tipo": "INGRESO",
      "cantidad": 50,
      "fecha": "2025-11-22T10:00:00.000000Z",
      "motivo": "Compra de materiales",
      "material": {
        "id": 1,
        "nombre": "Madera de Roble"
      },
      "compra": {
        "id": 1
      },
      "usuario": {
        "id": 1,
        "nombre": "Juan"
      }
    }
  ],
  "current_page": 1
}
```

---

### 2. Crear Movimiento de Inventario

Crea un movimiento manual de inventario (ingreso o salida).

**Endpoint**: `POST /api/movimientos-inventario`

**Autenticación**: Requerida

**Permisos**: 
- `inventario.ingreso` (para tipo INGRESO)
- `inventario.salida` (para tipo SALIDA)

**Request Body**:
```json
{
  "tipo": "INGRESO",
  "cantidad": 20,
  "motivo": "Ajuste de inventario",
  "observaciones": "Corrección de stock",
  "material_id": 1,
  "compra_id": null,
  "pedido_id": null
}
```

**Response 201 Created**:
```json
{
  "id": 2,
  "tipo": "INGRESO",
  "cantidad": 20,
  "fecha": "2025-11-22T10:00:00.000000Z",
  "material": {
    "id": 1,
    "nombre": "Madera de Roble",
    "stock_actual": 120
  }
}
```

**Response 400 Bad Request** (Stock insuficiente):
```json
{
  "error": "Stock insuficiente"
}
```

**Nota**: El stock se actualiza automáticamente al crear el movimiento.

---

### 3. Obtener Movimiento

**Endpoint**: `GET /api/movimientos-inventario/{id}`

**Autenticación**: Requerida

**Permisos**: `inventario.ver`

---

## Gestión de Usuarios

### 1. Listar Usuarios

**Endpoint**: `GET /api/usuarios`

**Autenticación**: Requerida

**Permisos**: `usuarios.ver`

---

### 2. Crear Usuario

**Endpoint**: `POST /api/usuarios`

**Autenticación**: Requerida

**Permisos**: `usuarios.crear`

**Request Body**:
```json
{
  "nombre": "Pedro",
  "apellido": "Gomez",
  "email": "nuevo@example.com",
  "password": "password123",
  "telefono": "222222222",
  "rol_id": 3,
  "estado": true,
  "disponibilidad": true
}
```

---

### 3. Obtener Usuario

**Endpoint**: `GET /api/usuarios/{id}`

**Autenticación**: Requerida

**Permisos**: `usuarios.ver`

---

### 4. Actualizar Usuario

**Endpoint**: `PUT /api/usuarios/{id}`

**Autenticación**: Requerida

**Permisos**: `usuarios.editar`

---

### 5. Eliminar Usuario

**Endpoint**: `DELETE /api/usuarios/{id}`

**Autenticación**: Requerida

**Permisos**: `usuarios.eliminar`

**Nota**: No se puede auto-eliminar.

---

## Otros Endpoints

### Categorías

- `GET /api/categorias` - Listar categorías
- `POST /api/categorias` - Crear categoría
- `GET /api/categorias/{id}` - Obtener categoría
- `PUT /api/categorias/{id}` - Actualizar categoría
- `DELETE /api/categorias/{id}` - Eliminar categoría

### Proveedores

- `GET /api/proveedores` - Listar proveedores
- `POST /api/proveedores` - Crear proveedor
- `GET /api/proveedores/{id}` - Obtener proveedor
- `PUT /api/proveedores/{id}` - Actualizar proveedor
- `DELETE /api/proveedores/{id}` - Eliminar proveedor

### Métodos de Pago

- `GET /api/metodos-pago` - Listar métodos de pago
- `POST /api/metodos-pago` - Crear método de pago
- `GET /api/metodos-pago/{id}` - Obtener método de pago
- `PUT /api/metodos-pago/{id}` - Actualizar método de pago
- `DELETE /api/metodos-pago/{id}` - Eliminar método de pago

### Almacenes

- `GET /api/almacenes` - Listar almacenes
- `POST /api/almacenes` - Crear almacén
- `GET /api/almacenes/{id}` - Obtener almacén
- `PUT /api/almacenes/{id}` - Actualizar almacén
- `DELETE /api/almacenes/{id}` - Eliminar almacén

### Sectores

- `GET /api/sectores` - Listar sectores
- `POST /api/sectores` - Crear sector
- `GET /api/sectores/{id}` - Obtener sector
- `PUT /api/sectores/{id}` - Actualizar sector
- `DELETE /api/sectores/{id}` - Eliminar sector

### Bitácora

- `GET /api/bitacoras` - Listar registros de bitácora
- `GET /api/bitacoras/{id}` - Obtener registro de bitácora

### Roles y Permisos

- `GET /api/roles` - Listar roles
- `POST /api/roles` - Crear rol
- `GET /api/permisos` - Listar permisos
- `GET /api/rol-permisos` - Listar asignaciones rol-permiso
- `POST /api/rol-permisos` - Asignar permiso a rol

---

## Códigos de Estado HTTP

| Código | Descripción |
|--------|-------------|
| 200 | OK - Solicitud exitosa |
| 201 | Created - Recurso creado exitosamente |
| 204 | No Content - Operación exitosa sin contenido |
| 400 | Bad Request - Error en la solicitud |
| 401 | Unauthorized - No autenticado |
| 403 | Forbidden - Sin permisos |
| 404 | Not Found - Recurso no encontrado |
| 422 | Unprocessable Entity - Error de validación |
| 500 | Internal Server Error - Error del servidor |

---

## Errores Comunes

### Error de Autenticación
```json
{
  "message": "No autenticado"
}
```
**Solución**: Incluir token Bearer en el header Authorization.

### Error de Permisos
```json
{
  "message": "No tiene permiso para realizar esta acción"
}
```
**Solución**: Verificar que el usuario tenga el permiso necesario asignado a su rol.

### Error de Validación
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```
**Solución**: Revisar los campos requeridos y sus formatos.

### Error de Stock Insuficiente
```json
{
  "error": "Stock insuficiente para producto Mesa de Roble"
}
```
**Solución**: Verificar stock disponible antes de crear pedido.

---

## Notas Importantes

1. **Autenticación**: Todos los endpoints (excepto login y register) requieren autenticación Bearer Token.

2. **Permisos**: Muchos endpoints requieren permisos específicos. Verificar que el usuario tenga el permiso necesario.

3. **Movimientos Automáticos**: 
   - Las compras confirmadas generan movimientos INGRESO automáticamente
   - Las ventas al contado generan movimientos SALIDA automáticamente
   - Los pedidos a crédito generan movimientos cuando se confirman o cuando todas las cuotas están pagadas

4. **Stock**: El stock se actualiza automáticamente cuando se crean movimientos de inventario.

5. **Bitácora**: Todas las operaciones CRUD se registran automáticamente en la bitácora.

6. **Fechas**: Las fechas deben estar en formato ISO 8601 (YYYY-MM-DD o YYYY-MM-DDTHH:mm:ss).

7. **Paginación**: Algunos endpoints retornan resultados paginados. Usar parámetros `page` y `per_page` si están disponibles.

---

## Ejemplos de Uso

### Ejemplo Completo: Venta al Contado

1. **Login**:
```bash
POST /api/login
{
  "email": "secretaria@carpinteria.com",
  "password": "password123"
}
```

2. **Crear Venta al Contado**:
```bash
POST /api/pedidos/storeContado
Authorization: Bearer {token}
{
  "usuario_id": 5,
  "metodo_pago_id": 1,
  "detalles": [
    {
      "producto_id": 1,
      "cantidad": 2,
      "precio_unitario": 1000.00
    }
  ]
}
```

3. **Resultado**:
- Pedido creado con estado completado
- Pago creado con estado PAGADO
- Movimiento SALIDA generado automáticamente
- Stock actualizado automáticamente
- Bitácora registrada

---

**Última actualización**: Noviembre 2025  
**Versión de API**: 1.0

