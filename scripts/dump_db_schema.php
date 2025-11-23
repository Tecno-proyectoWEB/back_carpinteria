<?php
// Dump DB schema to JSON. Reads .env for DB config.
$envPath = __DIR__ . '/../.env';
$env = [];
if (!file_exists($envPath)) {
    echo json_encode(["error" => " .env not found at $envPath"]);
    exit(1);
}
$lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    if (!strpos($line, '=')) continue;
    [$k, $v] = explode('=', $line, 2);
    $v = trim($v, " \t\n\r\0\x0B\"'");
    $env[trim($k)] = $v;
}
$dbConn = $env['DB_CONNECTION'] ?? 'pgsql';
$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? '5432';
$dbname = $env['DB_DATABASE'] ?? null;
$user = $env['DB_USERNAME'] ?? null;
$pass = $env['DB_PASSWORD'] ?? null;
if (!$dbname) {
    echo json_encode(["error"=>"DB_DATABASE not set in .env"]);
    exit(1);
}
try {
    if ($dbConn === 'pgsql') {
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    } elseif ($dbConn === 'mysql') {
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8";
    } else {
        echo json_encode(["error"=>"Unsupported DB_CONNECTION: $dbConn"]);
        exit(1);
    }
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // get tables
    if ($dbConn === 'pgsql') {
        $tablesStmt = $pdo->prepare("SELECT table_name FROM information_schema.tables WHERE table_schema='public' AND table_type='BASE TABLE';");
    } else {
        $tablesStmt = $pdo->prepare("SELECT table_name FROM information_schema.tables WHERE table_schema=DATABASE() AND table_type='BASE TABLE';");
    }
    $tablesStmt->execute();
    $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);
    $schema = [];
    foreach ($tables as $table) {
        $colsStmt = $pdo->prepare("SELECT column_name, data_type, is_nullable, column_default, character_maximum_length, numeric_precision, numeric_scale FROM information_schema.columns WHERE table_schema='public' AND table_name=? ORDER BY ordinal_position");
        $colsStmt->execute([$table]);
        $columns = $colsStmt->fetchAll(PDO::FETCH_ASSOC);

        // primary keys
        $pkStmt = $pdo->prepare("SELECT kcu.column_name
FROM information_schema.table_constraints tc
JOIN information_schema.key_column_usage kcu
  ON tc.constraint_name = kcu.constraint_name
  AND tc.table_schema = kcu.table_schema
WHERE tc.table_schema='public' AND tc.table_name = ? AND tc.constraint_type = 'PRIMARY KEY'");
        $pkStmt->execute([$table]);
        $pks = $pkStmt->fetchAll(PDO::FETCH_COLUMN);

        // foreign keys
        $fkStmt = $pdo->prepare("SELECT kcu.column_name AS column_name,
       ccu.table_name AS foreign_table_name,
       ccu.column_name AS foreign_column_name,
       tc.constraint_name
FROM information_schema.table_constraints tc
JOIN information_schema.key_column_usage kcu
  ON tc.constraint_name = kcu.constraint_name
  AND tc.table_schema = kcu.table_schema
JOIN information_schema.constraint_column_usage ccu
  ON ccu.constraint_name = tc.constraint_name
WHERE tc.constraint_type = 'FOREIGN KEY' AND tc.table_schema='public' AND tc.table_name = ?");
        $fkStmt->execute([$table]);
        $fks = $fkStmt->fetchAll(PDO::FETCH_ASSOC);

        $schema[] = [
            'table' => $table,
            'columns' => $columns,
            'primary_keys' => $pks,
            'foreign_keys' => $fks
        ];
    }
    echo json_encode(['status'=>'ok','tables'=>$schema], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
    exit(1);
}
