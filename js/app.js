(() => {
    const storageKey = 'supland-cart';
    const menuButton = document.querySelector('.hamb');
    const navigation = document.querySelector('header nav');
    const toast = document.querySelector('.toast');
    let toastTimer;

    function readCart() {
        try {
            const saved = JSON.parse(localStorage.getItem(storageKey) || '[]');
            return Array.isArray(saved) ? saved : [];
        } catch {
            return [];
        }
    }

    function updateCount() {
        document.querySelectorAll('#count').forEach((count) => {
            count.textContent = readCart().length;
        });
    }

    function notify(message) {
        toast.textContent = message;
        toast.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toast.classList.remove('show'), 3000);
    }

    function closeMenu() {
        navigation.classList.remove('open');
        menuButton.setAttribute('aria-expanded', 'false');
        menuButton.setAttribute('aria-label', 'Abrir menú');
    }

    menuButton.addEventListener('click', () => {
        const open = navigation.classList.toggle('open');
        menuButton.setAttribute('aria-expanded', String(open));
        menuButton.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
    });

    navigation.addEventListener('click', (event) => {
        if (event.target.closest('a')) closeMenu();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && navigation.classList.contains('open')) {
            closeMenu();
            menuButton.focus();
        }
    });

    document.querySelectorAll('[data-toy]').forEach((button) => {
        button.addEventListener('click', () => {
            const cart = readCart();
            cart.push({ name: button.dataset.toy, price: Number(button.dataset.price) });
            try {
                localStorage.setItem(storageKey, JSON.stringify(cart));
                updateCount();
                notify(button.dataset.toy + ' está en tu bolsa.');
            } catch {
                notify('No se pudo guardar la bolsa en este navegador.');
            }
        });
    });

    document.querySelector('.bag').addEventListener('click', () => {
        window.location.href = 'delivery.html';
    });

    document.querySelector('#showAll')?.addEventListener('click', () => {
        window.location.href = 'productos.html';
    });

    document.querySelectorAll('.agegrid button').forEach((button) => {
        button.addEventListener('click', () => {
            window.location.href = 'productos.html';
        });
    });

    document.querySelector('.news form').addEventListener('submit', (event) => {
        event.preventDefault();
        notify('La suscripción aún no está disponible. Escríbenos a hola@supland.com.');
    });

    window.addEventListener('storage', updateCount);
    window.addEventListener('pageshow', updateCount);
    window.addEventListener('cart-updated', updateCount);
    updateCount();
})();
