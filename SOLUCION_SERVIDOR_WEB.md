# 🔧 Solución: Error "could not find driver" en Servidor Web

## ⚠️ Problema

El servidor web (Herd) está usando **PHP 8.4** que no tiene las extensiones de PostgreSQL, mientras que en la línea de comandos usamos **PHP 8.3** que sí las tiene.

## ✅ Solución Rápida: Usar `php artisan serve`

En lugar de usar el servidor de Herd, usa el servidor de desarrollo de Laravel con PHP 8.3:

### Terminal 1 - Compilar Frontend:
```bash
export PATH="/c/Program Files/PostgreSQL/17/bin:$PATH"
npm run dev
```

### Terminal 2 - Iniciar Servidor:
```bash
export PATH="/c/Program Files/PostgreSQL/17/bin:$PATH"
"C:/Users/Shirley Gutierrez/AppData/Local/Microsoft/WinGet/Packages/PHP.PHP.NTS.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe/php.exe" artisan serve
```

O si agregaste PHP 8.3 al PATH:
```bash
export PATH="/c/Program Files/PostgreSQL/17/bin:$PATH"
php artisan serve
```

### Acceder a:
- **http://localhost:8000**

## 🔧 Solución Permanente: Configurar Herd para usar PHP 8.3

### Opción 1: Cambiar PHP en Herd (Interfaz Gráfica)

1. Abre **Herd** (la aplicación)
2. Ve a **Settings** o **Configuración**
3. Busca **PHP Version** o **Versión de PHP**
4. Cambia a **PHP 8.3** (si está disponible)
5. Reinicia Herd

### Opción 2: Configurar Herd para usar PHP 8.3 Manualmente

1. **Crea un enlace simbólico o copia PHP 8.3:**
   ```bash
   # Detener Herd primero
   # Luego reemplaza el PHP de Herd con PHP 8.3
   ```

2. **O configura Herd para usar el PHP 8.3 instalado**

### Opción 3: Agregar PHP 8.3 al PATH (Antes de Herd)

1. **Agrega al PATH del sistema:**
   ```
   C:\Users\Shirley Gutierrez\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.NTS.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe
   ```

2. **Colócalo ANTES de Herd** en el PATH

3. **Reinicia Herd** para que use PHP 8.3

## 📝 Script Rápido para Iniciar

Crea un archivo `start.bat` o `start.sh`:

```bash
#!/bin/bash
export PATH="/c/Program Files/PostgreSQL/17/bin:$PATH"
"C:/Users/Shirley Gutierrez/AppData/Local/Microsoft/WinGet/Packages/PHP.PHP.NTS.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe/php.exe" artisan serve
```

## ✅ Verificación

Después de configurar, verifica que funcione:

```bash
# Verificar PHP
php -v  # Debería ser 8.3.28

# Verificar extensiones
php -m | grep pgsql  # Debería mostrar pdo_pgsql y pgsql

# Probar servidor
php artisan serve
```

---

**Recomendación:** Usa `php artisan serve` con PHP 8.3 por ahora. Es la solución más rápida y funciona perfectamente.

