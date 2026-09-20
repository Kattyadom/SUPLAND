(() => {
    const { api, element: el, money, icons } = Supland;
    const message = document.querySelector('#message');
    const filter = document.querySelector('#filter');
    const search = document.querySelector('#search');
    let state = { products: [], users: [], orders: [] };
    let tab = 'products';
    let setup = false;
    const transitions = {
        Pendiente: ['Preparando', 'Cancelado'],
        Preparando: ['En camino', 'Cancelado'],
        'En camino': ['Entregado'],
        Entregado: [],
        Cancelado: [],
    };

    function report(error) {
        message.textContent = error.message;
    }

    function iconButton(icon, label, callback) {
        const button = el('button', undefined, { type: 'button', class: 'icon-action', title: label, 'aria-label': label });
        button.append(el('i', undefined, { 'data-lucide': icon }));
        button.addEventListener('click', callback);
        return button;
    }

    async function load() {
        message.textContent = 'Cargando…';
        const [products, users, orders] = await Promise.all([
            api('/admin/products'), api('/admin/users'), api('/admin/orders'),
        ]);
        state = { products, users, orders };
        document.querySelector('#metric-products').textContent = products.filter((p) => p.active).length;
        document.querySelector('#metric-users').textContent = users.filter((u) => u.active).length;
        document.querySelector('#metric-orders').textContent = orders.filter((o) => o.status === 'Pendiente').length;
        document.querySelector('#metric-sales').textContent = money(orders.filter((o) => o.status === 'Entregado').reduce((sum, o) => sum + o.total, 0));
        message.textContent = '';
        render();
    }

    function render() {
        const area = document.querySelector('#records');
        area.replaceChildren();
        const query = search.value.toLocaleLowerCase();
        const rows = state[tab].filter((row) => {
            const status = tab === 'orders' ? row.status : String(row.active);
            return (!filter.value || status === filter.value) && [row.name, row.email, row.customer, row.category, row.id].join(' ').toLocaleLowerCase().includes(query);
        });
        const headings = {
            products: ['Producto', 'Categoría', 'Precio', 'Stock', 'Estado', 'Acciones'],
            users: ['Usuario', 'Correo', 'Teléfono', 'Estado', 'Acciones'],
            orders: ['Compra', 'Cliente', 'Fecha', 'Total', 'Estado', 'Detalle'],
        }[tab];
        const table = el('table');
        const thead = el('thead');
        const heading = el('tr');
        headings.forEach((name) => heading.append(el('th', name, { scope: 'col' })));
        thead.append(heading);
        table.append(thead);
        const body = el('tbody');
        for (const row of rows) {
            const tr = el('tr');
            const values = tab === 'products'
                ? [row.name, row.category, money(row.price), row.stock, row.active ? 'Activo' : 'Inactivo']
                : tab === 'users'
                    ? [row.name, row.email, row.phone || '—', row.active ? 'Activo' : 'Inactivo']
                    : ['SUP-' + row.id, row.customer, row.created, money(row.total)];
            values.forEach((value) => tr.append(el('td', value)));
            if (tab === 'orders') {
                const cell = el('td');
                const select = el('select', undefined, { 'aria-label': 'Estado de compra SUP-' + row.id });
                [row.status, ...transitions[row.status]].forEach((status) => select.append(el('option', status, { value: status })));
                select.disabled = !transitions[row.status].length;
                select.addEventListener('change', async () => {
                    select.disabled = true;
                    try {
                        await api('/admin/orders/' + row.id, 'PUT', { status: select.value });
                        await load();
                    } catch (error) {
                        select.value = row.status;
                        select.disabled = false;
                        report(error);
                    }
                });
                cell.append(select);
                tr.append(cell);
            }
            const actions = el('td');
            const group = el('div', undefined, { class: 'cell-actions' });
            if (tab === 'products') {
                const edit = el('a', undefined, { class: 'icon-action', href: 'agregar-producto.html?id=' + row.id, title: 'Editar ' + row.name, 'aria-label': 'Editar ' + row.name });
                edit.append(el('i', undefined, { 'data-lucide': 'pencil' }));
                group.append(edit);
                if (row.active) group.append(iconButton('archive', 'Desactivar ' + row.name, () => confirmProduct(row)));
            } else if (tab === 'users') {
                group.append(iconButton('pencil', 'Editar ' + row.name, () => editUser(row)));
            } else {
                group.append(iconButton('eye', 'Ver compra SUP-' + row.id, () => viewOrder(row)));
            }
            actions.append(group);
            tr.append(actions);
            body.append(tr);
        }
        if (!rows.length) {
            const row = el('tr');
            row.append(el('td', 'No hay registros para esta selección.', { colspan: headings.length }));
            body.append(row);
        }
        table.append(body);
        area.append(table);
        document.querySelector('#record-count').textContent = rows.length + ' registros';
        icons();
    }

    function setTab(value) {
        tab = value;
        document.querySelectorAll('[data-tab]').forEach((button) => {
            button.setAttribute('aria-selected', String(button.dataset.tab === tab));
            button.tabIndex = button.dataset.tab === tab ? 0 : -1;
        });
        document.querySelector('#records').setAttribute('aria-labelledby', 'tab-' + tab);
        document.querySelector('#new-user').hidden = tab !== 'users';
        filter.replaceChildren(el('option', 'Todos los estados', { value: '' }));
        const options = tab === 'orders' ? Object.keys(transitions).map((status) => [status, status]) : [['1', 'Activos'], ['0', 'Inactivos']];
        options.forEach(([value, label]) => filter.append(el('option', label, { value })));
        search.value = '';
        render();
    }

    function editUser(user = {}) {
        const form = document.querySelector('#user-form');
        form.reset();
        for (const key of ['id', 'name', 'email', 'phone']) form.elements[key].value = user[key] || '';
        form.elements.active.checked = user.active === undefined || Boolean(user.active);
        document.querySelector('#user-title').textContent = user.id ? 'Editar usuario' : 'Nuevo usuario';
        document.querySelector('#user-message').textContent = '';
        document.querySelector('#user-dialog').showModal();
    }

    function viewOrder(order) {
        const area = document.querySelector('#order-detail');
        area.replaceChildren();
        [
            'SUP-' + order.id + ' · ' + order.status,
            order.customer + ' · ' + order.email,
            order.address,
            order.notes,
        ].filter(Boolean).forEach((value) => area.append(el('p', value)));
        const list = el('ul');
        order.items.forEach((item) => list.append(el('li', item.quantity + ' × ' + item.name + ' · ' + money(item.price * item.quantity))));
        area.append(list, el('strong', 'Total: ' + money(order.total)));
        document.querySelector('#order-dialog').showModal();
    }

    function confirmProduct(product) {
        const dialog = document.querySelector('#confirm-dialog');
        document.querySelector('#confirm-text').textContent = product.name + ' dejará de aparecer en el catálogo. Su historial de compras se conserva.';
        document.querySelector('#confirm-message').textContent = '';
        const button = document.querySelector('#confirm-action');
        button.onclick = async () => {
            button.disabled = true;
            try {
                await api('/admin/products/' + product.id, 'DELETE', {});
                dialog.close();
                await load();
            } catch (error) {
                document.querySelector('#confirm-message').textContent = error.message;
            } finally {
                button.disabled = false;
            }
        };
        dialog.showModal();
    }

    document.querySelector('#user-form').addEventListener('submit', async (event) => {
        event.preventDefault();
        const form = event.currentTarget;
        const data = Object.fromEntries(new FormData(form));
        data.active = form.elements.active.checked;
        const button = form.querySelector('[type="submit"]');
        button.disabled = true;
        try {
            await api('/admin/users' + (data.id ? '/' + data.id : ''), data.id ? 'PUT' : 'POST', data);
            document.querySelector('#user-dialog').close();
            await load();
        } catch (error) {
            document.querySelector('#user-message').textContent = error.message;
        } finally {
            button.disabled = false;
        }
    });

    document.querySelectorAll('[data-close]').forEach((button) => button.addEventListener('click', () => document.getElementById(button.dataset.close).close()));
    document.querySelectorAll('[data-tab]').forEach((button) => {
        button.addEventListener('click', () => setTab(button.dataset.tab));
        button.addEventListener('keydown', (event) => {
            if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
            event.preventDefault();
            const tabs = [...document.querySelectorAll('[data-tab]')];
            const offset = event.key === 'ArrowRight' ? 1 : -1;
            const index = event.key === 'Home' ? 0 : event.key === 'End' ? 2 : (tabs.indexOf(button) + offset + 3) % 3;
            setTab(tabs[index].dataset.tab);
            tabs[index].focus();
        });
    });
    search.addEventListener('input', render);
    filter.addEventListener('change', render);
    document.querySelector('#new-user').addEventListener('click', () => editUser());
    document.querySelector('#refresh').addEventListener('click', () => load().catch(report));
    document.querySelector('#logout').addEventListener('click', async () => {
        try { await api('/logout', 'POST', {}); location.reload(); } catch (error) { report(error); }
    });
    document.querySelector('#access-form').addEventListener('submit', async (event) => {
        event.preventDefault();
        const button = document.querySelector('#access-submit');
        button.disabled = true;
        try {
            await api(setup ? '/setup' : '/login', 'POST', Object.fromEntries(new FormData(event.currentTarget)));
            location.reload();
        } catch (error) { report(error); } finally { button.disabled = false; }
    });

    async function initialize() {
        const session = await api('/session');
        setup = session.setup;
        document.querySelector('#access').hidden = session.authenticated;
        document.querySelector('#workspace').hidden = !session.authenticated;
        document.querySelector('#access-title').textContent = setup ? 'Crear administrador' : 'Administración';
        document.querySelector('#access-submit').textContent = setup ? 'Crear cuenta de administrador' : 'Entrar';
        if (session.authenticated) {
            await load();
            setTab('products');
        }
        icons();
    }
    initialize().catch(report);
})();
