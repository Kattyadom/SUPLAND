(() => {
    const { api, element: el, money, icons } = Supland;
    const nav = document.querySelector('header nav');
    const toggle = document.querySelector('.hamb');
    const toast = document.querySelector('.toast');
    let timer;
    let products = [];

    function notify(text) {
        toast.textContent = text;
        toast.classList.add('show');
        clearTimeout(timer);
        timer = setTimeout(() => toast.classList.remove('show'), 3500);
    }

    function cart() {
        try {
            const value = JSON.parse(localStorage.getItem('supland-cart-v2') || '[]');
            return Array.isArray(value) ? value.filter((item) => item && Number.isInteger(item.id) && Number.isInteger(item.quantity) && item.quantity > 0) : [];
        } catch { return []; }
    }

    function saveCart(items) {
        try {
            localStorage.setItem('supland-cart-v2', JSON.stringify(items));
            window.dispatchEvent(new Event('cart-updated'));
            return true;
        } catch {
            notify('No se pudo guardar la bolsa en este navegador.');
            return false;
        }
    }

    function count() {
        document.querySelector('#count').textContent = cart().reduce((total, item) => total + item.quantity, 0);
    }

    function closeMenu() {
        nav.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Abrir menú');
    }
    toggle.addEventListener('click', () => {
        const open = nav.classList.toggle('open');
        toggle.setAttribute('aria-expanded', String(open));
        toggle.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
    });
    nav.addEventListener('click', (event) => { if (event.target.closest('a')) closeMenu(); });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && nav.classList.contains('open')) { closeMenu(); toggle.focus(); }
    });
    document.querySelector('.bag').addEventListener('click', () => { location.href = 'delivery.html'; });
    document.querySelector('#showAll')?.addEventListener('click', () => { location.href = 'productos.html'; });
    document.querySelectorAll('.agegrid button').forEach((button) => button.addEventListener('click', () => { location.href = 'productos.html'; }));
    document.querySelector('.news form')?.addEventListener('submit', (event) => {
        event.preventDefault();
        notify('Para novedades, escríbenos a hola@supland.com.');
    });
    window.addEventListener('storage', count);
    window.addEventListener('cart-updated', count);
    window.addEventListener('pageshow', count);
    count();

    const productGrid = document.querySelector('[data-store-grid]');
    function render() {
        if (!productGrid) return;
        const query = document.querySelector('#catalog-search')?.value.toLocaleLowerCase() || '';
        const category = document.querySelector('#catalog-category')?.value || '';
        let selected = products.filter((p) => p.name.toLocaleLowerCase().includes(query) && (!category || p.category === category));
        if (productGrid.dataset.limit) selected = selected.slice(0, Number(productGrid.dataset.limit));
        productGrid.replaceChildren();
        selected.forEach((product) => {
            const card = el('article', undefined, { class: 'store-card' });
            const visual = el('div', undefined, { class: 'store-image color-' + product.color });
            if (product.image) visual.append(el('img', undefined, { src: product.image, alt: product.name, loading: 'lazy' }));
            else visual.append(el('span', { Educativos: '🧩', Creativos: '🎨', Aventura: '🚀', Peluches: '🧸' }[product.category], { role: 'img', 'aria-label': product.category }));
            const info = el('div', undefined, { class: 'store-info' });
            info.append(el('small', product.category), el('h3', product.name), el('p', product.description));
            const bottom = el('div', undefined, { class: 'store-price' });
            bottom.append(el('strong', money(product.price)));
            const button = el('button', undefined, { type: 'button', class: 'action', 'aria-label': 'Agregar ' + product.name, title: 'Agregar a la bolsa' });
            button.append(el('i', undefined, { 'data-lucide': 'plus' }));
            button.disabled = product.stock === 0;
            if (!product.stock) info.append(el('small', 'Agotado'));
            button.addEventListener('click', () => {
                const items = cart();
                const existing = items.find((item) => item.id === product.id);
                if ((existing?.quantity || 0) >= product.stock) { notify('No hay más existencias de este producto.'); return; }
                if (existing) existing.quantity += 1;
                else items.push({ id: product.id, quantity: 1 });
                if (saveCart(items)) notify(product.name + ' está en tu bolsa.');
            });
            bottom.append(button);
            info.append(bottom);
            card.append(visual, info);
            productGrid.append(card);
        });
        if (!selected.length) productGrid.append(el('p', 'No hay productos disponibles para esta selección.'));
        icons();
    }
    if (productGrid) {
        productGrid.textContent = 'Cargando productos…';
        api('/products').then((result) => { products = result; render(); }).catch((error) => { productGrid.textContent = error.message; });
        document.querySelector('#catalog-search')?.addEventListener('input', render);
        document.querySelector('#catalog-category')?.addEventListener('change', render);
    }
    window.SuplandCart = { read: cart, save: saveCart, notify };
    icons();
})();
