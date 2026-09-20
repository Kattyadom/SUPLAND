# Revisión del proyecto

Requisitos tomados de «Proyecto – Bases de Datos.docx». Revisión funcional del 20 de septiembre de 2026.

## Funciones del sitio

| Requisito | Resultado y ubicación |
| --- | --- |
| PHP conectado a MySQL | Comprobado con PDO en `php/conexion.php` |
| Registro con nombre, correo y contraseña | Comprobado en `registro.php`; rechaza correo repetido |
| Login y cierre de sesión | Comprobados en `login.php` y `logout.php` |
| Dos tipos de usuario | Administrador y cliente, con permisos comprobados en el servidor |
| Opciones distintas según el rol | Menú en `php/vista.php`; cliente no puede abrir administración |
| Ver, agregar, modificar y eliminar productos | Comprobado con formularios reales; la eliminación es lógica para conservar ventas |
| Administrador consulta ventas y usuarios | Comprobado en `ventas.php` y `usuarios.php` |
| Cliente consulta productos y precios | Comprobado en `productos.php` |
| Seleccionar varios productos y cantidades | Bolsa de sesión en `comprar.php`; valida existencias |
| Compra simulada | Guarda venta, detalles, fecha, usuario, precio, cantidad y total; descuenta stock |
| Consultar compras propias | Comprobado: otro cliente no ve la compra del primero |
| Cinco tablas relacionadas | `usuarios`, `categorias`, `productos`, `ventas` y `detalle_venta`; claves foráneas en el SQL |
| Mínimo tres categorías | El SQL inicial incluye cuatro |
| Mínimo quince productos | El SQL inicial incluye quince; comprobado tras importación en base nueva |
| Páginas mínimas | Inicio, registro, login, productos, panel, agregar, modificar, ventas, compras y logout presentes |
| HTML, CSS y Bootstrap | Cargados desde el encabezado compartido; Bootstrap requiere Internet |
| Diseño adaptable | Inicio revisado en escritorio y móvil; catálogo, búsqueda combinada y menú probados a 390 px, sin desbordamiento horizontal en esas páginas |
| Búsqueda y filtros adicionales | Nombre, descripción, categoría, precios, disponibilidad y orden; pruebas combinadas correctas |
| Inicio visible para el cliente | Registro y login del cliente llevan a `index.php`; enlace Inicio en el menú |

## Pruebas realizadas

`pruebas/integracion.py` utiliza HTTP, sesiones separadas y una base temporal importada desde el SQL de entrega. Comprueba registro, login incorrecto y correcto, duplicados, permisos, productos, búsquedas, formulario inválido, carrito, límites de stock, ventas, historial privado y cierre de sesión.

Una compra de dos Smart Puzzle a $15.75 y un Number Blocks a $13.50 registró $45.00 y tres unidades en los detalles. Las existencias pasaron de 25 a 23 y de 20 a 19, respectivamente. Reenviar la confirmación no creó otra venta. La base temporal se eliminó al terminar. Los datos de la tienda no se modificaron.

También se revisó la sintaxis de los archivos PHP con `php -l`.

## Organización del código

Las páginas ya contienen su contenido y formularios; se eliminó el archivo que concentraba todas las pantallas. Se conservan funciones compartidas para conexión, sesión, permisos, consultas y elementos repetidos. Tener archivos compartidos evita copiar la misma lógica en cada página.

## Entrega académica pendiente

El sitio y su archivo SQL no equivalen a toda la entrega. El documento también solicita:

- PDF con capturas del funcionamiento: no está incluido todavía.
- Presentación breve: no está incluida todavía.
- Demostración del equipo: deben realizarla sus integrantes.

Para demostrarlo: importar el SQL, crear el administrador, registrar un cliente, mostrar las opciones por rol, agregar y editar un producto, hacer una compra, consultar el historial y mostrar las filas relacionadas en MySQL. El equipo debe poder explicar el código que presenta.
