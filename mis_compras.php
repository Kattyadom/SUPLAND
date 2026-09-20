<?php
// Esta página muestra mis compras.
$page = 'mis_compras';
require __DIR__ . '/php/preparar.php';
header_view($titles[$page], $account);
if ($error !== '') {
    echo '<p class="notice" role="alert">' . h($error) . '</p>';
}
?>
<h1><?= h($titles[$page]) ?></h1>
    <?php if (isset($_GET['confirmada'])): ?>
<p role="status">Compra simulada registrada. No se ha realizado ningún cobro.</p><?php endif; ?>
    <?php
    $sales = rows('SELECT v.*,u.nombre FROM ventas v JOIN usuarios u USING(id_usuario) WHERE v.id_usuario=? ORDER BY v.id_venta DESC', [$account['id_usuario']]);
    ?>
    <?php if (!$sales): ?>
<p>No hay compras registradas.</p><?php endif; ?>
    <?php foreach ($sales as $sale): ?>
        <details class="sale-record"><summary>SUP-<?= $sale['id_venta'] ?> · <?= h($sale['fecha']) ?> · <?= money($sale['total']) ?><?= $page === 'ventas' ? ' · ' . h($sale['nombre']) : '' ?></summary>
            <div class="table-responsive">
<table class="table">
<thead>
<tr>
<th>Producto</th>
<th>Cantidad</th>
<th>Precio</th>
<th>Subtotal</th></tr></thead>
<tbody>
            <?php foreach (rows('SELECT * FROM detalle_venta WHERE id_venta=?', [$sale['id_venta']]) as $detail): ?>
<tr>
<td><?= h($detail['nombre_producto']) ?></td>
<td><?= $detail['cantidad'] ?></td>
<td><?= money($detail['precio']) ?></td>
<td><?= money(cents($detail['precio']) * $detail['cantidad'] / 100) ?></td></tr><?php endforeach; ?>
            </tbody></table></div>
        </details>
    <?php endforeach; ?>
<?php footer_view(); ?>
