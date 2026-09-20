<?php
$page = 'login';
require __DIR__ . '/php/preparar.php';
header_view('Iniciar sesión', $account);
$configured = rows("SELECT id_usuario FROM usuarios WHERE tipo_usuario='administrador' LIMIT 1");
?>
<section class="account-form">
    <h1>Iniciar sesión</h1>
    <?php if ($error): ?>
        <p class="notice" role="alert"><?= h($error) ?></p>
    <?php endif; ?>
    <form method="post" class="vstack gap-3">
        <?= csrf() ?>
        <input type="hidden" name="accion" value="login">
        <label>
            Correo electrónico
            <input class="form-control" name="correo" type="email" maxlength="190" autocomplete="username" required value="<?= h($_POST['correo'] ?? '') ?>">
        </label>
        <label>
            Contraseña
            <input class="form-control" name="contrasena" type="password" minlength="8" maxlength="72" autocomplete="current-password" required>
        </label>
        <button class="btn action" type="submit">Entrar</button>
    </form>
    <p class="mt-3"><a href="registro.php">Crear una cuenta de cliente</a></p>
    <?php if (!$configured): ?>
        <a href="configurar.php">Configurar administrador</a>
    <?php endif; ?>
</section>
<?php footer_view(); ?>
