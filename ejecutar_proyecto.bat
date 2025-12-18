@echo off
REM Script para ejecutar el proyecto Laravel con PHP 8.3
echo ========================================
echo   Ejecutando Proyecto Laravel
echo ========================================
echo.

REM Configurar PHP 8.3
set PHP83_PATH=C:\Users\Shirley Gutierrez\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.NTS.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe
set PATH=%PHP83_PATH%;%PATH%

REM Verificar PHP
echo Verificando PHP 8.3...
%PHP83_PATH%\php.exe -v
echo.

REM Limpiar cache
echo Limpiando cache...
%PHP83_PATH%\php.exe artisan config:clear
%PHP83_PATH%\php.exe artisan cache:clear
echo.

REM Iniciar servidor Laravel
echo Iniciando servidor Laravel en http://127.0.0.1:8000...
start "Laravel Server PHP 8.3" cmd /k "cd /d \"%CD%\" && \"%PHP83_PATH%\php.exe\" artisan serve --host=127.0.0.1 --port=8000"
echo.

REM Esperar un momento
timeout /t 2 /nobreak >nul

REM Iniciar Vite
echo Iniciando Vite en http://localhost:5173...
start "Vite Dev Server" cmd /k "npm run dev"
echo.

echo ========================================
echo   Servidores iniciados!
echo ========================================
echo.
echo Laravel: http://127.0.0.1:8000
echo Vite:    http://localhost:5173
echo.
echo Presiona cualquier tecla para cerrar esta ventana...
pause >nul

