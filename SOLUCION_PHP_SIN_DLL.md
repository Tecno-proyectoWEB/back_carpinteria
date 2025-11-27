# 🔧 Solución: PHP sin DLLs de PostgreSQL

## Problema
Tu versión actual de PHP 8.4.0 no incluye los DLLs de PostgreSQL (`php_pgsql.dll` y `php_pdo_pgsql.dll`).

## Soluciones

### Opción 1: Cambiar a PHP 8.3 o 8.2 (Recomendado)

Las versiones anteriores de PHP suelen tener más extensiones disponibles.

#### Si usas Herd (Interfaz Gráfica):
1. Abre **Herd** (la aplicación)
2. Ve a **Settings** o **Configuración**
3. Busca **PHP Version** o **Versión de PHP**
4. Cambia a **PHP 8.3** o **PHP 8.2**
5. Reinicia Herd

#### Si usas Herd desde Terminal:
```bash
# Ver versiones disponibles
herd use --list

# Cambiar a PHP 8.3
herd use php83

# O cambiar a PHP 8.2
herd use php82
```

### Opción 2: Descargar PHP Completo con Extensiones

1. **Ve a:** https://windows.php.net/download/
2. **Descarga PHP 8.3 o 8.2:**
   - Elige **Thread Safe (TS)** o **Non-Thread Safe (NTS)**
   - Descarga el archivo `.zip`
3. **Extrae y reemplaza:**
   - Extrae el contenido
   - Los DLLs de PostgreSQL deberían estar en la carpeta `ext`
   - Copia `php_pgsql.dll` y `php_pdo_pgsql.dll` a `C:\php\ext\`

### Opción 3: Usar XAMPP o Laragon (Alternativa)

Si Herd no tiene las extensiones, puedes usar:

- **XAMPP:** https://www.apachefriends.org/
  - Incluye PHP con extensiones de PostgreSQL
- **Laragon:** https://laragon.org/
  - Gestor de versiones de PHP con extensiones

### Opción 4: Compilar las Extensiones (Avanzado)

Si realmente necesitas PHP 8.4:

1. Instala Visual Studio Build Tools
2. Descarga el código fuente de las extensiones
3. Compila los DLLs

**Nota:** Esto es complejo y toma tiempo.

## 🎯 Recomendación

**Cambia a PHP 8.3** - Es la versión más estable y tiene todas las extensiones disponibles.

### Pasos para Cambiar en Herd:

1. **Abre Herd** (la aplicación de escritorio)
2. **Busca la configuración de PHP**
3. **Selecciona PHP 8.3**
4. **Reinicia Herd**

Luego verifica:
```bash
php -v
php -m | grep pgsql
```

## 📝 Después de Cambiar

Una vez que tengas una versión con los DLLs:

1. **Habilita en php.ini:**
   ```ini
   extension=pgsql
   extension=pdo_pgsql
   ```

2. **Verifica:**
   ```bash
   php -m | grep pgsql
   ```

3. **Prueba conexión:**
   ```bash
   php artisan migrate:status
   ```

## 🔍 Verificar Versiones Disponibles

Si tienes acceso a la interfaz de Herd, deberías ver algo como:
- PHP 8.4 (actual - sin DLLs)
- PHP 8.3 (recomendado)
- PHP 8.2
- PHP 8.1

**Elige PHP 8.3 o 8.2** para tener soporte completo de PostgreSQL.

---

**¿Qué versión de PHP puedes instalar en Herd?** Avísame y te ayudo a configurarla.

