<?php
// Esta página muestra logout.
$page = 'logout';
require __DIR__ . '/php/preparar.php';
header_view($titles[$page], $account);
if ($error !== '') {
    echo '<p class="notice" role="alert">' . h($error) . '</p>';
}
?>
<h1>Cerrar sesión</h1>
<form method="post"><?= csrf() ?>
<input type="hidden" name="accion" value="logout">
<button class="btn action">Confirmar cierre de sesión</button></form>
<?php footer_view(); ?>
