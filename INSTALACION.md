# Supland con PHP y MySQL

## Ejecutar en XAMPP

1. Instala XAMPP con PHP 8.1 o posterior y su servicio MySQL/MariaDB.
2. Copia esta carpeta en `C:\xampp\htdocs\SUPLAND`.
3. Abre el panel de XAMPP e inicia Apache y MySQL.
4. Abre `http://localhost/phpmyadmin`, selecciona Importar e importa `sql/supland.sql`.
5. Revisa `php/config.php`. Los valores iniciales son los habituales de XAMPP local: servidor `127.0.0.1`, base `supland`, usuario `root` y contraseña vacía. Cambia estos datos si tu instalación es distinta.
6. Abre `http://localhost/SUPLAND/configurar.php` desde esta computadora y crea tu administrador. No se incluye ninguna contraseña predeterminada. Esta pantalla queda bloqueada cuando existe un administrador.
7. Abre `http://localhost/SUPLAND/index.php`. Registra una segunda cuenta como cliente para probar la compra.

Las páginas PHP se abren mediante Apache, no haciendo doble clic ni mediante Live Server. Bootstrap se carga desde su CDN y necesita conexión a Internet.

## Funciones implementadas

- Registro de clientes con contraseña cifrada mediante hash.
- Inicio y cierre de sesión, verificación de permisos en PHP y formularios con token CSRF.
- Administrador: agregar, modificar, retirar productos, consultar ventas y usuarios.
- Cliente: catálogo filtrable, carrito con cantidades, compra simulada e historial propio.
- Precio calculado en el servidor, bloqueo de existencias durante la compra y transacción para guardar venta, detalles y stock juntos.
- Eliminación lógica de productos: desaparecen del catálogo, pero se conserva el historial de las ventas y pueden reactivarse al editar.
- Cinco tablas relacionadas, cuatro categorías y quince juguetes iniciales.

## Tablas y relaciones

`categorias` tiene muchos `productos`.
`usuarios` tiene muchas `ventas`.
`ventas` tiene muchos registros en `detalle_venta`.
`productos` se relaciona con `detalle_venta`.

El SQL no elimina datos existentes. Los registros iniciales usan identificadores del 1 al 15 y se insertan solo si no existen. Utiliza una base nueva para una primera instalación limpia.

## Validación pendiente en tu entorno

En esta computadora no se encontró PHP ni MySQL al preparar la entrega. La ejecución real y las capturas del funcionamiento deben verificarse después de instalar XAMPP. No se han generado capturas ni un PDF que afirmen una ejecución todavía no realizada.

## Guion de demostración

1. Presentar Supland como tienda de juguetes y mostrar la paleta.
2. Mostrar las cinco tablas y sus relaciones en phpMyAdmin.
3. Crear un cliente y probar un inicio de sesión incorrecto y uno correcto.
4. Entrar como administrador, agregar un juguete, editarlo y retirarlo.
5. Entrar como cliente, agregar dos juguetes y ajustar cantidades.
6. Confirmar la compra simulada y consultar Mis compras.
7. Entrar como administrador y consultar esa venta y sus detalles.
8. Mostrar las filas registradas en MySQL y el stock descontado.
9. Cerrar sesión y comprobar que las páginas privadas vuelven al login.

Para la entrega académica aún se necesitan el PDF con capturas verificadas, la presentación breve y la demostración en un entorno PHP/MySQL funcionando.
