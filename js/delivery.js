(() => {
    const steps = [...document.querySelectorAll('.step')];
    const dots = [...document.querySelectorAll('.steps i')];
    const address = document.querySelector('#address');
    const confirmButton = document.querySelector('#confirm');
    const orderButton = document.querySelector('#order');
    let cart = [];

    try {
        const saved = JSON.parse(localStorage.getItem('supland-cart') || '[]');
        cart = Array.isArray(saved) ? saved : [];
    } catch {
        cart = [];
    }

    function goToStep(number) {
        steps.forEach((step, index) => step.classList.toggle('active', index === number - 1));
        dots.forEach((dot, index) => dot.classList.toggle('on', index < number));
        const heading = steps[number - 1].querySelector('h3');
        heading.setAttribute('tabindex', '-1');
        heading.focus();
    }

    address.addEventListener('input', () => {
        confirmButton.disabled = !address.value.trim();
    });

    confirmButton.addEventListener('click', () => {
        if (address.value.trim()) goToStep(2);
    });

    if (cart.length) {
        const total = cart.reduce((sum, item) => {
            return sum + (Number.isFinite(item.price) ? item.price : 21.5);
        }, 0);
        document.querySelector('#orderName').textContent = cart.length + ' juguete(s) Supland';
        document.querySelector('#orderPrice').textContent = '$' + total.toFixed(2);
        orderButton.querySelector('span').textContent = '$' + total.toFixed(2);
    }

    orderButton.addEventListener('click', () => {
        goToStep(3);
        try {
            localStorage.removeItem('supland-cart');
        } catch {
            // La simulación también funciona sin almacenamiento local.
        }
        window.dispatchEvent(new Event('cart-updated'));
        let minutes = 18;
        const timer = setInterval(() => {
            minutes -= 1;
            document.querySelector('#minutes').textContent = minutes + ' min';
            if (minutes <= 12) clearInterval(timer);
        }, 5000);
    }, { once: true });
})();
