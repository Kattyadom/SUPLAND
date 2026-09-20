"""Prueba los formularios reales en una base temporal. Requiere MySQL y PHP."""
import http.cookiejar
import os
from pathlib import Path
import re
import subprocess
import tempfile
import time
import urllib.error
import urllib.parse
import urllib.request

ROOT = Path(__file__).resolve().parents[1]
PHP = os.environ.get('PHP_BIN', 'C:/xampp/php/php.exe')
env = dict(os.environ, SUPLAND_DB_NAME='supland_prueba_' + str(time.time_ns()))
base = 'http://127.0.0.1:8097/'

def database(action):
    result = subprocess.run([PHP, str(ROOT / 'pruebas/base.php'), action], env=env, capture_output=True, text=True)
    if result.returncode:
        raise RuntimeError(result.stdout + result.stderr)
    print(result.stdout)

def client():
    return urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))

def request(browser, page, data=None):
    body = urllib.parse.urlencode(data).encode() if data is not None else None
    try:
        response = browser.open(base + page, body)
    except urllib.error.HTTPError as error:
        response = error
    text = response.read().decode('utf-8')
    assert not re.search(r'Fatal error|Warning:|Parse error', text), text
    return response.status, response.url, text

def post(browser, page, data):
    _, _, html = request(browser, page)
    token = re.search(r'name="csrf" value="([^"]+)"', html)
    assert token, page
    return request(browser, page, dict(data, csrf=token[1]))

server = None
sessions = tempfile.TemporaryDirectory(prefix='supland_sesiones_')
created = False
try:
    database('crear')
    created = True
    server = subprocess.Popen([PHP, '-d', 'session.save_path=' + sessions.name, '-S', '127.0.0.1:8097', '-t', str(ROOT)], env=env, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
    guest, admin, customer, other = [client() for _ in range(4)]
    for attempt in range(30):
        try:
            request(guest, 'index.php')
            break
        except urllib.error.URLError:
            time.sleep(0.1)
    assert server.poll() is None, 'El puerto 8097 está ocupado.'
    assert request(guest, 'admin.php')[1].endswith('login.php')
    assert '15 juguetes encontrados' in request(guest, 'productos.php')[2]
    assert 'Smart Puzzle' in request(guest, 'productos.php?q=logica')[2]
    assert '2 juguetes encontrados' in request(guest, 'productos.php?q=rocket&categoria=3&min=24&max=25&stock=1')[2]
    assert '0 juguetes encontrados' in request(guest, 'productos.php?q=%25')[2]
    assert request(guest, 'productos.php?q[]=x&categoria[]=x')[0] == 200
    print('OK: catálogo, filtros combinados, búsquedas y acceso de invitados')
    password = 'Prueba-local-2026'
    _, url, html = post(admin, 'configurar.php', dict(accion='configurar', nombre='Administrador de prueba', correo='admin@example.test', contrasena=password, confirmar=password))
    assert url.endswith('admin.php'), re.findall(r'<p class="notice"[^>]*>(.*?)</p>', html)
    assert 'Configuración no disponible' in request(guest, 'configurar.php')[2]
    for browser, email in [(customer, 'cliente@example.test'), (other, 'otro@example.test')]:
        _, url, html = post(browser, 'registro.php', dict(accion='registro', nombre='Cliente de prueba', correo=email, contrasena=password, confirmar=password))
        assert url.endswith('index.php') and 'Mis compras' in html
        assert request(browser, 'admin.php')[0] == 403
    assert 'Ese correo ya tiene una cuenta' in post(guest, 'registro.php', dict(accion='registro', nombre='Duplicado', correo='cliente@example.test', contrasena=password, confirmar=password))[2]
    assert 'Correo o contraseña incorrectos' in post(guest, 'login.php', dict(accion='login', correo='cliente@example.test', contrasena='incorrecta'))[2]
    print('OK: registro, correo duplicado, login incorrecto y roles')
    data = dict(accion='guardar_producto', id_producto=0, nombre_producto='Juguete de prueba', descripcion='Prueba de administración', precio='12.50', cantidad=3, id_categoria=1, color='sky', activo=1)
    assert 'Producto guardado' in post(admin, 'agregar_producto.php', data)[2]
    html = request(admin, 'admin.php')[2]
    product_id = max(map(int, re.findall(r'editar_producto.php\?id=(\d+)', html)))
    data.update(id_producto=product_id, nombre_producto='Juguete modificado')
    assert 'Producto guardado' in post(admin, f'editar_producto.php?id={product_id}', data)[2]
    assert 'Juguete modificado' in request(guest, 'productos.php')[2]
    post(admin, 'admin.php', dict(accion='eliminar_producto', id_producto=product_id))
    assert 'Juguete modificado' not in request(guest, 'productos.php')[2]
    assert 'cliente@example.test' in request(admin, 'usuarios.php')[2]
    assert 'sesión del formulario venció' in request(customer, 'productos.php', dict(accion='agregar', id_producto=1, csrf='invalido'))[2]
    print('OK: agregar, editar, retirar productos, usuarios y protección de formularios')
    _, url, _ = post(customer, 'productos.php?q=puzzle&categoria=1', dict(accion='agregar', id_producto=1))
    assert 'q=puzzle' in url and 'categoria=1' in url
    post(customer, 'productos.php', dict(accion='agregar', id_producto=2))
    post(customer, 'comprar.php', dict(accion='cantidad', id_producto=1, cantidad=2))
    assert 'No hay suficientes existencias' in post(customer, 'comprar.php', dict(accion='cantidad', id_producto=1, cantidad=999))[2]
    _, _, html = request(customer, 'comprar.php')
    key = re.search(r'name="clave" value="([^"]+)"', html)[1]
    assert 'Compra simulada registrada' in post(customer, 'comprar.php', dict(accion='comprar', clave=key))[2]
    assert '$45.00' in request(admin, 'ventas.php')[2]
    assert 'No hay compras registradas' in request(other, 'mis_compras.php')[2]
    post(customer, 'comprar.php', dict(accion='comprar', clave=key))
    database('comprobar')
    post(customer, 'logout.php', dict(accion='logout'))
    assert request(customer, 'mis_compras.php')[1].endswith('login.php')
    assert post(customer, 'login.php', dict(accion='login', correo='cliente@example.test', contrasena=password))[1].endswith('index.php')
    print('OK: carrito, stock, compra, historial privado, no duplicación, logout y login al inicio')
finally:
    if server is not None:
        server.terminate()
        server.wait()
    if created:
        database('borrar')
    sessions.cleanup()
