# 🚀 Comandos para Ejecutar el Proyecto

## Comando Completo (Manual)

### Opción 1: Usar el Script Batch (Recomendado)
```bash
ejecutar_proyecto.bat
```

Este script:
- ✅ Configura PHP 8.3 automáticamente
- ✅ Limpia la cache
- ✅ Inicia Laravel en http://127.0.0.1:8000
- ✅ Inicia Vite en http://localhost:5173

### Opción 2: Comandos Manuales

#### Terminal 1 - Servidor Laravel:
```bash
"C:\Users\Shirley Gutierrez\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.NTS.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe" artisan serve --host=127.0.0.1 --port=8000
```

#### Terminal 2 - Vite (Frontend):
```bash
npm run dev
```

## Comandos Útiles

### Limpiar Cache
```bash
"C:\Users\Shirley Gutierrez\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.NTS.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe" artisan config:clear
"C:\Users\Shirley Gutierrez\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.NTS.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe" artisan cache:clear
```

### Verificar Base de Datos
```bash
"C:\Users\Shirley Gutierrez\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.NTS.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe" artisan db:show
```

### Migraciones
```bash
"C:\Users\Shirley Gutierrez\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.NTS.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe" artisan migrate
```

## URLs del Proyecto

- **Backend Laravel**: http://127.0.0.1:8000
- **Frontend Vite**: http://localhost:5173
- **Aplicación**: http://127.0.0.1:8000 (Laravel sirve el frontend)

## Nota Importante

⚠️ **Problema de Credenciales PostgreSQL**: 
El proyecto está corriendo pero hay un error de autenticación con PostgreSQL. El driver funciona correctamente, pero las credenciales en el `.env` necesitan ser verificadas con el administrador de la base de datos.

## Solución Rápida

Si necesitas usar comandos artisan frecuentemente, puedes crear un alias:

```bash
# En Git Bash, agrega a ~/.bashrc:
alias php83='"C:/Users/Shirley Gutierrez/AppData/Local/Microsoft/WinGet/Packages/PHP.PHP.NTS.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe/php.exe"'

# Luego usa:
php83 artisan serve
php83 artisan migrate
```



