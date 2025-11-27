# Implementación de PagoFácil QR

## Configuración en .env

Agregar las siguientes variables al archivo `.env`:

```env
# PagoFácil Credenciales
PAGO_FACIL_TCTOKEN_SERVICE=tu_token_service
PAGO_FACIL_TCTOKEN_SECRET=tu_token_secret
PAGO_FACIL_CLIENT_CODE=tu_client_code
PAGO_FACIL_PAYMENT_METHOD_ID=4
PAGO_FACIL_CALLBACK_URL=http://tu-dominio.com/payment/callback
```

## Características Implementadas

### 1. Sistema de Pagos
- ✅ **Efectivo**: Pago inmediato, sin pasarela
- ✅ **QR PagoFácil**: Integración completa con PagoFácil

### 2. Flujo de Pago

#### Venta al Contado:
1. Usuario selecciona método de pago (Efectivo o QR)
2. Si es Efectivo: Pago se marca como PAGADO inmediatamente
3. Si es QR: Se genera QR con PagoFácil y se muestra al cliente
4. Cliente escanea QR y paga
5. PagoFácil envía callback a `/payment/callback`
6. Sistema actualiza estado del pago automáticamente

#### Venta a Crédito:
1. Se crean las cuotas según número especificado
2. Si método es QR: Se genera QR para la primera cuota
3. Cliente paga primera cuota escaneando QR
4. Cuando todas las cuotas están pagadas, el pedido se confirma

### 3. Componentes Vue

- **QRPayment.vue**: Muestra el código QR y permite verificar estado
- **PaymentModal.vue**: Modal con información detallada del estado del pago
- **Show.vue (Pedidos)**: Muestra QR si existe pago pendiente

### 4. Servicios

- **PaymentGatewayService**: Maneja toda la comunicación con PagoFácil
  - Autenticación automática
  - Generación de QR
  - Consulta de estado
  - Confirmación de pagos

### 5. Callback

- Ruta: `POST /payment/callback`
- Recibe notificaciones de PagoFácil cuando se completa un pago
- Actualiza automáticamente el estado del pago en la base de datos

## Migraciones

Ejecutar las migraciones:

```bash
php artisan migrate
```

Esto creará:
- Campos adicionales en tabla `pago` (nro_pago, nro_transaccion, qr_image, etc.)
- Campos adicionales en tabla `metodo_pago` (es_electronico, tipo_electronico, etc.)

## Seeders

Actualizar métodos de pago:

```bash
php artisan db:seed --class=MetodoPagoSeeder
```

Esto creará:
- EFECTIVO (no electrónico)
- QR PagoFácil (electrónico, tipo QR)

## Uso

1. Configurar credenciales en `.env`
2. Ejecutar migraciones y seeders
3. Crear una venta seleccionando "QR PagoFácil" como método de pago
4. El sistema generará automáticamente el QR
5. El cliente escanea y paga
6. El sistema recibirá el callback y actualizará el estado

## Notas Importantes

- El callback debe ser accesible públicamente (no requiere autenticación)
- El callback responde con formato JSON específico según documentación de PagoFácil
- Los tokens de acceso se cachean automáticamente para evitar múltiples autenticaciones
- El sistema consulta automáticamente el estado cada 10 segundos si el pago está pendiente

