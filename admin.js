/**
 * Admin Panel Logic - La Canasta
 * Uses LocalStorage to persist products
 */

document.addEventListener('DOMContentLoaded', () => {
    const productForm = document.getElementById('productForm');
    const productIdInput = document.getElementById('productId');
    const productNameInput = document.getElementById('productName');
    const productPriceInput = document.getElementById('productPrice');
    const productImageInput = document.getElementById('productImage');
    const productDescInput = document.getElementById('productDesc');
    const productsTableBody = document.getElementById('productsTableBody');
    const submitBtn = document.getElementById('submitBtn');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    const seedProductsBtn = document.getElementById('seedProductsBtn');

    // Default image fallback
    const DEFAULT_IMG = 'https://placehold.co/400x300/fdf6e3/1a5d2e?text=Producto';

    // Current editing state
    let isEditing = false;

    // Load products from Local Storage
    function getProducts() {
        const products = localStorage.getItem('laCanastaProducts');
        return products ? JSON.parse(products) : [];
    }

    // Save products to Local Storage
    function saveProducts(products) {
        localStorage.setItem('laCanastaProducts', JSON.stringify(products));
    }

    // Format currency
    function formatCurrency(value) {
        return new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(value);
    }

    // Render table
    function renderTable() {
        const products = getProducts();
        productsTableBody.innerHTML = '';

        if (products.length === 0) {
            productsTableBody.innerHTML = '<tr><td colspan="4" class="text-center" style="padding: 2rem;">No hay productos cargados.</td></tr>';
            return;
        }

        products.forEach(product => {
            const tr = document.createElement('tr');
            const imgUrl = product.image || DEFAULT_IMG;

            tr.innerHTML = `
                <td><img src="${imgUrl}" alt="${product.name}" class="img-preview" onerror="this.src='${DEFAULT_IMG}'"></td>
                <td>
                    <strong>${product.name}</strong><br>
                    <small style="color: #6b7280">${product.description || 'Sin descripción'}</small>
                </td>
                <td>${formatCurrency(product.price)}</td>
                <td>
                    <button class="action-btn edit-btn" data-id="${product.id}" title="Editar">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    </button>
                    <button class="action-btn delete-btn delete" data-id="${product.id}" title="Eliminar">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                    </button>
                </td>
            `;
            productsTableBody.appendChild(tr);
        });

        // Add event listeners to newly created buttons
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', (e) => editProduct(e.currentTarget.dataset.id));
        });
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', (e) => deleteProduct(e.currentTarget.dataset.id));
        });
    }

    // Generate unique ID
    function generateId() {
        return '_' + Math.random().toString(36).substr(2, 9);
    }

    // Handle Form Submit
    productForm.addEventListener('submit', (e) => {
        e.preventDefault();
        
        const products = getProducts();
        
        const productData = {
            name: productNameInput.value.trim(),
            price: parseFloat(productPriceInput.value),
            image: productImageInput.value.trim(),
            description: productDescInput.value.trim()
        };

        if (isEditing) {
            const index = products.findIndex(p => p.id === productIdInput.value);
            if (index !== -1) {
                products[index] = { ...products[index], ...productData };
            }
        } else {
            productData.id = generateId();
            products.push(productData);
        }

        saveProducts(products);
        resetForm();
        renderTable();
    });

    // Edit Product
    function editProduct(id) {
        const products = getProducts();
        const product = products.find(p => p.id === id);
        
        if (product) {
            productIdInput.value = product.id;
            productNameInput.value = product.name;
            productPriceInput.value = product.price;
            productImageInput.value = product.image || '';
            productDescInput.value = product.description || '';
            
            isEditing = true;
            submitBtn.textContent = 'Actualizar Producto';
            cancelEditBtn.style.display = 'inline-block';
            
            // Scroll to form
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    // Delete Product
    function deleteProduct(id) {
        if (confirm('¿Estás seguro de que quieres eliminar este producto?')) {
            let products = getProducts();
            products = products.filter(p => p.id !== id);
            saveProducts(products);
            renderTable();
        }
    }

    // Reset Form
    function resetForm() {
        productForm.reset();
        productIdInput.value = '';
        isEditing = false;
        submitBtn.textContent = 'Guardar Producto';
        cancelEditBtn.style.display = 'none';
    }

    cancelEditBtn.addEventListener('click', resetForm);

    // Seed test products
    seedProductsBtn.addEventListener('click', () => {
        if (confirm('Esto agregará algunos productos de prueba. ¿Continuar?')) {
            const seed = [
                { id: generateId(), name: 'Aceite Maravilla 1L', price: 1950, description: 'Caja de 12 Botellas de 1 Litro. Especial para frituras.', image: 'https://placehold.co/400x300/fdf6e3/1a5d2e?text=Aceite' },
                { id: generateId(), name: 'Arroz Grado 2 Tucapel', price: 1400, description: 'Bolsa de 1KG. Compra mínima 10 unidades.', image: 'https://placehold.co/400x300/fdf6e3/1a5d2e?text=Arroz' },
                { id: generateId(), name: 'Harina Selecta S/P', price: 1350, description: 'Paquete de 1KG. Fardo de 10 unidades.', image: 'https://placehold.co/400x300/fdf6e3/1a5d2e?text=Harina' },
                { id: generateId(), name: 'Jurel San José', price: 1550, description: 'Tarro 425g al natural. Caja de 24 unidades.', image: 'https://placehold.co/400x300/fdf6e3/1a5d2e?text=Jurel' },
                { id: generateId(), name: 'Azúcar Blanca Iansa', price: 1250, description: 'Bolsa de 1KG. Fardo de 10 paquetes.', image: 'https://placehold.co/400x300/fdf6e3/1a5d2e?text=Azucar' },
                { id: generateId(), name: 'Papel Higiénico Favorito', price: 3800, description: 'Paquete 4 rollos doble hoja. Pack x 6.', image: 'https://placehold.co/400x300/fdf6e3/1a5d2e?text=Confort' }
            ];
            
            const current = getProducts();
            saveProducts([...current, ...seed]);
            renderTable();
        }
    });

    // Initial render
    renderTable();
});
