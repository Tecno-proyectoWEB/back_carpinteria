# 🔧 SOLUCIÓN: QR de PagoFácil no se Genera en Ventas

## 📋 DIAGNÓSTICO DEL PROBLEMA

### El Problema
Cuando seleccionas "QR" como método de pago en ventas, **NO se genera el código QR** para PagoFácil.

### La Causa
El método de pago "QR" en la base de datos **NO tiene configurados** los campos necesarios:
- `es_electronico` = `true`
- `tipo_electronico` = `'QR'`

El código del sistema verifica estos campos para decidir si debe generar el QR:
```php
// VentaController.php línea 186 y 313
if ($metodoPago && $metodoPago->es_electronico && $metodoPago->tipo_electronico === 'QR') {
    // Aquí se genera el QR
}
```

Si estos campos no están configurados, el código **NO ejecuta** la generación del QR.

---

## ✅ SOLUCIÓN

### Opción 1: Ejecutar UPDATE SQL Directamente

Ejecuta este comando SQL en tu base de datos PostgreSQL:

```sql
UPDATE metodo_pago 
SET 
    es_electronico = true,
    tipo_electronico = 'QR',
    activo = true
WHERE nombre = 'QR';
```

### Opción 2: Usar el Seeder (Requiere arreglar PostgreSQL)

**Problema actual:** Tu instalación de PHP tiene un problema con el driver de PostgreSQL:
```
Warning: PHP Startup: pgsql: Unable to initialize module
Module compiled with module API=20230831
PHP compiled with module API=20240924
These options need to match
```

**Si arreglas PostgreSQL**, ejecuta:
```bash
php artisan db:seed --class=UpdateMetodoPagoQR
```

### Opción 3: Actualizar manualmente en DBeaver/pgAdmin

1. Abre DBeaver o pgAdmin
2. Conéctate a tu base de datos
3. Ejecuta la consulta SQL de la Opción 1

---

## 📝 VERIFICACIÓN

Después de aplicar la solución, verifica que el método de pago QR tenga estos valores:

```sql
SELECT id, nombre, es_electronico, tipo_electronico, activo 
FROM metodo_pago 
WHERE nombre = 'QR';
```

Resultado esperado:
```
id | nombre | es_electronico | tipo_electronico | activo
---|--------|----------------|------------------|--------
5  | QR     | true           | QR               | true
```

---

## 🧪 PRUEBA

Una vez aplicada la corrección:

1. Ve a **Ventas** → **Nueva Venta**
2. Selecciona un cliente
3. Agrega productos
4. Selecciona método de pago: **QR**
5. Completa la venta

**Resultado esperado:**
- Se creará la venta
- Se generará automáticamente un código QR
- Te redirigirá a la vista de la venta mostrando el QR
- Verás un mensaje: "Venta registrada. Escanea el código QR para pagar."

---

## 🔍 DETALLES TÉCNICOS

### Configuración Actual de PagoFácil

El sistema ya tiene configurado PagoFácil en `.env`:
```env
PAGO_FACIL_TCTOKEN_SERVICE=51247fae280c20410824977b0781453df59fad5b...
PAGO_FACIL_TCTOKEN_SECRET=0C351C6679844041AA31AF9C
PAGO_FACIL_CALLBACK_URL=https://www.tecnoweb.org.bo/payment/callback
PAGO_FACIL_PAYMENT_METHOD_ID=4
```

### Flujo de Generación de QR

1. **VentaController.php** (líneas 186-207 y 313-343)
   - Detecta si el método de pago es QR
   - Llama a `PaymentGatewayService::processQRPayment()`

2. **PaymentGatewayService.php** (líneas 194-300)
   - Se autentica con PagoFácil API
   - Genera el QR
   - Guarda la imagen en `storage/app/public/pagos/qr/`
   - Actualiza el pago con la URL del QR

3. **Frontend** (Ventas/Show.vue)
   - Muestra el QR usando el componente `QRPayment.vue`
   - Permite consultar el estado del pago

### Archivos Involucrados

- ✅ `app/Services/PaymentGatewayService.php` - Servicio de PagoFácil
- ✅ `app/Http/Controllers/VentaController.php` - Lógica de ventas
- ✅ `app/Http/Controllers/PagoController.php` - Lógica de pagos
- ✅ `resources/js/Components/QRPayment.vue` - Componente de QR
- ✅ `resources/js/Pages/Ventas/Show.vue` - Vista de venta con QR
- ⚠️ `database/seeders/MetodoPagoSeeder.php` - Falta configurar es_electronico
- ✅ `database/migrations/2025_11_27_052230_add_electronic_payment_fields_to_metodo_pago_table.php` - Campos agregados

---

## 🚨 PROBLEMA ADICIONAL: PostgreSQL Driver

Tu PHP tiene un problema con el driver de PostgreSQL:

### Síntomas:
```
Warning: PHP Startup: pgsql: Unable to initialize module
Module compiled with module API=20230831
PHP compiled with module API=20240924
```

### Causas posibles:
1. La extensión `pgsql.dll` fue compilada con una versión diferente de PHP
2. Estás usando PHP 8.3 pero la extensión es de PHP 8.2

### Solución:
1. Descarga las DLLs correctas para tu versión de PHP desde:
   - https://windows.php.net/downloads/pecl/releases/pgsql/
2. O reinstala PHP 8.3 completo
3. O usa la versión de PHP que coincida con las DLLs instaladas

---

## 📌 RESUMEN

**Problema:** El método de pago QR no tiene `es_electronico = true` configurado

**Solución:** Ejecutar el UPDATE SQL directamente en la base de datos

**Después de la corrección:** El QR se generará automáticamente al crear ventas con método de pago QR
