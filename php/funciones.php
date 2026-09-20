<?php
declare(strict_types=1);
require_once __DIR__ . '/conexion.php';

ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_start();
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
$_SESSION['carrito'] ??= [];

function h($value): string
{
    // Evita que el texto de un formulario se interprete como HTML.
    return htmlspecialchars(is_scalar($value) ? (string) $value : '', ENT_QUOTES, 'UTF-8');
}

function rows(string $sql, array $values = []): array
{
    // Los valores van separados del SQL para no concatenar datos del usuario.
    $query = db()->prepare($sql);
    $query->execute($values);
    return $query->fetchAll();
}

function execute(string $sql, array $values = []): void
{
    $query = db()->prepare($sql);
    $query->execute($values);
}

function user(): ?array
{
    if (empty($_SESSION['usuario'])) {
        return null;
    }
    return rows('SELECT id_usuario,nombre,correo,tipo_usuario FROM usuarios WHERE id_usuario=? AND activo=1', [$_SESSION['usuario']])[0] ?? null;
}

function redirect(string $page): void
{
    header('Location: ' . $page);
    exit;
}

function require_role(string $role): array
{
    $user = user();
    if (!$user) {
        redirect('login.php');
    }
    if ($user['tipo_usuario'] !== $role) {
        http_response_code(403);
        exit('No tienes permiso para acceder a esta página.');
    }
    return $user;
}

function csrf(): string
{
    return '<input type="hidden" name="csrf" value="' . h($_SESSION['csrf']) . '">';
}

function input(string $name, int $max = 200, bool $required = true): string
{
    $value = $_POST[$name] ?? '';
    if (!is_string($value) || strlen(trim($value)) > $max || ($required && trim($value) === '')) {
        throw new RuntimeException('Revisa el campo ' . $name . '.');
    }
    return trim($value);
}

function integer(string $name, int $min, int $max): int
{
    $value = filter_var($_POST[$name] ?? '', FILTER_VALIDATE_INT);
    if ($value === false || $value < $min || $value > $max) {
        throw new RuntimeException('Revisa el campo ' . $name . '.');
    }
    return $value;
}

function cents(string $value): int
{
    // Calculamos en centavos para evitar errores de redondeo en las compras.
    if (!preg_match('/^\d{1,6}(?:\.\d{1,2})?$/', $value)) {
        throw new RuntimeException('El precio debe ser positivo y tener hasta dos decimales.');
    }
    [$whole, $decimal] = array_pad(explode('.', $value), 2, '');
    return (int) $whole * 100 + (int) str_pad($decimal, 2, '0');
}

function money($value): string
{
    return '$' . number_format((float) $value, 2);
}

function cart(): array
{
    $items = [];
    foreach ($_SESSION['carrito'] as $id => $quantity) {
        $product = rows('SELECT * FROM productos WHERE id_producto=?', [$id])[0] ?? null;
        if ($product) {
            $product['unidades'] = $quantity;
            $items[] = $product;
        }
    }
    return $items;
}
