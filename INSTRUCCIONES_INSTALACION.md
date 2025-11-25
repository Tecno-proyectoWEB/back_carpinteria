# Instrucciones de Instalación - Frontend con Inertia

## 📋 Pasos para completar la instalación

### 1. Instalar dependencias de PHP (Composer)

```bash
composer require inertiajs/inertia-laravel
composer require tightenco/ziggy
```

### 2. Instalar dependencias de Node.js (NPM)

```bash
npm install
```

Esto instalará automáticamente:
- `vue@^3.4.0`
- `@inertiajs/vue3@^1.0.0`
- `@vitejs/plugin-vue@^5.0.0`

### 3. Publicar configuración de Inertia (opcional)

```bash
php artisan inertia:install
```

Este comando creará el middleware si no existe (ya está creado manualmente).

### 4. Compilar assets

Para desarrollo:
```bash
npm run dev
```

Para producción:
```bash
npm run build
```

### 5. Ejecutar el servidor

En una terminal:
```bash
php artisan serve
```

En otra terminal (si no usas el script dev):
```bash
npm run dev
```

### 6. Acceder a la aplicación

Abre tu navegador en: `http://localhost:8000`

---

## ✅ Lo que ya está configurado

- ✅ Middleware `HandleInertiaRequests` creado
- ✅ Middleware agregado al grupo `web` en `Kernel.php`
- ✅ Plantilla Blade `app.blade.php` creada
- ✅ Estructura de carpetas Vue creada:
  - `resources/js/Pages/Login.vue`
  - `resources/js/Pages/Dashboard.vue`
  - `resources/js/Layouts/AppLayout.vue`
- ✅ Controladores web creados:
  - `app/Http/Controllers/Web/AuthController.php`
  - `app/Http/Controllers/Web/DashboardController.php`
  - `app/Http/Controllers/MenuController.php`
- ✅ Rutas web configuradas en `routes/web.php`
- ✅ `vite.config.js` configurado con Vue
- ✅ `package.json` actualizado con dependencias

---

## 🔧 Configuración adicional necesaria

### Variables de entorno

Asegúrate de tener en tu `.env`:

```env
APP_NAME="Carpintería Jorge"
APP_URL=http://localhost:8000
```

### Autenticación

El sistema ya está configurado para usar el modelo `Usuario` en lugar de `User`.

---

## 📁 Estructura creada

```
back_carpinteria/
├── app/
│   └── Http/
│       ├── Controllers/
│       │   ├── Web/
│       │   │   ├── AuthController.php      ← NUEVO
│       │   │   └── DashboardController.php   ← NUEVO
│       │   └── MenuController.php            ← NUEVO
│       └── Middleware/
│           └── HandleInertiaRequests.php     ← NUEVO
├── resources/
│   ├── js/
│   │   ├── app.js                            ← ACTUALIZADO
│   │   ├── Pages/
│   │   │   ├── Login.vue                     ← NUEVO
│   │   │   └── Dashboard.vue                 ← NUEVO
│   │   └── Layouts/
│   │       └── AppLayout.vue                 ← NUEVO
│   ├── css/
│   │   └── app.css
│   └── views/
│       └── app.blade.php                     ← NUEVO
└── routes/
    └── web.php                                ← ACTUALIZADO
```

---

## 🚀 Próximos pasos

Después de instalar las dependencias, podrás:

1. **Acceder al login**: `http://localhost:8000/login`
2. **Ver el dashboard**: `http://localhost:8000` (requiere autenticación)

---

## ⚠️ Notas importantes

- Las rutas API (`routes/api.php`) siguen funcionando normalmente
- El frontend ahora usa sesiones en lugar de tokens para autenticación web
- Los controladores API (`app/Http/Controllers/AuthController.php`) siguen disponibles para API REST
- Los controladores web (`app/Http/Controllers/Web/`) son para Inertia

---

## 🐛 Solución de problemas

### Error: "Class 'Inertia\Inertia' not found"
- Ejecuta: `composer require inertiajs/inertia-laravel`

### Error: "Cannot find module 'vue'"
- Ejecuta: `npm install`

### Error: "Route [login] not defined"
- Verifica que `routes/web.php` tenga la ruta de login

### Error: "Target class [App\Http\Middleware\HandleInertiaRequests] does not exist"
- Verifica que el archivo existe en `app/Http/Middleware/HandleInertiaRequests.php`
- Ejecuta: `composer dump-autoload`

---

## 📝 Comandos útiles

```bash
# Limpiar caché
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Regenerar autoload
composer dump-autoload

# Recompilar assets
npm run build
```

