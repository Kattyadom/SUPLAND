<?php
declare(strict_types=1);

function process_action(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }
    if (!is_string($_POST['csrf'] ?? null) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        throw new RuntimeException('La sesión del formulario venció. Recarga la página.');
    }
    $action = input('accion', 40);
    if ($action === 'registro' || $action === 'configurar') {
        $name = input('nombre', 100);
        $email = strtolower(input('correo', 190));
        $password = input('contrasena', 72);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
            throw new RuntimeException('Usa un correo válido y una contraseña de 8 a 72 caracteres.');
        }
        if ($password !== input('confirmar', 72)) {
            throw new RuntimeException('Las contraseñas no coinciden.');
        }
        $role = 'cliente';
        if ($action === 'configurar') {
            if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
                throw new RuntimeException('Configura al administrador desde esta computadora.');
            }
            // Serialize the one-time administrator setup.
            $lock = rows('SELECT GET_LOCK(?, 5) AS acquired', ['supland_admin_setup'])[0];
            if ((int) $lock['acquired'] !== 1) {
                throw new RuntimeException('La configuración está en uso. Inténtalo de nuevo.');
            }
            if (rows("SELECT id_usuario FROM usuarios WHERE tipo_usuario='administrador'")) {
                execute('SELECT RELEASE_LOCK(?)', ['supland_admin_setup']);
                throw new RuntimeException('El administrador ya está configurado.');
            }
            $role = 'administrador';
        }
        try {
            execute('INSERT INTO usuarios(nombre,correo,contrasena,tipo_usuario) VALUES(?,?,?,?)', [$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
            $newUserId = (int) db()->lastInsertId();
        } catch (PDOException $error) {
            if ($error->getCode() === '23000') {
                throw new RuntimeException('Ese correo ya tiene una cuenta.');
            }
            throw $error;
        } finally {
            if ($role === 'administrador') {
                execute('SELECT RELEASE_LOCK(?)', ['supland_admin_setup']);
            }
        }
        session_regenerate_id(true);
        $_SESSION['usuario'] = $newUserId;
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        redirect($role === 'administrador' ? 'admin.php' : 'productos.php');
    }
    if ($action === 'login') {
        $attempts = array_filter($_SESSION['intentos'] ?? [], fn($stamp) => $stamp > time() - 60);
        if (count($attempts) >= 8) {
            throw new RuntimeException('Espera un minuto antes de intentar nuevamente.');
        }
        $account = rows('SELECT * FROM usuarios WHERE correo=? AND activo=1', [strtolower(input('correo', 190))])[0] ?? null;
        if (!$account || !password_verify(input('contrasena', 72), $account['contrasena'])) {
            $attempts[] = time();
            $_SESSION['intentos'] = $attempts;
            throw new RuntimeException('Correo o contraseña incorrectos.');
        }
        session_regenerate_id(true);
        $_SESSION['usuario'] = $account['id_usuario'];
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        $_SESSION['intentos'] = [];
        redirect($account['tipo_usuario'] === 'administrador' ? 'admin.php' : 'productos.php');
    }
    if ($action === 'logout') {
        $_SESSION = [];
        session_regenerate_id(true);
        session_destroy();
        redirect('index.php');
    }
    if ($action === 'agregar' || $action === 'cantidad') {
        require_role('cliente');
        $id = integer('id_producto', 1, PHP_INT_MAX);
        $product = rows('SELECT * FROM productos WHERE id_producto=? AND activo=1', [$id])[0] ?? null;
        $quantity = $action === 'agregar' ? ($_SESSION['carrito'][$id] ?? 0) + 1 : integer('cantidad', 0, 9999);
        if ($quantity === 0) {
            unset($_SESSION['carrito'][$id]);
        } elseif (!$product || $product['cantidad'] < $quantity) {
            throw new RuntimeException('No hay suficientes existencias.');
        } else {
            $_SESSION['carrito'][$id] = $quantity;
        }
        redirect($action === 'agregar' ? 'productos.php?agregado=1' : 'comprar.php');
    }
    if ($action === 'guardar_producto') {
        require_role('administrador');
        $id = integer('id_producto', 0, PHP_INT_MAX);
        $price = cents(input('precio', 10));
        if ($price < 1) {
            throw new RuntimeException('El precio debe ser mayor que cero.');
        }
        $category = integer('id_categoria', 1, PHP_INT_MAX);
        $color = input('color', 10);
        if (!rows('SELECT id_categoria FROM categorias WHERE id_categoria=?', [$category]) || !in_array($color, ['grass','sky','candy','crayon','sun','star'], true)) {
            throw new RuntimeException('Categoría o color inválido.');
        }
        $values = [input('nombre_producto', 100), input('descripcion', 1500), number_format($price / 100, 2, '.', ''), integer('cantidad', 0, 9999), $category, $color, isset($_POST['activo']) ? 1 : 0];
        if ($id) {
            if (!rows('SELECT id_producto FROM productos WHERE id_producto=?', [$id])) {
                throw new RuntimeException('El producto no existe.');
            }
            execute('UPDATE productos SET nombre_producto=?,descripcion=?,precio=?,cantidad=?,id_categoria=?,color=?,activo=? WHERE id_producto=?', [...$values, $id]);
        } else {
            execute('INSERT INTO productos(nombre_producto,descripcion,precio,cantidad,id_categoria,color,activo) VALUES(?,?,?,?,?,?,?)', $values);
        }
        redirect('admin.php?guardado=1');
    }
    if ($action === 'eliminar_producto') {
        require_role('administrador');
        // Logical deletion keeps products referenced by existing sales intact.
        execute('UPDATE productos SET activo=0 WHERE id_producto=?', [integer('id_producto', 1, PHP_INT_MAX)]);
        redirect('admin.php?eliminado=1');
    }
    if ($action === 'comprar') {
        $customer = require_role('cliente');
        $key = input('clave', 64);
        if (!hash_equals($_SESSION['compra_clave'] ?? '', $key)) {
            throw new RuntimeException('Recarga el carrito antes de confirmar.');
        }
        $prior = rows('SELECT id_venta FROM ventas WHERE clave=? AND id_usuario=?', [$key, $customer['id_usuario']]);
        if ($prior) {
            redirect('mis_compras.php');
        }
        if (!$_SESSION['carrito']) {
            throw new RuntimeException('El carrito está vacío.');
        }
        db()->beginTransaction();
        try {
            $details = [];
            $total = 0;
            $quantities = $_SESSION['carrito'];
            ksort($quantities);
            foreach ($quantities as $id => $quantity) {
                $product = rows('SELECT * FROM productos WHERE id_producto=? AND activo=1 FOR UPDATE', [$id])[0] ?? null;
                if (!$product || $quantity < 1 || $product['cantidad'] < $quantity) {
                    throw new RuntimeException('Cambió la disponibilidad de un producto. Revisa tu carrito.');
                }
                $total += cents($product['precio']) * $quantity;
                $details[] = [$product, $quantity];
            }
            execute('INSERT INTO ventas(total,id_usuario,clave) VALUES(?,?,?)', [number_format($total / 100, 2, '.', ''), $customer['id_usuario'], $key]);
            $sale = (int) db()->lastInsertId();
            foreach ($details as [$product, $quantity]) {
                execute('INSERT INTO detalle_venta(id_venta,id_producto,nombre_producto,cantidad,precio) VALUES(?,?,?,?,?)', [$sale, $product['id_producto'], $product['nombre_producto'], $quantity, $product['precio']]);
                execute('UPDATE productos SET cantidad=cantidad-? WHERE id_producto=?', [$quantity, $product['id_producto']]);
            }
            db()->commit();
            $_SESSION['carrito'] = [];
            $_SESSION['compra_clave'] = bin2hex(random_bytes(32));
            redirect('mis_compras.php?confirmada=1');
        } catch (Throwable $error) {
            if (db()->inTransaction()) {
                db()->rollBack();
            }
            throw $error;
        }
    }
    throw new RuntimeException('Acción no válida.');
}
