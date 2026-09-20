(() => {
    const { api, element: el, money, icons } = Supland;
    const { read, save } = SuplandCart;
    const message = document.querySelector('#checkout-message');
    const place = document.querySelector('#place-order');
    let products = [];
    const requestId = crypto.randomUUID();

    function render() {
        const area = document.querySelector('#cart-lines');
        area.replaceChildren();
        const items = read();
        let total = 0;
        let valid = items.length > 0;
        items.forEach((item) => {
            const product = products.find((p) => p.id === item.id);
            const row = el('div', undefined, { class: 'cart-line' });
            const info = el('div');
            info.append(el('strong', product?.name || 'Producto no disponible'));
            if (product) info.append(el('p', money(product.price) + ' por unidad'));
            if (!product || product.stock < item.quantity) {
                valid = false;
                info.append(el('p', 'No hay suficientes existencias. Ajusta la cantidad.'));
            }
            const controls = el('div', undefined, { class: 'quantity-controls' });
            const minus = el('button', undefined, { class: 'icon-action', type: 'button', 'aria-label': 'Quitar una unidad de ' + (product?.name || 'producto'), title: 'Quitar una unidad' });
            minus.append(el('i', undefined, { 'data-lucide': 'minus' }));
            minus.onclick = () => save(items.map((line) => line.id === item.id ? { ...line, quantity: line.quantity - 1 } : line).filter((line) => line.quantity > 0));
            const plus = el('button', undefined, { class: 'icon-action', type: 'button', 'aria-label': 'Agregar una unidad de ' + (product?.name || 'producto'), title: 'Agregar una unidad' });
            plus.append(el('i', undefined, { 'data-lucide': 'plus' }));
            plus.disabled = !product || item.quantity >= product.stock;
            plus.onclick = () => save(items.map((line) => line.id === item.id ? { ...line, quantity: line.quantity + 1 } : line));
            controls.append(minus, el('span', item.quantity), plus);
            const remove = el('button', undefined, { class: 'icon-action', type: 'button', 'aria-label': 'Eliminar ' + (product?.name || 'producto'), title: 'Eliminar de la bolsa' });
            remove.append(el('i', undefined, { 'data-lucide': 'trash-2' }));
            remove.onclick = () => save(items.filter((line) => line.id !== item.id));
            controls.append(remove);
            row.append(info, controls);
            area.append(row);
            total += (product?.price || 0) * item.quantity;
        });
        if (!items.length) area.append(el('p', 'Tu bolsa está vacía.'));
        document.querySelector('#cart-total').textContent = money(total);
        place.disabled = !valid;
        icons();
    }

    window.addEventListener('cart-updated', render);
    window.addEventListener('storage', render);
    document.querySelector('#checkout-form').addEventListener('submit', async (event) => {
        event.preventDefault();
        place.disabled = true;
        message.textContent = '';
        try {
            const data = Object.fromEntries(new FormData(event.currentTarget));
            const result = await api('/orders', 'POST', { ...data, items: read(), request_id: requestId });
            save([]);
            document.querySelector('#checkout-content').hidden = true;
            document.querySelector('#order-success').hidden = false;
            document.querySelector('#success-summary').textContent = 'SUP-' + result.id + ' · ' + money(result.total) + ' · Pendiente';
        } catch (error) {
            message.textContent = error.message;
            try { products = await api('/products'); } catch { /* Keep the last loaded catalog. */ }
            render();
        }
    });
    api('/products').then((result) => { products = result; render(); }).catch((error) => { message.textContent = error.message; document.querySelector('#cart-lines').textContent = 'No se pudo cargar la bolsa.'; });
})();
