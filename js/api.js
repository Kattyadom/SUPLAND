/* Datos visuales locales: no usa servidor ni base de datos. */
window.Supland = (() => {
    const key = 'supland-admin-data';
    const seed = { products: [{ id: 1, name: 'Rocket Builder', description: 'Construye, despega y explora.', category: 'Aventura', price: 2499, stock: 20, active: 1, color: 'sky', image: '' }, { id: 2, name: 'Mi Primer Dino', description: 'Un amigo jurásicamente tierno.', category: 'Peluches', price: 1850, stock: 15, active: 1, color: 'grass', image: '' }, { id: 3, name: 'Smart Puzzle', description: 'Rompecabezas para desarrollar la lógica.', category: 'Educativos', price: 1575, stock: 25, active: 1, color: 'candy', image: '' }], users: [], orders: [] };
    function read() { try { return JSON.parse(localStorage.getItem(key)) || seed; } catch { return seed; } }
    function write(data) { localStorage.setItem(key, JSON.stringify(data)); }
    function element(tag, text, attributes = {}) { const node = document.createElement(tag); if (text !== undefined) node.textContent = text; Object.entries(attributes).forEach(([name, value]) => node.setAttribute(name, value)); return node; }
    function money(cents) { return new Intl.NumberFormat('es-SV', { style: 'currency', currency: 'USD' }).format(cents / 100); }
    function icons() { window.lucide?.createIcons(); }
    async function api(path, method = 'GET', payload) {
        const data = read();
        if (path === '/session') return { authenticated: true, setup: false };
        if (path === '/login' || path === '/setup' || path === '/logout') return { ok: true };
        if (path === '/products') return data.products.filter((product) => product.active);
        const match = path.match(/^\/admin\/(products|users|orders)(?:\/(\d+))?$/); if (!match) throw new Error('Sección no encontrada.');
        const [, type, id] = match; const list = data[type];
        if (method === 'GET') return type === 'orders' ? list.map((order) => ({ ...order, items: order.items || [] })) : list;
        if (type === 'products' && method === 'POST') { const product = { ...payload, id: Date.now(), price: Math.round(Number(payload.price) * 100), active: payload.active ? 1 : 0 }; list.push(product); write(data); return { ok: true, id: product.id }; }
        if (type === 'products' && method === 'PUT') { Object.assign(list.find((item) => item.id === Number(id)), { ...payload, price: Math.round(Number(payload.price) * 100), active: payload.active ? 1 : 0 }); write(data); return { ok: true }; }
        if (type === 'products' && method === 'DELETE') { list.find((item) => item.id === Number(id)).active = 0; write(data); return { ok: true }; }
        if (type === 'users' && method === 'POST') { list.push({ ...payload, id: Date.now(), active: payload.active ? 1 : 0 }); write(data); return { ok: true }; }
        if (type === 'users' && method === 'PUT') { Object.assign(list.find((item) => item.id === Number(id)), payload, { active: payload.active ? 1 : 0 }); write(data); return { ok: true }; }
        if (type === 'orders' && method === 'PUT') { Object.assign(list.find((item) => item.id === Number(id)), payload); write(data); return { ok: true }; }
        throw new Error('Operación no disponible.');
    }
    return { api, element, money, icons };
})();
