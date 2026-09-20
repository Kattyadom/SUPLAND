<?php
// Esta página muestra usuarios.
$page = 'usuarios';
require __DIR__ . '/php/preparar.php';
header_view($titles[$page], $account);
if ($error !== '') {
    echo '<p class="notice" role="alert">' . h($error) . '</p>';
}
?>
<h1>Usuarios registrados</h1>
<div class="table-responsive mt-4">
<table class="table">
<thead>
<tr>
<th>Nombre</th>
<th>Correo</th>
<th>Rol</th></tr></thead>
<tbody>
        <?php foreach (rows('SELECT nombre,correo,tipo_usuario FROM usuarios ORDER BY id_usuario DESC') as $registered): ?>
<tr>
<td><?= h($registered['nombre']) ?></td>
<td><?= h($registered['correo']) ?></td>
<td><?= h($registered['tipo_usuario']) ?></td></tr><?php endforeach; ?>
    </tbody></table></div>
<?php footer_view(); ?>
