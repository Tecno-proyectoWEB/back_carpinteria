<?php

try {
    $pdo = new PDO('pgsql:host=127.0.0.1;port=5432;dbname=db_carpinteriajorge_tecno', 'postgres', 'admin123');
    echo 'Conexión a la base de datos exitosa.';
} catch (Exception $e) {
    echo 'Error de conexión: ' . $e->getMessage();
}
