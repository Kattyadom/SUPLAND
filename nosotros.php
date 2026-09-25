<?php
$page = 'nosotros';
require __DIR__ . '/php/preparar.php';
header_view($titles[$page], $account);
?>
<section class="about-intro" aria-labelledby="about-title">
    <div>
        <p class="eyebrow">CONOCE SUPLAND</p>
        <h1 id="about-title">Sobre nosotros</h1>
        <p class="about-lead">Un espacio para jugar, descubrir e imaginar.</p>
        <p>En Supland nos gustan los juguetes que se convierten en el comienzo de una aventura: construir un cohete, resolver un rompecabezas o inventar una historia con un nuevo compañero de peluche.</p>
        <a class="btn action" href="productos.php">Explorar juguetes →</a>
    </div>
    <div class="about-illustration" aria-hidden="true">
        <span>🧸</span><span>🧩</span><span>🎨</span><span>🚀</span>
    </div>
</section>

<section class="about-values" aria-labelledby="values-title">
    <p class="eyebrow">LO QUE NOS INSPIRA</p>
    <h2 id="values-title">Hay muchas maneras de jugar</h2>
    <div class="value-grid">
        <article class="value-card">
            <span aria-hidden="true">🧩</span>
            <h3>Aprender jugando</h3>
            <p>Los retos, las piezas y los números invitan a probar ideas y descubrir algo nuevo.</p>
        </article>
        <article class="value-card">
            <span aria-hidden="true">🎨</span>
            <h3>Crear a tu manera</h3>
            <p>Colores y materiales para inventar personajes, construir mundos y expresarse con libertad.</p>
        </article>
        <article class="value-card">
            <span aria-hidden="true">🚀</span>
            <h3>Compartir aventuras</h3>
            <p>Un juego puede ser la oportunidad de pasar tiempo juntos y crear nuevas historias.</p>
        </article>
    </div>
</section>

<section class="about-story" aria-labelledby="story-title">
    <div>
        <p class="eyebrow">NUESTRO PROYECTO</p>
        <h2 id="story-title">Una tienda para aprender haciendo</h2>
    </div>
    <div>
        <p>Supland es un proyecto estudiantil de una tienda de juguetes. Reúne un catálogo con categorías, cuentas de usuario y una experiencia de compra simulada.</p>
        <p>Puedes explorar los juguetes, buscar por categoría y precio, y crear una cuenta para armar tu bolsa y consultar tus compras. Las compras son de demostración y no generan cobros ni envíos reales.</p>
        <a class="btn secondary" href="registro.php">Crear mi cuenta</a>
    </div>
</section>
<?php footer_view(); ?>
