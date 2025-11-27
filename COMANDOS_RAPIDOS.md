# ⚡ Comandos Rápidos para el Proyecto

## 🚀 Iniciar Proyecto

### Terminal 1 - Backend (Ya iniciado ✅):
```bash
export PATH="/c/Program Files/PostgreSQL/17/bin:$PATH"
"C:/Users/Shirley Gutierrez/AppData/Local/Microsoft/WinGet/Packages/PHP.PHP.NTS.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe/php.exe" artisan serve
```

### Terminal 2 - Frontend (Ejecuta esto ahora):
```bash
cd "C:\Users\Shirley Gutierrez\Documents\tecno\proeyectogruplaWEB\2do parcial\back_carpinteria"
npm run dev
```

## 🌐 Acceso

- **URL:** http://127.0.0.1:8000
- **Backend:** ✅ Corriendo
- **Frontend:** ⏳ Ejecuta `npm run dev`

## 🛠️ Comandos Útiles

### Base de Datos:
```bash
# Ver estado de migraciones
php artisan migrate:status

# Ejecutar migraciones
php artisan migrate

# Ejecutar seeders
php artisan db:seed

# Resetear base de datos
php artisan migrate:fresh --seed
```

### Desarrollo:
```bash
# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Generar clave de aplicación
php artisan key:generate
```

### Frontend:
```bash
# Desarrollo (hot reload)
npm run dev

# Producción (compilar)
npm run build

# Verificar
npm run build && npm run preview
```

## 📝 Notas

- **PHP 8.3** está configurado con extensiones PostgreSQL
- **PostgreSQL** debe estar en el PATH para que funcione
- El servidor usa **PHP 8.3** desde la ruta completa
- Frontend usa **Vite** para compilación en tiempo real

---

**¡Abre otra terminal y ejecuta `npm run dev` para compilar el frontend!**

