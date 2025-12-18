@echo off
echo ========================================
echo   Deteniendo servidores PHP...
echo ========================================
taskkill /F /IM php.exe >nul 2>&1
timeout /t 2 /nobreak >nul
echo Servidores detenidos.
echo.

echo ========================================
echo   Iniciando Laravel con PHP 8.3...
echo ========================================
set PHP83_PATH=C:\Users\Shirley Gutierrez\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.NTS.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe

cd /d "%~dp0"
start "Laravel PHP 8.3" cmd /k "cd /d \"%CD%\" && \"%PHP83_PATH%\php.exe\" artisan serve --host=127.0.0.1 --port=8000"

timeout /t 3 /nobreak >nul

echo.
echo ========================================
echo   Servidor iniciado!
echo ========================================
echo.
echo URL: http://127.0.0.1:8000
echo.
echo Presiona cualquier tecla para cerrar...
pause >nul



