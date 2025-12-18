# 🔧 Solución para Driver PostgreSQL PHP 8.4

## Problema
El driver `pdo_pgsql` está compilado para PHP 8.3 pero tienes PHP 8.4 instalado.

## Solución: Instalar Driver para PHP 8.4

### Opción 1: Descargar DLLs desde PECL (Recomendado)

1. **Visita**: https://pecl.php.net/package/pdo_pgsql
2. **Descarga** la versión más reciente para PHP 8.4 (Thread Safe - TS)
3. **Extrae** los archivos:
   - `php_pdo_pgsql.dll`
   - `php_pgsql.dll` (si está incluido)

4. **Copia los DLLs** a la carpeta de extensiones de PHP:
   ```
   C:\Users\Shirley Gutierrez\.config\herd-lite\bin\ext\
   ```

5. **Edita** el archivo `php.ini`:
   ```
   C:\Users\Shirley Gutierrez\.config\herd-lite\bin\php.ini
   ```

6. **Descomenta o agrega** estas líneas:
   ```ini
   extension=pgsql
   extension=pdo_pgsql
   ```

7. **Reinicia** el servidor PHP/Laravel

### Opción 2: Usar PHP 8.3 (Alternativa)

Si no encuentras los DLLs para PHP 8.4, puedes cambiar a PHP 8.3:

1. **Descarga PHP 8.3** desde: https://windows.php.net/download/
2. **Instala** los drivers de PostgreSQL para PHP 8.3
3. **Configura** Herd Lite para usar PHP 8.3

### Opción 3: Compilar desde fuente (Avanzado)

Si tienes Visual Studio y las herramientas de compilación, puedes compilar el driver desde el código fuente.

---

## Verificación

Después de instalar, verifica con:

```bash
php -m | grep pdo_pgsql
```

Deberías ver `pdo_pgsql` en la lista.

---

## Nota Importante

Las extensiones que comenté temporalmente están en:
- Línea 9: `;extension=pgsql`
- Línea 10: `;extension=pdo_pgsql`

Una vez que instales los DLLs correctos, descomenta estas líneas.



