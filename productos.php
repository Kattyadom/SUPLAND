<?php
// Esta página muestra el catálogo y sus filtros.
$page = 'productos';
require __DIR__ . '/php/preparar.php';
header_view($titles[$page], $account);
if ($error !== '') {
    echo '<p class="notice" role="alert">' . h($error) . '</p>';
}
?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <h1>Todos nuestros juguetes</h1>
        <?php if ($account && $account['tipo_usuario'] === 'cliente'): ?>
<a href="comprar.php">Bolsa (<?= array_sum($_SESSION['carrito']) ?>)</a><?php endif; ?>
    </div>
    <?php if (isset($_GET['agregado'])): ?>
<p role="status">Producto agregado a la bolsa.</p><?php endif; ?>
    <?php $filters = catalog_filters($_GET); $products = catalog_products($filters); ?>
<form action="productos.php" method="get" class="row g-3 mb-4 catalog-filters" role="search">
    <label class="col-md-6">Buscar juguetes<input name="q" class="form-control" type="search" maxlength="200" placeholder="Nombre, descripción o categoría" value="<?= h($filters['q']) ?>"></label>
    <label class="col-md-6">Categoría<select name="categoria" class="form-select">
<option value="">Todas las categorías</option>
        <?php foreach (navigation_categories() as $category): ?>
<option value="<?= $category['id_categoria'] ?>" <?= (string) $category['id_categoria'] === $filters['categoria'] ? 'selected' : '' ?>><?= h($category['nombre_categoria']) ?></option><?php endforeach; ?>
    </select></label>
    <label class="col-sm-6 col-lg-3">Precio mínimo ($)<input name="min" class="form-control" type="number" min="0" max="999999.99" step="0.01" placeholder="0.00" value="<?= h($filters['min']) ?>"></label>
    <label class="col-sm-6 col-lg-3">Precio máximo ($)<input name="max" class="form-control" type="number" min="0" max="999999.99" step="0.01" placeholder="Sin límite" value="<?= h($filters['max']) ?>"></label>
    <label class="col-lg-6">Ordenar por<select name="orden" class="form-select">
        <?php foreach (['recientes' => 'Más recientes', 'precio_asc' => 'Precio: menor a mayor', 'precio_desc' => 'Precio: mayor a menor', 'nombre' => 'Nombre: A–Z'] as $value => $label): ?>
<option value="<?= $value ?>" <?= $filters['orden'] === $value ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?>
    </select></label>
    <div class="col-12 d-flex align-items-center flex-wrap gap-3">
        <label class="d-flex align-items-center gap-2">
<input name="stock" type="checkbox" value="1" <?= $filters['stock'] ? 'checked' : '' ?>>Solo disponibles</label>
        <button class="btn action" type="submit">Buscar</button>
<a class="btn secondary" href="productos.php">Limpiar filtros</a>
    </div>
</form>
<p role="status"><?= count($products) ?> <?= count($products) === 1 ? 'juguete encontrado' : 'juguetes encontrados' ?><?= $filters['q'] !== '' ? ' para «' . h($filters['q']) . '»' : '' ?>.</p>
<?php
if (!$products) { echo '<p>No se encontraron productos. Prueba otra búsqueda o limpia los filtros.</p>'; }
product_cards($products, $account);

?>
<?php footer_view(); ?>
