<?php
// Esta página muestra admin.
$page = 'admin';
require __DIR__ . '/php/preparar.php';
header_view($titles[$page], $account);
if ($error !== '') {
    echo '<p class="notice" role="alert">' . h($error) . '</p>';
}
?>
<div class="d-flex justify-content-between flex-wrap gap-3 mb-4">
<h1>Administración de productos</h1>
<a class="btn action" href="agregar_producto.php">Agregar producto</a></div>
    <?php if (isset($_GET['guardado'])): ?>
<p role="status">Producto guardado.</p><?php endif; ?>
    <?php if (isset($_GET['eliminado'])): ?>
<p role="status">Producto retirado del catálogo. Su historial se conserva.</p><?php endif; ?>
    <div class="table-responsive">
<table class="table">
<thead>
<tr>
<th>Producto</th>
<th>Categoría</th>
<th>Precio</th>
<th>Stock</th>
<th>Estado</th>
<th>Acciones</th></tr></thead>
<tbody>
        <?php foreach (rows('SELECT p.*,c.nombre_categoria FROM productos p JOIN categorias c USING(id_categoria) ORDER BY p.id_producto DESC') as $product): ?>
            <tr>
<td><?= h($product['nombre_producto']) ?></td>
<td><?= h($product['nombre_categoria']) ?></td>
<td><?= money($product['precio']) ?></td>
<td><?= (int) $product['cantidad'] ?></td>
<td><?= $product['activo'] ? 'Activo' : 'Retirado' ?></td>
<td>
                <div class="d-flex gap-2">
<a class="icon-button" href="editar_producto.php?id=<?= $product['id_producto'] ?>" title="Modificar producto" aria-label="Modificar <?= h($product['nombre_producto']) ?>"><i data-lucide="pencil"></i></a>
                <?php if ($product['activo']): ?>
<form method="post" data-confirm="¿Retirar este producto del catálogo?">
                    <?= csrf() ?>
<input type="hidden" name="accion" value="eliminar_producto">
<input type="hidden" name="id_producto" value="<?= $product['id_producto'] ?>">
                    <button class="icon-button" title="Eliminar producto" aria-label="Eliminar <?= h($product['nombre_producto']) ?>"><i data-lucide="trash-2"></i></button>
                </form><?php endif; ?></div>
            </td></tr>
        <?php endforeach; ?>
    </tbody></table></div>
<?php footer_view(); ?>
