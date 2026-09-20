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
header_view($titles[$page] ?? 'Supland', $account);
if ($error) {
    echo '<p class="notice" role="alert">' . h($error) . '</p>';
}
?>
<?php if ($page === 'index'): ?>
    <?php require __DIR__ . '/inicio.php'; ?>
<?php elseif (in_array($page, ['registro','login','configurar'], true)): ?>
    <?php $configured = (bool) rows("SELECT id_usuario FROM usuarios WHERE tipo_usuario='administrador' LIMIT 1"); ?>
    <?php if ($page === 'configurar' && ($configured || !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1','::1'], true))): ?>
        <h1>Configuración no disponible</h1><a href="login.php">Iniciar sesión</a>
    <?php else: ?>
        <section class="account-form">
            <h1><?= h($titles[$page]) ?></h1>
            <form method="post" class="vstack gap-3">
                <?= csrf() ?>
                <input type="hidden" name="accion" value="<?= h($page) ?>">
                <?php if ($page !== 'login'): ?>
                    <label>Nombre<input class="form-control" name="nombre" maxlength="100" autocomplete="name" required value="<?= h($_POST['nombre'] ?? '') ?>"></label>
                <?php endif; ?>
                <label>Correo electrónico<input class="form-control" name="correo" type="email" maxlength="190" autocomplete="username" required value="<?= h($_POST['correo'] ?? '') ?>"></label>
                <label>Contraseña<input class="form-control" name="contrasena" type="password" minlength="8" maxlength="72" required autocomplete="<?= $page === 'login' ? 'current-password' : 'new-password' ?>"></label>
                <?php if ($page !== 'login'): ?>
                    <label>Confirmar contraseña<input class="form-control" name="confirmar" type="password" required autocomplete="new-password"></label>
                <?php endif; ?>
                <button class="btn action"><?= $page === 'login' ? 'Entrar' : 'Crear cuenta' ?></button>
            </form>
            <p class="mt-3"><a href="<?= $page === 'login' ? 'registro.php' : 'login.php' ?>"><?= $page === 'login' ? 'Crear una cuenta de cliente' : 'Ya tengo cuenta' ?></a></p>
            <?php if (!$configured && $page === 'login'): ?><a href="configurar.php">Configurar administrador</a><?php endif; ?>
        </section>
    <?php endif; ?>
<?php elseif ($page === 'productos'): ?>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <h1>Todos nuestros juguetes</h1>
        <?php if ($account && $account['tipo_usuario'] === 'cliente'): ?><a href="comprar.php">Bolsa (<?= array_sum($_SESSION['carrito']) ?>)</a><?php endif; ?>
    </div>
    <?php if (isset($_GET['agregado'])): ?><p role="status">Producto agregado a la bolsa.</p><?php endif; ?>
    <?php require __DIR__ . '/catalogo_vista.php'; ?>
<?php elseif ($page === 'admin'): ?>
    <div class="d-flex justify-content-between flex-wrap gap-3 mb-4"><h1>Administración de productos</h1><a class="btn action" href="agregar_producto.php">Agregar producto</a></div>
    <?php if (isset($_GET['guardado'])): ?><p role="status">Producto guardado.</p><?php endif; ?>
    <?php if (isset($_GET['eliminado'])): ?><p role="status">Producto retirado del catálogo. Su historial se conserva.</p><?php endif; ?>
    <div class="table-responsive"><table class="table"><thead><tr><th>Producto</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
        <?php foreach (rows('SELECT p.*,c.nombre_categoria FROM productos p JOIN categorias c USING(id_categoria) ORDER BY p.id_producto DESC') as $product): ?>
            <tr><td><?= h($product['nombre_producto']) ?></td><td><?= h($product['nombre_categoria']) ?></td><td><?= money($product['precio']) ?></td><td><?= (int) $product['cantidad'] ?></td><td><?= $product['activo'] ? 'Activo' : 'Retirado' ?></td><td>
                <div class="d-flex gap-2"><a class="icon-button" href="editar_producto.php?id=<?= $product['id_producto'] ?>" title="Modificar producto" aria-label="Modificar <?= h($product['nombre_producto']) ?>"><i data-lucide="pencil"></i></a>
                <?php if ($product['activo']): ?><form method="post" data-confirm="¿Retirar este producto del catálogo?">
                    <?= csrf() ?><input type="hidden" name="accion" value="eliminar_producto"><input type="hidden" name="id_producto" value="<?= $product['id_producto'] ?>">
                    <button class="icon-button" title="Eliminar producto" aria-label="Eliminar <?= h($product['nombre_producto']) ?>"><i data-lucide="trash-2"></i></button>
                </form><?php endif; ?></div>
            </td></tr>
        <?php endforeach; ?>
    </tbody></table></div>
<?php elseif ($page === 'agregar_producto' || $page === 'editar_producto'): ?>
    <?php
    $product = ['id_producto' => 0, 'nombre_producto' => '', 'descripcion' => '', 'precio' => '', 'cantidad' => 0, 'id_categoria' => 1, 'color' => 'sky', 'activo' => 1];
    if ($page === 'editar_producto') {
        $product = rows('SELECT * FROM productos WHERE id_producto=?', [(int) ($_GET['id'] ?? 0)])[0] ?? null;
    }
    if ($product && $error && ($_POST['accion'] ?? '') === 'guardar_producto') {
        foreach (['nombre_producto','descripcion','precio','cantidad','id_categoria','color'] as $field) { $product[$field] = is_scalar($_POST[$field] ?? '') ? ($_POST[$field] ?? '') : ''; }
        $product['activo'] = isset($_POST['activo']);
    }
    ?>
    <a href="admin.php">← Administración</a><h1 class="mt-3"><?= h($titles[$page]) ?></h1>
    <?php if (!$product): ?><p>El producto no existe.</p><?php else: ?>
        <form method="post" class="product-form vstack gap-3 mt-4">
            <?= csrf() ?><input type="hidden" name="accion" value="guardar_producto"><input type="hidden" name="id_producto" value="<?= (int) $product['id_producto'] ?>">
            <label>Nombre<input class="form-control" name="nombre_producto" required maxlength="100" value="<?= h($product['nombre_producto']) ?>"></label>
            <label>Descripción<textarea class="form-control" name="descripcion" rows="4" maxlength="1500" required><?= h($product['descripcion']) ?></textarea></label>
            <div class="row g-3"><label class="col-sm-6">Precio ($)<input class="form-control" name="precio" type="number" min="0.01" max="999999.99" step="0.01" required value="<?= h($product['precio']) ?>"></label><label class="col-sm-6">Stock<input class="form-control" name="cantidad" type="number" min="0" max="9999" required value="<?= h($product['cantidad']) ?>"></label></div>
            <label>Categoría<select class="form-select" name="id_categoria"><?php foreach (rows('SELECT * FROM categorias') as $category): ?><option value="<?= $category['id_categoria'] ?>" <?= $category['id_categoria'] == $product['id_categoria'] ? 'selected' : '' ?>><?= h($category['nombre_categoria']) ?></option><?php endforeach; ?></select></label>
            <fieldset><legend class="fs-6">Color</legend><div class="d-flex gap-3 flex-wrap"><?php foreach (['grass','sky','candy','crayon','sun','star'] as $color): ?><label class="swatch color-<?= $color ?>" title="<?= ucfirst($color) ?>"><input type="radio" name="color" value="<?= $color ?>" aria-label="<?= ucfirst($color) ?>" <?= $product['color'] === $color ? 'checked' : '' ?>></label><?php endforeach; ?></div></fieldset>
            <label class="d-flex gap-2"><input type="checkbox" name="activo" <?= $product['activo'] ? 'checked' : '' ?>>Visible en el catálogo</label>
            <div class="d-flex gap-3"><button class="btn action">Guardar producto</button><a class="btn secondary" href="admin.php">Cancelar</a></div>
        </form>
    <?php endif; ?>
<?php elseif ($page === 'usuarios'): ?>
    <h1>Usuarios registrados</h1><div class="table-responsive mt-4"><table class="table"><thead><tr><th>Nombre</th><th>Correo</th><th>Rol</th></tr></thead><tbody>
        <?php foreach (rows('SELECT nombre,correo,tipo_usuario FROM usuarios ORDER BY id_usuario DESC') as $registered): ?><tr><td><?= h($registered['nombre']) ?></td><td><?= h($registered['correo']) ?></td><td><?= h($registered['tipo_usuario']) ?></td></tr><?php endforeach; ?>
    </tbody></table></div>
<?php elseif ($page === 'comprar'): ?>
    <?php $_SESSION['compra_clave'] ??= bin2hex(random_bytes(32)); $items = cart(); $sum = 0; ?>
    <h1>Tu bolsa</h1>
    <?php if (!$items): ?><p>Tu bolsa está vacía.</p><a href="productos.php">Explorar juguetes</a><?php else: ?>
        <div class="table-responsive mt-4"><table class="table"><thead><tr><th>Producto</th><th>Precio</th><th>Cantidad</th><th>Subtotal</th></tr></thead><tbody>
        <?php foreach ($items as $item): $subtotal = cents($item['precio']) * $item['unidades']; $sum += $subtotal; ?>
            <tr><td><?= h($item['nombre_producto']) ?><?= !$item['activo'] ? ' (no disponible)' : '' ?></td><td><?= money($item['precio']) ?></td><td><form method="post" class="d-flex gap-2">
                <?= csrf() ?><input type="hidden" name="accion" value="cantidad"><input type="hidden" name="id_producto" value="<?= $item['id_producto'] ?>">
                <input class="form-control quantity" type="number" name="cantidad" min="0" max="9999" value="<?= $item['unidades'] ?>" aria-label="Cantidad de <?= h($item['nombre_producto']) ?>">
                <button class="icon-button" title="Actualizar cantidad" aria-label="Actualizar cantidad"><i data-lucide="refresh-cw"></i></button>
            </form></td><td><?= money($subtotal / 100) ?></td></tr>
        <?php endforeach; ?>
        </tbody></table></div>
        <p class="fs-4">Total: <strong><?= money($sum / 100) ?></strong></p>
        <form method="post"><?= csrf() ?><input type="hidden" name="accion" value="comprar"><input type="hidden" name="clave" value="<?= h($_SESSION['compra_clave']) ?>"><button class="btn action">Confirmar compra simulada</button></form>
    <?php endif; ?>
<?php elseif ($page === 'ventas' || $page === 'mis_compras'): ?>
    <h1><?= h($titles[$page]) ?></h1>
    <?php if (isset($_GET['confirmada'])): ?><p role="status">Compra simulada registrada. No se ha realizado ningún cobro.</p><?php endif; ?>
    <?php
    $sales = $page === 'ventas'
        ? rows('SELECT v.*,u.nombre FROM ventas v JOIN usuarios u USING(id_usuario) ORDER BY v.id_venta DESC')
        : rows('SELECT v.*,u.nombre FROM ventas v JOIN usuarios u USING(id_usuario) WHERE v.id_usuario=? ORDER BY v.id_venta DESC', [$account['id_usuario']]);
    ?>
    <?php if (!$sales): ?><p>No hay compras registradas.</p><?php endif; ?>
    <?php foreach ($sales as $sale): ?>
        <details class="sale-record"><summary>SUP-<?= $sale['id_venta'] ?> · <?= h($sale['fecha']) ?> · <?= money($sale['total']) ?><?= $page === 'ventas' ? ' · ' . h($sale['nombre']) : '' ?></summary>
            <div class="table-responsive"><table class="table"><thead><tr><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th></tr></thead><tbody>
            <?php foreach (rows('SELECT * FROM detalle_venta WHERE id_venta=?', [$sale['id_venta']]) as $detail): ?><tr><td><?= h($detail['nombre_producto']) ?></td><td><?= $detail['cantidad'] ?></td><td><?= money($detail['precio']) ?></td><td><?= money(cents($detail['precio']) * $detail['cantidad'] / 100) ?></td></tr><?php endforeach; ?>
            </tbody></table></div>
        </details>
    <?php endforeach; ?>
<?php elseif ($page === 'logout'): ?>
    <h1>Cerrar sesión</h1><form method="post"><?= csrf() ?><input type="hidden" name="accion" value="logout"><button class="btn action">Confirmar cierre de sesión</button></form>
<?php endif; ?>
<?php footer_view(); ?>
