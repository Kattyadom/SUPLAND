<?php
declare(strict_types=1);
require_once __DIR__ . '/funciones.php';
require_once __DIR__ . '/catalogo.php';
require_once __DIR__ . '/acciones.php';
require_once __DIR__ . '/vista.php';

$error = '';
try {
    db();
    rows('SELECT id_usuario FROM usuarios LIMIT 1');
} catch (Throwable $exception) {
    http_response_code(503);
    header_view('Conexión pendiente', null);
    echo '<h1>Conexión pendiente</h1><p>Inicia MySQL, importa sql/supland.sql y revisa php/config.php.</p>';
    footer_view();
    exit;
}

try {
    process_action();
} catch (PDOException $exception) {
    $error = 'No se pudo guardar la operación. Inténtalo de nuevo.';
} catch (RuntimeException $exception) {
    $error = $exception->getMessage();
}

$account = user();
$adminPages = ['admin', 'agregar_producto', 'editar_producto', 'ventas', 'usuarios'];
if (in_array($page, $adminPages, true)) {
    $account = require_role('administrador');
}
if (in_array($page, ['comprar', 'mis_compras'], true)) {
    $account = require_role('cliente');
}
$titles = [
    'index' => 'Juguetes para imaginar', 'productos' => 'Catálogo',
    'registro' => 'Crear cuenta', 'login' => 'Iniciar sesión',
    'admin' => 'Administración', 'agregar_producto' => 'Agregar producto',
    'editar_producto' => 'Modificar producto', 'ventas' => 'Ventas',
    'usuarios' => 'Usuarios registrados', 'comprar' => 'Tu bolsa',
    'mis_compras' => 'Mis compras', 'logout' => 'Cerrar sesión',
    'configurar' => 'Crear administrador',
];
