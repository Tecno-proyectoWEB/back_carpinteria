# ✅ PHP 8.3 Configurado con PostgreSQL

## ✅ Estado Actual

- **PHP 8.3.28** instalado y configurado ✓
- **Extensiones PostgreSQL** habilitadas:
  - `pdo_pgsql` ✓
  - `pgsql` ✓
- **libpq.dll** copiado ✓

## 📍 Ubicación de PHP 8.3

```
C:/Users/Shirley Gutierrez/AppData/Local/Microsoft/WinGet/Packages/PHP.PHP.NTS.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe/
```

## 🔧 Usar PHP 8.3 en tu Proyecto

### Opción 1: Usar Ruta Completa (Temporal)

Para comandos de Laravel, usa la ruta completa:

```bash
"C:/Users/Shirley Gutierrez/AppData/Local/Microsoft/WinGet/Packages/PHP.PHP.NTS.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe/php.exe" artisan migrate
```

### Opción 2: Agregar al PATH (Recomendado)

1. **Abre Variables de Entorno:**
   - Presiona `Win + R`
   - Escribe: `sysdm.cpl`
   - Ve a la pestaña **Avanzado**
   - Click en **Variables de entorno**

2. **Edita PATH:**
   - Busca **Path** en Variables del sistema
   - Click en **Editar**
   - Click en **Nuevo**
   - Agrega: `C:\Users\Shirley Gutierrez\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.NTS.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe`
   - **Mueve esta entrada al principio** de la lista (para que tenga prioridad sobre Herd)
   - Click en **Aceptar**

3. **Reinicia tu terminal** completamente

4. **Verifica:**
   ```bash
   php -v
   ```
   Deberías ver: `PHP 8.3.28`

### Opción 3: Crear Alias (Rápido)

Agrega esto a tu `.bashrc` o `.bash_profile`:

```bash
alias php83="C:/Users/Shirley Gutierrez/AppData/Local/Microsoft/WinGet/Packages/PHP.PHP.NTS.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe/php.exe"
```

Luego usa: `php83 artisan migrate`

## 🧪 Probar Conexión

```bash
# Verificar extensiones
php -m | grep pgsql

# Probar conexión a PostgreSQL
php artisan migrate:status
```

## 🚀 Ejecutar Migraciones

```bash
# Ver estado
php artisan migrate:status

# Ejecutar todas las migraciones
php artisan migrate:fresh

# Ejecutar seeders
php artisan db:seed
```

## 📝 Notas

- **Herd seguirá usando PHP 8.4** a menos que lo configures
- **Si agregas PHP 8.3 al PATH**, será el que se use por defecto
- **Las extensiones ya están habilitadas** en el `php.ini` de PHP 8.3

## ⚠️ Si tienes problemas

1. **Verifica que libpq.dll esté accesible:**
   - Ya está copiado junto a `php.exe`
   - O asegúrate de que `C:\Program Files\PostgreSQL\17\bin` esté en el PATH

2. **Verifica tu `.env`:**
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=tu_base_de_datos
   DB_USERNAME=postgres
   DB_PASSWORD=tu_contraseña
   ```

---

**¿Quieres que agregue PHP 8.3 al PATH automáticamente?** Puedo ayudarte con eso.

