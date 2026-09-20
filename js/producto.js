(() => {
    const { api, icons } = Supland;
    const form = document.querySelector('#product-form');
    const message = document.querySelector('#message');
    const id = new URLSearchParams(location.search).get('id');
    const preview = document.querySelector('#preview-image');
    let image = '';
    let changed = false;

    function showImage() {
        preview.hidden = !image;
        document.querySelector('#image-placeholder').hidden = Boolean(image);
        if (image) preview.src = image;
        else preview.removeAttribute('src');
    }

    form.addEventListener('input', () => { changed = true; });
    form.addEventListener('change', () => {
        document.querySelector('#image-preview').className = 'image-preview color-' + form.elements.color.value;
    });
    window.addEventListener('beforeunload', (event) => {
        if (changed) { event.preventDefault(); event.returnValue = ''; }
    });

    document.querySelector('#image-file').addEventListener('change', async (event) => {
        const file = event.target.files[0];
        if (!file) return;
        if (!['image/png', 'image/jpeg', 'image/webp'].includes(file.type) || file.size > 1000000) {
            message.textContent = 'Selecciona una imagen PNG, JPG o WebP de hasta 1 MB.';
            event.target.value = '';
            return;
        }
        document.querySelector('#save-product').disabled = true;
        try {
            image = await new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve(reader.result);
                reader.onerror = () => reject(new Error('No se pudo leer la imagen.'));
                reader.readAsDataURL(file);
            });
            showImage();
            changed = true;
            message.textContent = '';
        } catch (error) { message.textContent = error.message; }
        finally { document.querySelector('#save-product').disabled = false; }
    });

    document.querySelector('#remove-image').addEventListener('click', () => {
        image = '';
        document.querySelector('#image-file').value = '';
        changed = true;
        showImage();
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const button = document.querySelector('#save-product');
        button.disabled = true;
        const data = Object.fromEntries(new FormData(form));
        data.stock = Number(data.stock);
        data.active = form.elements.active.checked;
        data.image = image;
        try {
            await api('/admin/products' + (id ? '/' + id : ''), id ? 'PUT' : 'POST', data);
            changed = false;
            location.href = 'admin.html';
        } catch (error) {
            message.textContent = error.message;
            message.scrollIntoView({ block: 'center' });
        } finally { button.disabled = false; }
    });

    async function initialize() {
        const session = await api('/session');
        if (!session.authenticated) { location.replace('admin.html'); return; }
        if (id) {
            const products = await api('/admin/products');
            const product = products.find((item) => String(item.id) === id);
            if (!product) throw new Error('El producto no existe. Regresa a Administración.');
            for (const key of ['name', 'description', 'category', 'stock', 'color']) form.elements[key].value = product[key];
            form.elements.price.value = (product.price / 100).toFixed(2);
            form.elements.active.checked = Boolean(product.active);
            image = product.image;
            document.querySelector('#product-heading').textContent = 'Editar producto';
            document.querySelector('#image-preview').className = 'image-preview color-' + product.color;
            showImage();
        }
        form.hidden = false;
        icons();
    }
    initialize().catch((error) => { message.textContent = error.message; });
})();
