# Módulos Implementados - Sistema de Carpintería

Este documento describe todos los módulos implementados en el frontend con Vue.js e Inertia.js, así como las instrucciones de instalación y ejecución.

## 📋 Tabla de Contenidos

1. [Módulos Implementados](#módulos-implementados)
2. [Instalación](#instalación)
3. [Ejecución](#ejecución)
4. [Estructura del Proyecto](#estructura-del-proyecto)
5. [Rutas Disponibles](#rutas-disponibles)

---

## 🎯 Módulos Implementados

### 1. **Productos** ✅
- **Rutas**: `/productos`
- **Funcionalidades**:
  - Listado con filtros y búsqueda
  - Crear, editar, ver y eliminar productos
  - Gestión de imágenes
  - Control de stock
- **Permisos**: `productos.ver`, `productos.crear`, `productos.editar`, `productos.eliminar`

### 2. **Servicios** ✅
- **Rutas**: `/servicios`
- **Funcionalidades**:
  - Listado con filtros y búsqueda
  - CRUD completo
  - Gestión de precios y tiempos estimados
- **Permisos**: `servicios.ver`, `servicios.crear`, `servicios.editar`, `servicios.eliminar`

### 3. **Materiales** ✅
- **Rutas**: `/materiales`
- **Funcionalidades**:
  - Listado con filtros
  - CRUD completo
  - Gestión de stock y precios
  - Asignación de categorías y sectores
- **Permisos**: `materiales.ver`, `materiales.crear`, `materiales.editar`, `materiales.eliminar`

### 4. **Pedidos (Ventas)** ✅
- **Rutas**: `/pedidos`
- **Funcionalidades**:
  - Listado de pedidos
  - Crear pedidos al contado
  - Crear pedidos a crédito
  - Ver detalle de pedidos
  - Gestión automática de stock
- **Permisos**: `pedidos.ver`, `pedidos.crear`, `pedidos.editar`

### 5. **Compras** ✅
- **Rutas**: `/compras`
- **Funcionalidades**:
  - Listado de compras
  - Crear compras a proveedores
  - Confirmar compras
  - Ver detalle de compras
  - Actualización automática de stock
- **Permisos**: `compras.ver`, `compras.crear`, `compras.confirmar`

### 6. **Usuarios** ✅
- **Rutas**: `/usuarios`
- **Funcionalidades**:
  - Listado con filtros avanzados
  - CRUD completo
  - Gestión de estados de cuenta
  - Asignación de roles
  - Protección contra auto-eliminación
- **Permisos**: `usuarios.ver`, `usuarios.crear`, `usuarios.editar`, `usuarios.eliminar`

### 7. **Roles y Permisos** ✅
- **Rutas**: `/roles`
- **Funcionalidades**:
  - Listado de roles con permisos
  - Edición de permisos por rol
  - Agrupación de permisos por módulo
  - Selección masiva de permisos
- **Permisos**: `roles.ver`, `roles.editar`

### 8. **Inventario** ✅
- **Rutas**: `/inventario`, `/inventario/stock`
- **Funcionalidades**:
  - Listado de movimientos de inventario
  - Vista de stock actual (materiales y productos)
  - Crear movimientos manuales (ingresos/salidas)
  - Filtros avanzados
  - Alertas de stock bajo
- **Permisos**: `inventario.ver`, `inventario.ingreso`, `inventario.salida`

### 9. **Reportes** ✅
- **Rutas**: `/reportes`, `/reportes/ventas`, `/reportes/compras`, `/reportes/inventario`
- **Funcionalidades**:
  - Reporte de ventas con estadísticas
  - Reporte de compras
  - Reporte de inventario
  - Filtros por fecha, vendedor, proveedor
  - Gráficos y resúmenes
- **Permisos**: `reportes.ver`

### 10. **Proveedores** ✅
- **Rutas**: `/proveedores`
- **Funcionalidades**:
  - CRUD completo
  - Historial de compras por proveedor
  - Gestión de información de contacto
- **Permisos**: `proveedores.ver`, `proveedores.crear`, `proveedores.editar`, `proveedores.eliminar`

### 11. **Bitácora** ✅
- **Rutas**: `/bitacora`
- **Funcionalidades**:
  - Listado de registros de auditoría
  - Filtros avanzados (módulo, usuario, tabla, fechas)
  - Ver detalle de registros
  - Visualización de datos anteriores y nuevos
- **Permisos**: `bitacora.ver`

### 12. **Categorías** ✅
- **Rutas**: `/categorias`
- **Funcionalidades**:
  - CRUD completo
  - Asignación de subcategorías
  - Estadísticas de uso (productos, servicios, materiales)
- **Permisos**: `categorias.ver`, `categorias.crear`, `categorias.editar`, `categorias.eliminar`

### 13. **Sectores** ✅
- **Rutas**: `/sectores`
- **Funcionalidades**:
  - CRUD completo
  - Asignación a almacenes
  - Control de capacidad y stock
  - Visualización de materiales por sector
- **Permisos**: `sectores.ver`, `sectores.crear`, `sectores.editar`, `sectores.eliminar`

### 14. **Pagos** ✅
- **Rutas**: `/pagos`
- **Funcionalidades**:
  - Listado de pagos con filtros
  - Ver detalle de pagos
  - Registrar pagos pendientes
  - Estadísticas de pagos
  - Confirmación automática de pedidos
- **Permisos**: `pagos.ver`, `pagos.registrar`

### 15. **Métodos de Pago** ✅
- **Rutas**: `/metodos-pago`
- **Funcionalidades**:
  - CRUD completo
  - Estadísticas de uso
  - Validación antes de eliminar
- **Permisos**: `metodos_pago.ver`, `metodos_pago.crear`, `metodos_pago.editar`, `metodos_pago.eliminar`

### 16. **Pagofacil** ✅
- **Rutas**: `/pagofacil/*`
- **Funcionalidades**:
  - Crear cupones de pago
  - Generar códigos QR y de barras
  - Crear planes de pagos a plazos
  - Verificar estado de pagos
  - **Nota**: Actualmente simulado, listo para integración con API real
- **Permisos**: `pagos.crear`, `pagos.ver`

---

## 📦 Instalación

### Requisitos Previos

- PHP >= 8.1
- Composer
- Node.js >= 16.x y npm
- PostgreSQL (o la base de datos configurada)
- Git

### Paso 1: Instalar Dependencias de PHP

```bash
# Navegar al directorio del proyecto
cd "C:\Users\Shirley Gutierrez\Documents\tecno\proeyectogruplaWEB\2do parcial\back_carpinteria"

# Instalar dependencias de Composer
composer install
```

### Paso 2: Instalar Dependencias de Node.js

```bash
# Instalar dependencias de npm
npm install
```

### Paso 3: Configurar Variables de Entorno

```bash
# Copiar el archivo .env.example a .env (si no existe)
cp .env.example .env

# Generar la clave de aplicación
php artisan key:generate
```

### Paso 4: Configurar Base de Datos

Editar el archivo `.env` y configurar la conexión a la base de datos:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=nombre_base_datos
DB_USERNAME=usuario
DB_PASSWORD=contraseña
```

### Paso 5: Ejecutar Migraciones

```bash
# Ejecutar todas las migraciones
php artisan migrate

# Ejecutar seeders (datos iniciales)
php artisan db:seed
```

**Seeders importantes:**
- `MenuItemSeeder`: Crea el menú dinámico
- `RolSeeder`: Crea los roles del sistema
- `PermisoSeeder`: Crea los permisos
- `UsuarioSeeder`: Crea usuarios iniciales

---

## 🚀 Ejecución

### Desarrollo

El proyecto utiliza **Laravel** para el backend y **Vite** para compilar los assets del frontend.

#### Terminal 1: Servidor Laravel

```bash
# Iniciar el servidor de desarrollo de Laravel
php artisan serve
```

El servidor estará disponible en: `http://localhost:8000`

#### Terminal 2: Compilador Vite (Vue.js)

```bash
# Iniciar Vite para compilar Vue.js en tiempo real
npm run dev
```

Vite estará disponible en: `http://localhost:5173` (o el puerto que asigne)

### Producción

Para producción, primero compila los assets:

```bash
# Compilar assets para producción
npm run build
```

Luego inicia el servidor Laravel:

```bash
php artisan serve
```

---

## 📁 Estructura del Proyecto

```
back_carpinteria/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Web/              # Controladores para Inertia
│   │   │       ├── AuthController.php
│   │   │       ├── DashboardController.php
│   │   │       ├── ProductoController.php
│   │   │       ├── ServicioController.php
│   │   │       ├── MaterialController.php
│   │   │       ├── PedidoController.php
│   │   │       ├── CompraController.php
│   │   │       ├── UsuarioController.php
│   │   │       ├── RolController.php
│   │   │       ├── InventarioController.php
│   │   │       ├── ReporteController.php
│   │   │       ├── ProveedorController.php
│   │   │       ├── BitacoraController.php
│   │   │       ├── CategoriaController.php
│   │   │       ├── SectorController.php
│   │   │       ├── PagoController.php
│   │   │       ├── MetodoPagoController.php
│   │   │       └── PagoFacilController.php
│   │   └── Middleware/
│   │       ├── HandleInertiaRequests.php
│   │       └── TrackPageVisits.php
│   └── Models/                   # Modelos Eloquent
├── resources/
│   ├── js/
│   │   ├── Pages/                # Páginas Vue (Inertia)
│   │   │   ├── Login.vue
│   │   │   ├── Dashboard.vue
│   │   │   ├── Productos/
│   │   │   ├── Servicios/
│   │   │   ├── Materiales/
│   │   │   ├── Pedidos/
│   │   │   ├── Compras/
│   │   │   ├── Usuarios/
│   │   │   ├── Roles/
│   │   │   ├── Inventario/
│   │   │   ├── Reportes/
│   │   │   ├── Proveedores/
│   │   │   ├── Bitacora/
│   │   │   ├── Categorias/
│   │   │   ├── Sectores/
│   │   │   ├── Pagos/
│   │   │   ├── MetodosPago/
│   │   │   └── PagoFacil/
│   │   ├── Components/           # Componentes reutilizables
│   │   │   ├── Form/
│   │   │   ├── UI/
│   │   │   ├── Table/
│   │   │   ├── ThemeSelector.vue
│   │   │   ├── AccessibilityControls.vue
│   │   │   └── SearchBar.vue
│   │   ├── Layouts/
│   │   │   └── AppLayout.vue
│   │   └── app.js                # Punto de entrada Vue
│   ├── css/
│   │   ├── themes/               # Temas CSS
│   │   ├── accessibility.css
│   │   └── app.css
│   └── views/
│       └── app.blade.php         # Template principal
├── routes/
│   └── web.php                   # Rutas web (Inertia)
├── vite.config.js                # Configuración de Vite
├── package.json                  # Dependencias npm
└── composer.json                 # Dependencias PHP
```

---

## 🛣️ Rutas Disponibles

### Autenticación
- `GET /login` - Formulario de login
- `POST /login` - Procesar login
- `POST /logout` - Cerrar sesión

### Dashboard
- `GET /` - Dashboard principal

### Módulos Principales
- `GET /productos` - Listado de productos
- `GET /servicios` - Listado de servicios
- `GET /materiales` - Listado de materiales
- `GET /pedidos` - Listado de pedidos
- `GET /compras` - Listado de compras
- `GET /usuarios` - Listado de usuarios
- `GET /roles` - Listado de roles
- `GET /inventario` - Movimientos de inventario
- `GET /inventario/stock` - Stock actual
- `GET /reportes` - Selección de reportes
- `GET /proveedores` - Listado de proveedores
- `GET /bitacora` - Registros de bitácora
- `GET /categorias` - Listado de categorías
- `GET /sectores` - Listado de sectores
- `GET /pagos` - Listado de pagos
- `GET /metodos-pago` - Listado de métodos de pago

### Búsqueda
- `GET /buscar?q=termino` - Búsqueda global

---

## 🎨 Características Adicionales

### Temas y Accesibilidad
- **3 Temas**: Niños, Jóvenes, Adultos
- **Modo Día/Noche**: Automático según hora del cliente
- **Accesibilidad**: Ajuste de tamaño de fuente y contraste

### Menú Dinámico
- Menú basado en base de datos
- Permisos por rol
- Actualización automática

### Contador de Visitas
- Contador por página en el footer
- Actualización automática

### Validaciones
- Todas las validaciones en español
- Mensajes de error personalizados
- Validación en frontend y backend

---

## 🔧 Comandos Útiles

### Desarrollo
```bash
# Limpiar caché de Laravel
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Regenerar autoload de Composer
composer dump-autoload

# Recompilar assets
npm run dev
```

### Base de Datos
```bash
# Crear nueva migración
php artisan make:migration nombre_migracion

# Crear nuevo seeder
php artisan make:seeder NombreSeeder

# Ejecutar migraciones frescas con seeders
php artisan migrate:fresh --seed
```

### Producción
```bash
# Optimizar Laravel
php artisan optimize

# Compilar assets para producción
npm run build

# Cachear rutas y configuraciones
php artisan route:cache
php artisan config:cache
```

---

## 📝 Notas Importantes

### Pagofacil
El módulo de Pagofacil está **preparado para integración real** pero actualmente usa simulación. Para integrar con la API real:

1. Instalar SDK de Pagofacil (si existe)
2. Configurar credenciales en `.env`:
   ```env
   PAGOFACIL_API_KEY=tu_api_key
   PAGOFACIL_SECRET_KEY=tu_secret_key
   ```
3. Descomentar y ajustar el código en `app/Http/Controllers/Web/PagoFacilController.php`

### Permisos
Todos los módulos requieren permisos específicos. Asegúrate de que los usuarios tengan los permisos necesarios asignados a sus roles.

### Base de Datos
El sistema utiliza PostgreSQL por defecto. Asegúrate de tener:
- Las migraciones ejecutadas
- Los seeders ejecutados
- Las vistas de base de datos creadas (v_ventas_diarias, v_stock_bajo_productos, etc.)

---

## 🐛 Solución de Problemas

### Error: "Vite manifest not found"
```bash
npm run dev
# O para producción:
npm run build
```

### Error: "Class not found"
```bash
composer dump-autoload
php artisan optimize:clear
```

### Error: "Route not found"
```bash
php artisan route:clear
php artisan route:cache
```

### Error: "Component not found"
Verificar que el componente existe en `resources/js/Components/` o `resources/js/Pages/`

---

## 📚 Recursos Adicionales

- [Documentación de Laravel](https://laravel.com/docs)
- [Documentación de Inertia.js](https://inertiajs.com/)
- [Documentación de Vue.js](https://vuejs.org/)
- [Documentación de Vite](https://vitejs.dev/)

---

## ✅ Checklist de Instalación

- [ ] PHP >= 8.1 instalado
- [ ] Composer instalado
- [ ] Node.js >= 16.x instalado
- [ ] PostgreSQL configurado
- [ ] `composer install` ejecutado
- [ ] `npm install` ejecutado
- [ ] Archivo `.env` configurado
- [ ] `php artisan key:generate` ejecutado
- [ ] `php artisan migrate` ejecutado
- [ ] `php artisan db:seed` ejecutado
- [ ] `php artisan serve` ejecutado
- [ ] `npm run dev` ejecutado
- [ ] Acceso a `http://localhost:8000` verificado

---

**¡Listo!** El sistema está completamente funcional con todos los módulos implementados. 🎉

