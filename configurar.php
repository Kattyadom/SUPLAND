<?php
$page = 'configurar';
require __DIR__ . '/php/preparar.php';
header_view($titles[$page], $account);
if ($error !== '') {
    echo '<p class="notice" role="alert">' . h($error) . '</p>';
}
?>
<?php $configured = (bool) rows("SELECT id_usuario FROM usuarios WHERE tipo_usuario='administrador' LIMIT 1"); ?>
    <?php if ($configured || !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1','::1'], true)): ?>
        <h1>Configuración no disponible</h1>
<a href="login.php">Iniciar sesión</a>
    <?php else: ?>
        <section class="account-form">
            <h1><?= h($titles[$page]) ?></h1>
            <form method="post" class="vstack gap-3">
                <?= csrf() ?>
                <input type="hidden" name="accion" value="<?= h($page) ?>">
                <label>Nombre<input class="form-control" name="nombre" maxlength="100" autocomplete="name" required value="<?= h($_POST['nombre'] ?? '') ?>"></label>

                <label>Correo electrónico<input class="form-control" name="correo" type="email" maxlength="190" autocomplete="username" required value="<?= h($_POST['correo'] ?? '') ?>"></label>
                <label>Contraseña<input class="form-control" name="contrasena" type="password" minlength="8" maxlength="72" required autocomplete="new-password"></label>
                <label>Confirmar contraseña<input class="form-control" name="confirmar" type="password" required autocomplete="new-password"></label>

                <button class="btn action">Crear administrador</button>
            </form>
            <p class="mt-3">
<a href="login.php">Ya tengo cuenta</a></p>
        </section>
    <?php endif; ?>
<?php footer_view(); ?>
