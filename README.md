# Supland

Tienda de juguetes para el proyecto de Bases de Datos. Utiliza PHP, MySQL, HTML, CSS, Bootstrap y JavaScript para el menú y los mensajes de confirmación.

## Cómo abrir el proyecto

1. Instala XAMPP con PHP 8.1 o posterior y las extensiones `pdo_mysql` y `mbstring` habilitadas.
2. Copia la carpeta a `C:\xampp\htdocs\SUPLAND`.
3. Inicia Apache y MySQL desde XAMPP.
4. Entra a `http://localhost/phpmyadmin` e importa `sql/supland.sql`.
5. Revisa `php/config.php`. Por defecto usa la base `supland`, usuario `root`, contraseña vacía y puerto `3306`. Son valores para XAMPP local.
6. Entra a `http://localhost/SUPLAND/configurar.php` y crea el primer administrador. Solo funciona desde la computadora del servidor y cuando todavía no hay un administrador.
7. Abre `http://localhost/SUPLAND/index.php`. Usa Crear cuenta para registrar un cliente.

Los PHP necesitan Apache: no se abren con doble clic ni con Live Server. GitHub guarda el código y el SQL inicial; no copia los usuarios, ventas y productos que cada persona crea en su MySQL. GitHub Pages no ejecuta PHP. Cada integrante puede instalar su copia siguiendo estos pasos.

Bootstrap se carga desde Internet. Mantén conexión a Internet durante la demostración del diseño.

## Dónde está cada parte

Cada página PHP de la carpeta principal contiene su formulario o contenido:

| Archivo | Para qué sirve |
| --- | --- |
| `index.php` | Inicio, buscador y categorías |
| `registro.php`, `login.php`, `logout.php` | Crear cuenta, entrar y salir |
| `productos.php` | Catálogo, búsqueda y filtros |
| `comprar.php` | Bolsa, cantidades y confirmación |
| `mis_compras.php` | Historial del cliente conectado |
| `admin.php` | Productos y acciones del administrador |
| `agregar_producto.php`, `editar_producto.php` | Formularios de productos |
| `ventas.php`, `usuarios.php` | Consultas del administrador |
| `configurar.php` | Creación del primer administrador |

Compartimos las partes que varias páginas necesitan:

- `php/preparar.php`: prepara la sesión y comprueba los permisos de la página.
- `php/conexion.php` y `php/config.php`: conexión a MySQL.
- `php/acciones.php`: recibe los formularios y guarda los cambios.
- `php/funciones.php`: consultas, sesión, validaciones y precios.
- `php/catalogo.php`: búsqueda con sus filtros.
- `php/vista.php`: encabezado, pie de página y tarjetas.
- `css/styles.css` y `css/proyecto.css`: estilos usados por el sitio.
- `js/proyecto.js`: menú móvil y confirmación al retirar productos. `js/lucide.js` contiene los iconos.

Los `.html` anteriores redirigen a los PHP para conservar enlaces. No son las páginas que se deben editar. Los demás CSS y JavaScript antiguos no se cargan en la versión PHP.

## Cómo funciona una compra

1. El cliente agrega productos a una bolsa guardada en su sesión.
2. Al confirmar, PHP consulta los precios y existencias de MySQL.
3. Guarda la venta y sus detalles y descuenta existencias dentro de una transacción. Si algo falla, cancela los cambios.
4. El cliente ve su compra en Mis compras y el administrador la ve en Ventas.

No hay cobros reales. Retirar un producto lo marca como inactivo: desaparece del catálogo, pero permanece en el historial. Las contraseñas se guardan como hashes y las consultas reciben los datos mediante parámetros.

## Pruebas

Con MySQL iniciado, Python instalado y el puerto 8097 libre:

```powershell
python pruebas/integracion.py
```

La prueba crea una base `supland_prueba_` con identificador único, ejecuta un servidor PHP temporal y prueba formularios reales. Al terminar elimina esa base y sus sesiones. No usa los datos de `supland`. La variable `PHP_BIN` permite indicar otra ruta de PHP.

Consulta `REVISION.md` para ver los requisitos comprobados y los pendientes de entrega.
