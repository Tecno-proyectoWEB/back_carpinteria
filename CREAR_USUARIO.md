# 👤 Cómo Crear un Nuevo Usuario

## 🎯 Opciones Disponibles

Hay varias formas de crear usuarios en el sistema. Elige la que prefieras:

---

## 1️⃣ Desde la Aplicación Web (Recomendado)

Si tienes acceso al módulo de usuarios en la aplicación:

1. **Inicia sesión** con un usuario administrador
2. Ve a la sección **Usuarios** en el menú
3. Click en **Crear Nuevo Usuario**
4. Completa el formulario:
   - Nombre
   - Apellido
   - Email
   - Teléfono
   - Contraseña
   - Rol (PROPIETARIO, SECRETARIA, CARPINTERO, CLIENTE)
5. Guarda el usuario

**URL:** `http://127.0.0.1:8000/usuarios/create`

---

## 2️⃣ Desde Laravel Tinker (Rápido)

### Paso 1: Abrir Tinker

```bash
export PATH="/c/Program Files/PostgreSQL/17/bin:$PATH"
"C:/Users/Shirley Gutierrez/AppData/Local/Microsoft/WinGet/Packages/PHP.PHP.NTS.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe/php.exe" artisan tinker
```

### Paso 2: Crear Usuario

```php
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Support\Facades\Hash;

// Obtener un rol (ejemplo: Administrador)
$rol = Rol::where('nombre', 'ADMINISTRADOR')->first();

// Crear usuario
$usuario = Usuario::create([
    'nombre' => 'Tu Nombre',
    'apellido' => 'Tu Apellido',
    'email' => 'tuemail@example.com',
    'telefono' => '0987654321',
    'password' => Hash::make('tu_contraseña'),
    'rol_id' => $rol->id,
    'estado' => true,
    'disponibilidad' => true,
    'cuenta_no_expirada' => true,
    'cuenta_no_bloqueada' => true,
    'credenciales_no_expiradas' => true,
]);

echo "Usuario creado: " . $usuario->email;
```

### Paso 3: Salir de Tinker

```php
exit
```

---

## 3️⃣ Desde un Seeder (Para Múltiples Usuarios)

### Crear un nuevo seeder:

```bash
php artisan make:seeder CrearMiUsuarioSeeder
```

### Editar el seeder:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Support\Facades\Hash;

class CrearMiUsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $rol = Rol::where('nombre', 'ADMINISTRADOR')->first();
        
        Usuario::firstOrCreate(
            ['email' => 'miemail@example.com'],
            [
                'nombre' => 'Mi Nombre',
                'apellido' => 'Mi Apellido',
                'telefono' => '0987654321',
                'password' => Hash::make('mi_contraseña'),
                'rol_id' => $rol->id,
                'estado' => true,
                'disponibilidad' => true,
                'cuenta_no_expirada' => true,
                'cuenta_no_bloqueada' => true,
                'credenciales_no_expiradas' => true,
            ]
        );
    }
}
```

### Ejecutar el seeder:

```bash
php artisan db:seed --class=CrearMiUsuarioSeeder
```

---

## 4️⃣ Desde PostgreSQL Directamente (Avanzado)

### Conectarse a PostgreSQL:

```bash
psql -U postgres -d Tecnonuevo
```

### Insertar usuario:

```sql
-- Primero obtener el ID del rol
SELECT id, nombre FROM rol;

-- Insertar usuario (reemplaza los valores)
INSERT INTO usuario (
    nombre, 
    apellido, 
    email, 
    telefono, 
    password, 
    rol_id,
    estado,
    disponibilidad,
    cuenta_no_expirada,
    cuenta_no_bloqueada,
    credenciales_no_expiradas
) VALUES (
    'Tu Nombre',
    'Tu Apellido',
    'tuemail@example.com',
    '0987654321',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password hasheado
    1, -- ID del rol
    true,
    true,
    true,
    true,
    true
);
```

**Nota:** Necesitas generar el hash de la contraseña primero. Es mejor usar Tinker o la aplicación web.

---

## 5️⃣ Usuarios de Prueba Ya Creados

Si ejecutaste los seeders, ya tienes estos usuarios:

### Usuario Administrador:
- **Email:** `admin@example.com`
- **Contraseña:** `password`

### Usuario Propietario:
- **Email:** `propietario@carpinteria.com`
- **Contraseña:** `password123`

### Usuario Secretaria:
- **Email:** `secretaria@carpinteria.com`
- **Contraseña:** `password123`

### Usuario Carpintero:
- **Email:** `carpintero@carpinteria.com`
- **Contraseña:** `password123`

### Usuario Cliente:
- **Email:** `cliente@example.com`
- **Contraseña:** `password123`

---

## 📋 Roles Disponibles

Para asignar un rol al usuario, primero verifica qué roles existen:

```bash
php artisan tinker
```

```php
use App\Models\Rol;
Rol::all(['id', 'nombre']);
```

Roles comunes:
- `ADMINISTRADOR`
- `PROPIETARIO`
- `SECRETARIA`
- `CARPINTERO`
- `CLIENTE`

---

## ✅ Verificar Usuario Creado

```bash
php artisan tinker
```

```php
use App\Models\Usuario;
Usuario::where('email', 'tuemail@example.com')->first();
```

---

## 🔐 Campos Requeridos

- `nombre` - Nombre del usuario
- `apellido` - Apellido del usuario
- `email` - Email único
- `password` - Contraseña (debe estar hasheada)
- `rol_id` - ID del rol
- `estado` - true/false
- `disponibilidad` - true/false
- `cuenta_no_expirada` - true
- `cuenta_no_bloqueada` - true
- `credenciales_no_expiradas` - true

---

## 🚀 Método Más Rápido

**Usa Tinker** - Es la forma más rápida y segura:

```bash
php artisan tinker
```

Luego copia y pega el código de la opción 2.

---

**¿Quieres que te ayude a crear un usuario específico ahora?**

