/**
 * La Canasta - JavaScript Logic
 */

const WHATSAPP_NUMBER = '56912345678'; 
const DEFAULT_IMG = 'https://placehold.co/400x300/fdf6e3/1a5d2e?text=Producto';

function openWhatsApp(text) {
    const encodedText = encodeURIComponent(text);
    const url = `https://wa.me/${WHATSAPP_NUMBER}?text=${encodedText}`;
    window.open(url, '_blank', 'noopener,noreferrer');
}

function formatCurrency(value) {
    return new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(value);
}

// --- Cart and Catalog Logic ---
let cart = [];

document.addEventListener('DOMContentLoaded', () => {

    // --- Animations and Scroll ---
    const navbar = document.querySelector('.navbar');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 20) {
            navbar.style.boxShadow = '0 4px 6px -1px rgb(0 0 0 / 0.1)';
        } else {
            navbar.style.boxShadow = 'none';
        }
    });

    const observerOptions = { root: null, rootMargin: '0px', threshold: 0.15 };
    const scrollObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.animate-on-scroll').forEach(el => scrollObserver.observe(el));

    // --- Catalog Render ---
    const catalogGrid = document.getElementById('catalogGrid');
    
    function loadCatalog() {
        if (!catalogGrid) return;
        const productsLocal = localStorage.getItem('laCanastaProducts');
        const products = productsLocal ? JSON.parse(productsLocal) : [];

        if (products.length === 0) {
            catalogGrid.innerHTML = `
                <div class="text-center" style="grid-column: 1 / -1; padding: 3rem; background: #fdf6e3; border-radius: 12px; border: 1px dashed #df7b2b;">
                    <p style="font-size: 1.1rem; color: #df7b2b; font-weight: 600;">Aún no hay productos en el catálogo.</p>
                    <p>Ingresa al panel de <a href="admin.html" style="text-decoration: underline; font-weight: bold;">administración</a> para cargar productos.</p>
                </div>`;
            return;
        }

        catalogGrid.innerHTML = '';
        products.forEach(product => {
            const card = document.createElement('div');
            card.className = 'product-card animate-on-scroll';
            const imgUrl = product.image || DEFAULT_IMG;

            card.innerHTML = `
                <div class="product-img-wrapper">
                    <img src="${imgUrl}" alt="${product.name}" onerror="this.src='${DEFAULT_IMG}'" loading="lazy">
                </div>
                <div class="product-details">
                    <h3 class="product-name">${product.name}</h3>
                    <p class="product-desc">${product.description || ''}</p>
                    <div class="product-footer">
                        <span class="product-price">${formatCurrency(product.price)}</span>
                        <button class="btn-add-cart" onclick="addToCart('${product.id}', '${product.name.replace(/'/g, "\\'")}', ${product.price}, '${imgUrl}')" aria-label="Agregar al carrito" title="Agregar al pedido">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </button>
                    </div>
                </div>
            `;
            catalogGrid.appendChild(card);
            scrollObserver.observe(card);
        });
    }

    loadCatalog();

    // --- Shopping Cart ---
    const cartSidebar = document.getElementById('cartSidebar');
    const cartOverlay = document.getElementById('cartOverlay');
    const openCartBtn = document.getElementById('openCartBtn');
    const closeCartBtn = document.getElementById('closeCartBtn');
    const cartItemsContainer = document.getElementById('cartItems');
    const cartTotalValue = document.getElementById('cartTotalValue');
    const cartCount = document.getElementById('cartCount');
    const checkoutBtn = document.getElementById('checkoutBtn');

    // UI Toggle
    function toggleCart() {
        if (!cartSidebar) return;
        cartSidebar.classList.toggle('open');
        cartOverlay.classList.toggle('open');
    }

    if (openCartBtn) openCartBtn.addEventListener('click', toggleCart);
    if (closeCartBtn) closeCartBtn.addEventListener('click', toggleCart);
    if (cartOverlay) cartOverlay.addEventListener('click', toggleCart);

    // Global Add to Cart function
    window.addToCart = function(id, name, price, image) {
        const existingItem = cart.find(item => item.id === id);
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({ id, name, price, image, quantity: 1 });
        }
        updateCartUI();
        toggleCart(); // Open cart to show user action
    }

    window.updateQuantity = function(id, delta) {
        const item = cart.find(item => item.id === id);
        if (item) {
            item.quantity += delta;
            if (item.quantity <= 0) {
                cart = cart.filter(i => i.id !== id);
            }
            updateCartUI();
        }
    }

    // Render cart
    function updateCartUI() {
        if (!cartItemsContainer) return;
        
        let total = 0;
        let count = 0;
        cartItemsContainer.innerHTML = '';

        if (cart.length === 0) {
            cartItemsContainer.innerHTML = '<p class="empty-cart-msg">Tu carrito está vacío</p>';
            checkoutBtn.disabled = true;
            checkoutBtn.style.opacity = '0.5';
        } else {
            checkoutBtn.disabled = false;
            checkoutBtn.style.opacity = '1';
            
            cart.forEach(item => {
                total += item.price * item.quantity;
                count += item.quantity;

                const d = document.createElement('div');
                d.className = 'cart-item';
                d.innerHTML = `
                    <img src="${item.image}" alt="${item.name}" class="cart-item-img" onerror="this.src='${DEFAULT_IMG}'">
                    <div class="cart-item-info">
                        <div class="cart-item-title">${item.name}</div>
                        <div class="cart-item-price">${formatCurrency(item.price)}</div>
                        <div class="cart-item-controls">
                            <button class="qty-btn" onclick="updateQuantity('${item.id}', -1)">-</button>
                            <span>${item.quantity}</span>
                            <button class="qty-btn" onclick="updateQuantity('${item.id}', 1)">+</button>
                        </div>
                    </div>
                    <button class="remove-item" onclick="updateQuantity('${item.id}', -999)" title="Eliminar">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                `;
                cartItemsContainer.appendChild(d);
            });
        }

        cartTotalValue.textContent = formatCurrency(total);
        if (cartCount) cartCount.textContent = count;
        
        // Pulse animation on cart count
        if (cartCount) {
            cartCount.style.animation = 'none';
            setTimeout(() => cartCount.style.animation = 'pulse 0.3s ease-in-out', 10);
        }
    }

    // Checkout via WhatsApp
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', () => {
            if (cart.length === 0) return;
            
            let message = "Hola La Canasta, me gustaría realizar el siguiente pedido:\n\n";
            let total = 0;
            
            cart.forEach(item => {
                message += `- ${item.quantity}x ${item.name} (${formatCurrency(item.price * item.quantity)})\n`;
                total += item.price * item.quantity;
            });
            
            message += `\n*Total: ${formatCurrency(total)}*\n\n`;
            message += "Quedo atento/a para coordinar el despacho. ¡Gracias!";
            
            openWhatsApp(message);
        });
    }

});
