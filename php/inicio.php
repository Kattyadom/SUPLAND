<section class="hero" id="inicio">
                <div>
                    <p class="eyebrow">★ JUGAR ES COSA SERIA</p>
                    <h1>Un mundo de<br>
                        <em>¡wow!</em> en cada caja</h1>
                    <p>Juguetes que despiertan grandes ideas, aventuras y sonrisas. Elegidos con amor para pequeños exploradores.</p>
                    <div class="actions">
                        <a class="btn" href="#catalogo">Explorar juguetes →</a>
                        <a class="plain" href="comprar.php">🛵 Pide y juega</a>
                    </div>
                </div>
                <div class="art">
                    <span class="rocket">🚀</span>
                    <span class="rainbow">🌈</span>
                    <div class="box">SUPLAND<small>La diversión vive aquí</small>
                    </div>
                    <a class="express" href="comprar.php">🛵 <b>Delivery rápido</b>
                        <small>¡Juguetes en camino!</small>
                    </a>
                </div>
            </section>
            <div class="ticker">★ JUGUETES INCREÍBLES　•　 ENTREGA EN TU PUERTA　★　 SONRISAS GARANTIZADAS</div>
            <section class="products" id="catalogo">
                <div class="heading" id="novedades">
                    <div>
                        <p class="eyebrow">FAVORITOS DE LOS PEQUES</p>
                        <h2>Elige tu próxima <em>aventura</em>
                        </h2>
                    </div>
                    <a class="catalog-link" href="productos.php">Ver todos →</a>
                </div>
                <?php product_cards(rows('SELECT p.*,c.nombre_categoria FROM productos p JOIN categorias c USING(id_categoria) WHERE p.activo=1 ORDER BY p.id_producto LIMIT 3'), $account); ?>
            </section>
            <section class="ages" id="edades">
                <div>
                    <p class="eyebrow">ENCUENTRA EL REGALO PERFECTO</p>
                    <h2>¿Para qué edad<br>buscamos magia?</h2>
                </div>
                <div class="agegrid">
                    <a class="age-link" href="productos.php">🧸<b>0—2</b>
                        <small>años</small>
                    </a>
                    <a class="age-link" href="productos.php">🪁<b>3—5</b>
                        <small>años</small>
                    </a>
                    <a class="age-link" href="productos.php">🛹<b>6—8</b>
                        <small>años</small>
                    </a>
                    <a class="age-link" href="productos.php">🧩<b>9—12</b>
                        <small>años</small>
                    </a>
                </div>
            </section>
            <section class="about" id="nosotros">
                <div class="aboutart">🪁<b>Desde 2018<br>jugando en serio</b>
                </div>
                <div>
                    <p class="eyebrow">NUESTRA HISTORIA</p>
                    <h2>No vendemos juguetes.<br>
                        <em>Encendemos imaginación.</em>
                    </h2>
                    <p>Supland nació de una idea sencilla: cada juguete puede ser la puerta a una gran historia. Elegimos piezas seguras, duraderas y capaces de convertir cualquier tarde en una aventura.</p>
                    <div class="stats">
                        <span>
                            <b>+8K</b>familias felices</span>
                        <span>
                            <b>100%</b>juego seguro</span>
                        <span>
                            <b>7 años</b>creando sonrisas</span>
                    </div>
                </div>
            </section>
            <section class="deliverycta">
                <span>🛵</span>
                <div>
                    <p class="eyebrow">SUPLAND EXPRESS</p>
                    <h2>De la tienda a su mundo <em>en tiempo récord</em>
                    </h2>
                    <p>Elige, sigue el recorrido y recibe la diversión en tu puerta.</p>
                    <a class="btn" href="comprar.php">Simular mi delivery →</a>
                </div>
                <div class="route">🏬 ┄┄┄ 🏠<small>25—35 min</small>
                </div>
            </section>
