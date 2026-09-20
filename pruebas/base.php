<?php
// Solo se ejecuta desde la terminal y sobre una base separada de la tienda.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
$name = getenv('SUPLAND_DB_NAME');
if (!preg_match('/^supland_prueba_[0-9]+$/', $name ?: '')) {
    exit('Nombre de base de pruebas inválido.');
}
require __DIR__ . '/../php/conexion.php';
if (($argv[1] ?? '') === 'crear') {
    $config = require __DIR__ . '/../php/config.php';
    $pdo = new PDO("mysql:host={$config['host']};port={$config['port']};charset=utf8mb4", $config['user'], $config['password']);
    $sql = file_get_contents(__DIR__ . '/../sql/supland.sql');
    $sql = str_replace(['DATABASE IF NOT EXISTS supland', 'USE supland;'], ["DATABASE IF NOT EXISTS $name", "USE $name;"], $sql);
    $pdo->exec($sql);
    echo 'Base de pruebas creada';
} elseif (($argv[1] ?? '') === 'comprobar') {
    $sale = db()->query('SELECT total FROM ventas')->fetchAll(PDO::FETCH_COLUMN);
    $details = db()->query('SELECT SUM(cantidad) FROM detalle_venta')->fetchColumn();
    $stock = db()->query('SELECT cantidad FROM productos WHERE id_producto=1')->fetchColumn();
    $secondStock = db()->query('SELECT cantidad FROM productos WHERE id_producto=2')->fetchColumn();
    if ($sale !== ['45.00'] || (int) $details !== 3 || (int) $stock !== 23 || (int) $secondStock !== 19) {
        throw new RuntimeException('Venta, detalles o stock incorrectos.');
    }
    echo 'Venta de $45.00, 3 unidades de dos productos y stocks finales 23 y 19: correctos';
} elseif (($argv[1] ?? '') === 'borrar') {
    db()->exec("DROP DATABASE `$name`");
    echo 'Base temporal eliminada';
}
