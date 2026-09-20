<?php
// Esta página muestra comprar.
$page = 'comprar';
require __DIR__ . '/php/preparar.php';
header_view($titles[$page], $account);
if ($error !== '') {
    echo '<p class="notice" role="alert">' . h($error) . '</p>';
}
?>
<?php $_SESSION['compra_clave'] ??= bin2hex(random_bytes(32)); $items = cart(); $sum = 0; ?>
    <h1>Tu bolsa</h1>
    <?php if (!$items): ?>
<p>Tu bolsa está vacía.</p>
<a href="productos.php">Explorar juguetes</a><?php else: ?>
        <div class="table-responsive mt-4">
<table class="table">
<thead>
<tr>
<th>Producto</th>
<th>Precio</th>
<th>Cantidad</th>
<th>Subtotal</th></tr></thead>
<tbody>
        <?php foreach ($items as $item): $subtotal = cents($item['precio']) * $item['unidades']; $sum += $subtotal; ?>
            <tr>
<td><?= h($item['nombre_producto']) ?><?= !$item['activo'] ? ' (no disponible)' : '' ?></td>
<td><?= money($item['precio']) ?></td>
<td>
<form method="post" class="d-flex gap-2">
                <?= csrf() ?>
<input type="hidden" name="accion" value="cantidad">
<input type="hidden" name="id_producto" value="<?= $item['id_producto'] ?>">
                <input class="form-control quantity" type="number" name="cantidad" min="0" max="9999" value="<?= $item['unidades'] ?>" aria-label="Cantidad de <?= h($item['nombre_producto']) ?>">
                <button class="icon-button" title="Actualizar cantidad" aria-label="Actualizar cantidad"><i data-lucide="refresh-cw"></i></button>
            </form></td>
<td><?= money($subtotal / 100) ?></td></tr>
        <?php endforeach; ?>
        </tbody></table></div>
        <p class="fs-4">Total: <strong><?= money($sum / 100) ?></strong></p>
        <form method="post"><?= csrf() ?>
<input type="hidden" name="accion" value="comprar">
<input type="hidden" name="clave" value="<?= h($_SESSION['compra_clave']) ?>">
<button class="btn action">Confirmar compra simulada</button></form>
    <?php endif; ?>
<?php footer_view(); ?>
