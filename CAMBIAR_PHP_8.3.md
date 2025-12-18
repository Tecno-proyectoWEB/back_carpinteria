# 🔄 Cambiar a PHP 8.3 en Herd Lite

## Problema
Los DLLs de PostgreSQL son para PHP 8.3, pero estás usando PHP 8.4.

## Solución: Cambiar a PHP 8.3 en Herd Lite

### Opción 1: Desde la Interfaz de Herd Lite (Más Fácil)

1. **Abre Herd Lite**
2. **Click derecho** en el icono de Herd Lite en la bandeja del sistema
3. Busca la opción **"PHP Version"** o **"Switch PHP Version"**
4. Selecciona **PHP 8.3**
5. **Reinicia** el servidor Laravel

### Opción 2: Desde la Terminal

Si Herd Lite tiene comandos CLI:

```bash
# Ver versiones disponibles
herd php:list

# Cambiar a PHP 8.3
herd php:use 8.3
```

### Opción 3: Configuración Manual

1. **Descarga PHP 8.3** desde: https://windows.php.net/download/
2. **Extrae** en una carpeta (ej: `C:\php83\`)
3. **Copia** los DLLs de PostgreSQL de PHP 8.3 a la carpeta `ext` de PHP 8.3
4. **Configura** Herd Lite para usar esa instalación de PHP 8.3

---

## Después de Cambiar

1. **Verifica** la versión:
   ```bash
   php -v
   ```
   Debería mostrar PHP 8.3.x

2. **Verifica** las extensiones:
   ```bash
   php -m | grep pdo_pgsql
   ```
   Debería mostrar `pdo_pgsql` sin warnings

3. **Prueba** la conexión:
   ```bash
   php artisan db:show
   ```

4. **Reinicia** el servidor Laravel:
   ```bash
   php artisan serve
   ```

---

## Nota

Una vez que cambies a PHP 8.3, los DLLs de PostgreSQL deberían funcionar correctamente porque coincidirán las versiones de la API del módulo.



