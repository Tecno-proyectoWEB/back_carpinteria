# 🚀 Instrucciones para Ejecutar el Servidor Correctamente

## ⚠️ Problema Actual

El error `could not find driver` aparece porque hay múltiples servidores PHP corriendo, y algunos usan PHP 8.4 (sin DLLs de PostgreSQL).

## ✅ Solución: Usar el Script de Reinicio

### Paso 1: Detener TODOS los servidores
Ejecuta este comando en una terminal:
```bash
taskkill /F /IM php.exe
```

### Paso 2: Iniciar el servidor con PHP 8.3
Ejecuta el script:
```bash
reiniciar_servidor.bat
```

O manualmente:
```bash
"C:\Users\Shirley Gutierrez\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.NTS.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe" artisan serve --host=127.0.0.1 --port=8000
```

## 🔍 Verificación

### Verificar que está usando PHP 8.3:
1. Abre http://127.0.0.1:8000
2. Si ves el error "could not find driver" → está usando PHP 8.4
3. Si funciona correctamente → está usando PHP 8.3

### Ver qué PHP está corriendo:
```bash
wmic process where "name='php.exe' and CommandLine like '%artisan serve%'" get CommandLine
```

Debería mostrar la ruta completa a PHP 8.3.

## 📝 Notas Importantes

1. **Siempre detén todos los procesos PHP antes de iniciar uno nuevo**
2. **Usa siempre la ruta completa de PHP 8.3** o el script `reiniciar_servidor.bat`
3. **No uses `php artisan serve` directamente** - usará PHP 8.4 del PATH

## 🛠️ Scripts Disponibles

- `reiniciar_servidor.bat` - Detiene todo y reinicia con PHP 8.3
- `ejecutar_proyecto.bat` - Inicia Laravel y Vite (actualizado para PHP 8.3)



