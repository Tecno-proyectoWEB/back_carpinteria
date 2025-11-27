# 🚀 Cómo Iniciar el Proyecto

## ✅ Servidor Backend (Ya Iniciado)

El servidor Laravel está corriendo en:
- **URL:** http://127.0.0.1:8000
- **PHP:** 8.3.28 con extensiones PostgreSQL habilitadas

## 📦 Compilar Frontend (Terminal Nueva)

Abre una **nueva terminal** y ejecuta:

```bash
# 1. Ir al directorio del proyecto
cd "C:\Users\Shirley Gutierrez\Documents\tecno\proeyectogruplaWEB\2do parcial\back_carpinteria"

# 2. Compilar frontend (modo desarrollo)
npm run dev
```

Esto compilará los assets de Vue.js y Tailwind CSS.

## 🌐 Acceder a la Aplicación

Una vez que `npm run dev` esté corriendo:

1. Abre tu navegador
2. Ve a: **http://127.0.0.1:8000**
3. Deberías ver la aplicación funcionando

## 🛑 Detener Servidores

### Detener Backend (Laravel):
- Presiona `Ctrl + C` en la terminal donde corre `php artisan serve`

### Detener Frontend (Vite):
- Presiona `Ctrl + C` en la terminal donde corre `npm run dev`

## 📝 Comandos Completos

### Terminal 1 - Backend:
```bash
export PATH="/c/Program Files/PostgreSQL/17/bin:$PATH"
cd "C:\Users\Shirley Gutierrez\Documents\tecno\proeyectogruplaWEB\2do parcial\back_carpinteria"
"C:/Users/Shirley Gutierrez/AppData/Local/Microsoft/WinGet/Packages/PHP.PHP.NTS.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe/php.exe" artisan serve
```

### Terminal 2 - Frontend:
```bash
cd "C:\Users\Shirley Gutierrez\Documents\tecno\proeyectogruplaWEB\2do parcial\back_carpinteria"
npm run dev
```

## ⚙️ Configuración Permanente (Opcional)

Para no tener que escribir las rutas completas cada vez:

1. **Agrega al PATH del sistema:**
   - `C:\Program Files\PostgreSQL\17\bin`
   - `C:\Users\Shirley Gutierrez\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.NTS.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe`

2. **Luego puedes usar simplemente:**
   ```bash
   php artisan serve
   npm run dev
   ```

## ✅ Verificación

- ✅ Backend corriendo en http://127.0.0.1:8000
- ⏳ Frontend: Ejecuta `npm run dev` en otra terminal
- ✅ Base de datos PostgreSQL conectada
- ✅ Extensiones habilitadas

---

**¡El servidor backend ya está corriendo!** Solo falta compilar el frontend en otra terminal.

