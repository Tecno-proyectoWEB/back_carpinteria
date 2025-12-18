# 🔧 Solución: Error "could not find driver"

## Problema

El error `could not find driver (Connection: pgsql)` aparece cuando:
- El servidor Laravel está usando **PHP 8.4** (que no tiene DLLs de PostgreSQL)
- En lugar de **PHP 8.3** (que sí tiene los DLLs)

## Causa

El PATH de Windows tiene primero PHP 8.4 (herd-lite), entonces cuando ejecutas `php artisan serve`, usa PHP 8.4.

## Solución

### ✅ Usar la ruta completa de PHP 8.3

**Comando correcto:**
```bash
"C:\Users\Shirley Gutierrez\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.NTS.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe" artisan serve --host=127.0.0.1 --port=8000
```

### ✅ Usar el script actualizado

Ejecuta:
```bash
ejecutar_proyecto.bat
```

Este script ahora usa la ruta completa de PHP 8.3.

## Verificación

Para verificar que el servidor está usando PHP 8.3:
1. Abre http://127.0.0.1:8000
2. Si ves el error "could not find driver", el servidor está usando PHP 8.4
3. Si funciona correctamente, está usando PHP 8.3

## Nota Importante

⚠️ **Siempre usa la ruta completa de PHP 8.3** cuando ejecutes comandos artisan, o el script `ejecutar_proyecto.bat` que ya está configurado correctamente.



