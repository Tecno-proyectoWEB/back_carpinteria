# 📋 PLAN DE APLICACIÓN DE CORRECCIONES

## 🎯 ESTADO ACTUAL DEL PROYECTO

### ❌ Pendiente de Aplicar:
1. **Renombrar Pedido → Venta** (Modelos, Controlador, Rutas, Vistas)
2. **Crear PaymentGatewayService** (app/Services/)
3. **Crear Componentes Vue** (QRPayment.vue, PaymentModal.vue)
4. **Crear Middleware ContarVisitas**
5. **Crear sistema de temas y accesibilidad**
6. **Crear BusquedaController**
7. **Aplicar 12 correcciones de errores específicos**
8. **Crear migraciones para PagoFácil y visitas**
9. **Crear PaymentController para callback**

## 📝 ORDEN DE EJECUCIÓN

### FASE 1: Renombrar Pedido → Venta (CRÍTICO)
- [ ] Renombrar modelo `Pedido.php` → `Venta.php`
- [ ] Renombrar modelo `DetallePedido.php` → `DetalleVenta.php`
- [ ] Renombrar controlador `PedidoController.php` → `VentaController.php`
- [ ] Actualizar todas las referencias en modelos relacionados
- [ ] Actualizar rutas `/pedidos` → `/ventas`
- [ ] Renombrar vistas `Pedidos/` → `Ventas/`
- [ ] Actualizar permisos `pedidos.*` → `ventas.*`

### FASE 2: Integración PagoFácil (CRÍTICO)
- [ ] Crear directorio `app/Services/`
- [ ] Crear `PaymentGatewayService.php` con todas las correcciones
- [ ] Crear directorio `resources/js/Components/`
- [ ] Crear `QRPayment.vue`
- [ ] Crear `PaymentModal.vue`
- [ ] Crear `PaymentController.php` para callback y consulta
- [ ] Crear migraciones para campos PagoFácil
- [ ] Actualizar modelo `Pago.php` con campos PagoFácil
- [ ] Actualizar modelo `MetodoPago.php` con campos electrónicos

### FASE 3: Sistema de Temas y Accesibilidad
- [ ] Crear `resources/css/themes.css`
- [ ] Crear `resources/js/composables/useTheme.js`
- [ ] Actualizar `Layout.vue` con selector de temas
- [ ] Actualizar `Dashboard.vue` con clases de legibilidad

### FASE 4: Contador de Visitas
- [ ] Crear migración para tabla `visita`
- [ ] Crear middleware `ContarVisitas.php`
- [ ] Registrar middleware en `Kernel.php`
- [ ] Actualizar `HandleInertiaRequests.php` para compartir contador
- [ ] Actualizar `Layout.vue` para mostrar contador en footer

### FASE 5: Búsqueda
- [ ] Crear `BusquedaController.php`
- [ ] Crear vista `Busqueda/Resultados.vue`
- [ ] Agregar ruta de búsqueda
- [ ] Actualizar `Layout.vue` con barra de búsqueda

### FASE 6: Aplicar Correcciones de Errores (12 correcciones)
- [ ] 5.1: Corregir `Auth::id()` en PagoController
- [ ] 5.2: Validación manual producto_id/servicio_id
- [ ] 5.3: Validación manual material_id
- [ ] 5.4: Mejorar `tienePermiso()` en Usuario y Rol
- [ ] 5.5: Ajustar middleware ContarVisitas
- [ ] 5.6: Serialización de permisos en RolController
- [ ] 5.7: Route model binding para roles
- [ ] 5.8: Variables CSS para botones
- [ ] 5.9: QR para todas las cuotas
- [ ] 5.10: Filtrado de métodos de pago
- [ ] 5.11: Optimizar consultas en Ventas/Show
- [ ] 5.12: Mejorar guardado de transactionId

### FASE 7: Actualizar Rutas y Configuración
- [ ] Actualizar `routes/web.php` con todas las rutas correctas
- [ ] Agregar rutas de PagoFácil (callback, status)
- [ ] Actualizar `AppServiceProvider.php` con route binding
- [ ] Verificar variables de entorno

---

## ⚠️ NOTA IMPORTANTE

Este es un trabajo extenso que requiere múltiples pasos. Se aplicarán todas las correcciones del historial de forma sistemática.

