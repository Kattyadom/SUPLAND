<?php
$page = 'registro';
require __DIR__ . '/php/preparar.php';
header_view('Crear cuenta', $account);
?>
<section class="account-form">
    <h1>Crear cuenta</h1>
    <?php if ($error): ?>
        <p class="notice" role="alert"><?= h($error) ?></p>
    <?php endif; ?>
    <form method="post" class="vstack gap-3">
        <?= csrf() ?>
        <input type="hidden" name="accion" value="registro">
        <label>
            Nombre
            <input class="form-control" name="nombre" maxlength="100" autocomplete="name" required value="<?= h($_POST['nombre'] ?? '') ?>">
        </label>
        <label>
            Correo electrónico
            <input class="form-control" name="correo" type="email" maxlength="190" autocomplete="username" required value="<?= h($_POST['correo'] ?? '') ?>">
        </label>
        <label>
            Contraseña
            <input class="form-control" name="contrasena" type="password" minlength="8" maxlength="72" autocomplete="new-password" required>
        </label>
        <label>
            Confirmar contraseña
            <input class="form-control" name="confirmar" type="password" maxlength="72" autocomplete="new-password" required>
        </label>
        <button class="btn action" type="submit">Crear cuenta</button>
    </form>
    <p class="mt-3"><a href="login.php">Ya tengo cuenta</a></p>
</section>
<?php footer_view(); ?>
