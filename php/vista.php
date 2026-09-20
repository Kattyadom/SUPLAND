<?php
declare(strict_types=1);

function header_view(string $title, ?array $account): void
{
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($title) ?> | Supland</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/proyecto.css">
</head>
<body>
    <div class="promo">✦ ENVÍO GRATIS EN PEDIDOS DE $50 ✦ <span>La diversión va por nuestra cuenta</span></div>
    <header>
        <a class="logo" href="index.php" aria-label="Supland, inicio"><i>S</i>upland<b>.</b></a>
        <button class="hamb" type="button" aria-label="Abrir menú" aria-expanded="false" aria-controls="navigation">☰</button>
        <nav id="navigation" aria-label="Navegación principal">
            <div class="drop">
                <a href="productos.php">Catálogo⌄</a>
                <div class="mega">
                    <a href="productos.php?categoria=1">🧩 Educativos</a>
                    <a href="productos.php?categoria=2">🎨 Creativos</a>
                    <a href="productos.php?categoria=3">🚀 Aventura</a>
                    <a href="productos.php?categoria=4">🧸 Peluches</a>
                </div>
            </div>
            <a href="index.php#nosotros">Sobre nosotros</a>
            <?php if (!$account): ?>
                <a href="registro.php">Crear cuenta</a>
                <a class="delivery" href="login.php">Iniciar sesión</a>
            <?php elseif ($account['tipo_usuario'] === 'administrador'): ?>
                <a href="admin.php">Administración</a>
                <a href="ventas.php">Ventas</a>
                <a href="usuarios.php">Usuarios</a>
            <?php else: ?>
                <a href="mis_compras.php">Mis compras</a>
                <a class="delivery" href="comprar.php">Delivery ↗</a>
            <?php endif; ?>
        </nav>
        <?php if ($account): ?>
            <form action="logout.php" method="post" class="session-action">
                <?= csrf() ?>
                <input type="hidden" name="accion" value="logout">
                <button class="bag" title="Cerrar sesión" type="submit"><i data-lucide="log-out"></i><span class="visually-hidden">Cerrar sesión</span></button>
            </form>
        <?php endif; ?>
    </header>
    <main class="container py-4">
<?php
}

function footer_view(): void
{
?>
    </main>
    <footer class="sitefooter">
        <div class="footgrid">
            <div>
                <a class="logo light" href="index.php"><i>S</i>upland<b>.</b></a>
                <p>Grandes aventuras para pequeñas personas. Juguetes que inspiran, delivery que emociona.</p>
                <div class="social">◎　♪　f</div>
            </div>
            <div>
                <h3>Descubre</h3>
                <a href="productos.php">Catálogo completo</a>
                <a href="productos.php?categoria=2">Novedades</a>
                <a href="productos.php?categoria=1">Aprender jugando</a>
                <a href="productos.php">Ideas para regalar</a>
            </div>
            <div>
                <h3>Te ayudamos</h3>
                <a href="mis_compras.php">Mis compras</a>
                <a href="index.php#nosotros">Sobre nosotros</a>
                <a href="mailto:hola@supland.com">hola@supland.com</a>
            </div>
            <div class="news">
                <b>💌 Magia en tu buzón</b>
                <p>Novedades, juegos y sorpresas. Sin correos aburridos.</p>
                <form action="mailto:hola@supland.com" method="get">
                    <input name="subject" required placeholder="Escríbenos" aria-label="Asunto del correo">
                    <button type="submit" aria-label="Abrir correo">→</button>
                </form>
            </div>
        </div>
        <div class="perks">✓ Pago seguro　 ↻ Cambios fáciles　 ♻ Empaque responsable　 ☎ Atención con humanos</div>
        <div class="copy">© <?= date('Y') ?> Supland · Hecho con alegría y un poquito de magia</div>
    </footer>
    <script src="js/lucide.js"></script>
    <script src="js/proyecto.js"></script>
</body>
</html>
<?php
}

function product_cards(array $products, ?array $account): void
{
    echo '<div class="row g-4">';
    foreach ($products as $product) {
        $icon = [1 => '🧩', 2 => '🎨', 3 => '🚀', 4 => '🧸'][$product['id_categoria']] ?? '🎁';
        ?>
        <div class="col-12 col-sm-6 col-lg-4">
            <article class="toy-card h-100">
                <div class="toy-image color-<?= h($product['color']) ?>" role="img" aria-label="<?= h($product['nombre_categoria']) ?>"><?= $icon ?></div>
                <div class="p-4 d-flex flex-column flex-grow-1">
                    <small><?= h($product['nombre_categoria']) ?></small>
                    <h2 class="h4 mt-2"><?= h($product['nombre_producto']) ?></h2>
                    <p class="flex-grow-1"><?= h($product['descripcion']) ?></p>
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <strong><?= money($product['precio']) ?></strong>
                        <?php if ($account && $account['tipo_usuario'] === 'cliente' && $product['cantidad'] > 0): ?>
                            <form method="post">
                                <?= csrf() ?>
                                <input type="hidden" name="accion" value="agregar">
                                <input type="hidden" name="id_producto" value="<?= (int) $product['id_producto'] ?>">
                                <button class="btn action" title="Agregar a la bolsa" aria-label="Agregar <?= h($product['nombre_producto']) ?>"><i data-lucide="plus"></i></button>
                            </form>
                        <?php elseif ($product['cantidad'] < 1): ?>
                            <span>Agotado</span>
                        <?php elseif (!$account): ?>
                            <a href="login.php">Inicia sesión para comprar</a>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        </div>
        <?php
    }
    echo '</div>';
}
