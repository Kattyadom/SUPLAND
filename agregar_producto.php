<?php
// Esta página muestra agregar producto.
$page = 'agregar_producto';
require __DIR__ . '/php/preparar.php';
header_view($titles[$page], $account);
if ($error !== '') {
    echo '<p class="notice" role="alert">' . h($error) . '</p>';
}
?>
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
    <a href="admin.php">← Administración</a>
<h1 class="mt-3"><?= h($titles[$page]) ?></h1>
    <?php if (!$product): ?>
<p>El producto no existe.</p><?php else: ?>
        <form method="post" class="product-form vstack gap-3 mt-4">
            <?= csrf() ?>
<input type="hidden" name="accion" value="guardar_producto">
<input type="hidden" name="id_producto" value="<?= (int) $product['id_producto'] ?>">
            <label>Nombre<input class="form-control" name="nombre_producto" required maxlength="100" value="<?= h($product['nombre_producto']) ?>"></label>
            <label>Descripción<textarea class="form-control" name="descripcion" rows="4" maxlength="1500" required><?= h($product['descripcion']) ?></textarea></label>
            <div class="row g-3">
<label class="col-sm-6">Precio ($)<input class="form-control" name="precio" type="number" min="0.01" max="999999.99" step="0.01" required value="<?= h($product['precio']) ?>"></label>
<label class="col-sm-6">Stock<input class="form-control" name="cantidad" type="number" min="0" max="9999" required value="<?= h($product['cantidad']) ?>"></label></div>
            <label>Categoría<select class="form-select" name="id_categoria"><?php foreach (rows('SELECT * FROM categorias') as $category): ?>
<option value="<?= $category['id_categoria'] ?>" <?= $category['id_categoria'] == $product['id_categoria'] ? 'selected' : '' ?>><?= h($category['nombre_categoria']) ?></option><?php endforeach; ?></select></label>
            <fieldset><legend class="fs-6">Color</legend>
<div class="d-flex gap-3 flex-wrap"><?php foreach (['grass','sky','candy','crayon','sun','star'] as $color): ?>
<label class="swatch color-<?= $color ?>" title="<?= ucfirst($color) ?>">
<input type="radio" name="color" value="<?= $color ?>" aria-label="<?= ucfirst($color) ?>" <?= $product['color'] === $color ? 'checked' : '' ?>></label><?php endforeach; ?></div></fieldset>
            <label class="d-flex gap-2">
<input type="checkbox" name="activo" <?= $product['activo'] ? 'checked' : '' ?>>Visible en el catálogo</label>
            <div class="d-flex gap-3">
<button class="btn action">Guardar producto</button>
<a class="btn secondary" href="admin.php">Cancelar</a></div>
        </form>
    <?php endif; ?>
<?php footer_view(); ?>
