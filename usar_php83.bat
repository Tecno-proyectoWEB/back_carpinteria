@echo off
REM Script para usar PHP 8.3 con PostgreSQL
set PHP83_PATH=C:\Users\Shirley Gutierrez\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.NTS.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe
set PATH=%PHP83_PATH%;%PATH%

echo Usando PHP 8.3...
%PHP83_PATH%\php.exe -v
echo.
echo Ejecutando: %*
%PHP83_PATH%\php.exe %*



