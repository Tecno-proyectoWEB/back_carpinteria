-- =========================================
-- SCRIPT PARA HABILITAR QR DE PAGOFÁCIL
-- =========================================

-- 1. Actualizar el método de pago QR para que sea reconocido como electrónico
UPDATE metodo_pago 
SET 
    es_electronico = true,
    tipo_electronico = 'QR',
    activo = true,
    descripcion = 'Pago mediante código QR con PagoFácil'
WHERE nombre = 'QR';

-- 2. Verificar que se aplicó correctamente
SELECT 
    id, 
    nombre, 
    descripcion,
    es_electronico, 
    tipo_electronico, 
    activo 
FROM metodo_pago 
WHERE nombre = 'QR';

-- Resultado esperado:
-- id | nombre | descripcion                            | es_electronico | tipo_electronico | activo
-- ---|--------|----------------------------------------|----------------|------------------|--------
-- 5  | QR     | Pago mediante código QR con PagoFácil  | t              | QR               | t

-- =========================================
-- OPCIONAL: Si el método QR no existe, créalo
-- =========================================

-- Descomentar y ejecutar SOLO si la consulta anterior no devuelve resultados:

-- INSERT INTO metodo_pago (nombre, descripcion, es_electronico, tipo_electronico, activo)
-- VALUES ('QR', 'Pago mediante código QR con PagoFácil', true, 'QR', true);
