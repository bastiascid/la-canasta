/**
 * La Canasta Distribuidora - Admin Panel JS (Redesign Upgrade)
 */

window.onerror = function(message, source, lineno, colno, error) {
    alert("Runtime Error: " + message + "\nIn: " + source + "\nLine: " + lineno + "\nCol: " + colno + (error ? "\nStack: " + error.stack : ""));
    return false;
};

const PASSCODE = 'admin123';

let brandsCache = [];
let leadsCache = [];
let claimsCache = [];
let offersCache = [];
let coverageCache = [];
let partnersCache = [];
let subBrandsCache = [];

document.addEventListener('DOMContentLoaded', () => {
    
    // Auth Nodes
    const adminAuth = document.getElementById('adminAuth');
    const adminPanel = document.getElementById('adminPanel');
    const loginForm = document.getElementById('loginForm');
    const passcodeVal = document.getElementById('passcode');
    const loginError = document.getElementById('loginError');
    const logoutBtn = document.getElementById('logoutBtn');

    // Tab Links
    const tabButtons = document.querySelectorAll('.admin-tab-btn');
    const tabContents = document.querySelectorAll('.admin-tab-content');

    // Forms
    const brandAdminForm = document.getElementById('brandAdminForm');
    const productAdminForm = document.getElementById('productAdminForm');
    const settingsAdminForm = document.getElementById('settingsAdminForm');
    const partnerAdminForm = document.getElementById('partnerAdminForm');
    const subBrandAdminForm = document.getElementById('subBrandAdminForm');

    // --- 1. Authentication ---
    function checkLogin() {
        const loggedIn = sessionStorage.getItem('laCanastaAdminLoggedIn') === 'true';
        if (loggedIn) {
            adminAuth.style.display = 'none';
            adminPanel.style.display = 'block';
            loadDashboard(); // Default tab load
        } else {
            adminAuth.style.display = 'flex';
            adminPanel.style.display = 'none';
        }
    }

    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            if (passcodeVal.value === PASSCODE) {
                sessionStorage.setItem('laCanastaAdminLoggedIn', 'true');
                sessionStorage.setItem('laCanastaAdminPasscode', passcodeVal.value);
                loginError.style.display = 'none';
                passcodeVal.value = '';
                checkLogin();
            } else {
                loginError.style.display = 'block';
                passcodeVal.focus();
            }
        });
    }

    if (logoutBtn) {
        logoutBtn.addEventListener('click', () => {
            sessionStorage.removeItem('laCanastaAdminLoggedIn');
            sessionStorage.removeItem('laCanastaAdminPasscode');
            checkLogin();
        });
    }

    // Initialize Login check
    checkLogin();

    // --- 2. Tab Navigation ---
    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            tabButtons.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
            
            btn.classList.add('active');
            const tabId = btn.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
            
            // Load specific tab data
            if (tabId === 'tab-dashboard') loadDashboard();
            else if (tabId === 'tab-brands') loadBrands();
            else if (tabId === 'tab-products') loadProducts();
            else if (tabId === 'tab-offers') loadOffers();
            else if (tabId === 'tab-coverage') loadCoverage();
            else if (tabId === 'tab-partners') loadPartners();
            else if (tabId === 'tab-subbrands') loadSubBrands();
            else if (tabId === 'tab-leads') loadLeads();
            else if (tabId === 'tab-claims') loadClaims();
            else if (tabId === 'tab-settings') loadSettings();
        });
    });

    // Helper to get Auth Headers
    function getAuthHeaders() {
        return {
            'Content-Type': 'application/json',
            'X-Admin-Passcode': sessionStorage.getItem('laCanastaAdminPasscode')
        };
    }

    // --- 3. Dashboard Operations ---
    function loadDashboard() {
        const headers = getAuthHeaders();
        
        // Fetch stats: leads, brands, products, offers, coverage count
        Promise.all([
            fetch('api/leads.php', { headers }).then(res => res.json()),
            fetch('api/brands.php', { headers }).then(res => res.json()),
            fetch('api/products.php', { headers }).then(res => res.json()),
            fetch('api/offers.php', { headers }).then(res => res.json()),
            fetch('api/coverage.php', { headers }).then(res => res.json()),
            fetch('api/partners.php', { headers }).then(res => res.json())
        ])
        .then(([leadsRes, brandsRes, productsRes, offersRes, coverageRes, partnersRes]) => {
            if (leadsRes.status === 'success') {
                document.getElementById('statLeadsCount').textContent = leadsRes.data.length;
                renderRecentLeads(leadsRes.data.slice(0, 5));
            }
            if (brandsRes.status === 'success') {
                document.getElementById('statBrandsCount').textContent = brandsRes.data.length;
            }
            if (productsRes.status === 'success') {
                document.getElementById('statProductsCount').textContent = productsRes.data.length;
            }
            if (offersRes.status === 'success') {
                document.getElementById('statOffersCount').textContent = offersRes.data.length;
            }
            if (coverageRes.status === 'success') {
                document.getElementById('statCoverageCount').textContent = coverageRes.data.length;
            }
            if (partnersRes.status === 'success') {
                document.getElementById('statPartnersCount').textContent = partnersRes.data.length;
            }
        })
        .catch(err => console.error("Error loading dashboard metrics:", err));
    }

    function renderRecentLeads(leads) {
        const container = document.getElementById('recentLeadsBody');
        if (!container) return;
        
        if (leads.length === 0) {
            container.innerHTML = `<tr><td colspan="6" class="text-center">No hay contactos registrados aun.</td></tr>`;
            return;
        }
        
        container.innerHTML = leads.map(lead => `
            <tr>
                <td>${lead.created_at}</td>
                <td><strong>${lead.name}</strong></td>
                <td>${lead.company || '-'}</td>
                <td>${lead.phone || '-'}</td>
                <td>${lead.email}</td>
                <td>${lead.region || '-'}</td>
            </tr>
        `).join('');
    }

    // --- 4. Brands Operations ---
    function loadBrands() {
        const headers = getAuthHeaders();
        fetch('api/brands.php', { headers })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    brandsCache = res.data;
                    renderBrandsTable();
                    populateBrandDropdown();
                }
            })
            .catch(err => console.error("Error loading brands:", err));
    }

    function renderBrandsTable() {
        const container = document.getElementById('brandsTableBody');
        if (!container) return;
        
        if (brandsCache.length === 0) {
            container.innerHTML = `<tr><td colspan="5" class="text-center">No hay marcas registradas.</td></tr>`;
            return;
        }
        
        container.innerHTML = brandsCache.map(brand => `
            <tr>
                <td><img src="${brand.logo_url}" alt="Logo" style="height: 30px; max-width: 80px; object-fit: contain;" onerror="this.src='https://placehold.co/120x80/0f2c59/ffffff?text=${brand.name}'"></td>
                <td><strong>${brand.name}</strong></td>
                <td><span class="badge" style="background-color: ${brand.status === 'Activa' ? '#1a5d2e' : '#ef4444'}; font-size: 0.75rem; padding: 0.15rem 0.5rem; margin:0;">${brand.status}</span></td>
                <td>${brand.sort_order}</td>
                <td style="text-align: center;">
                    <div style="display: flex; gap: 0.5rem; justify-content: center;">
                        <button class="btn-edit" onclick="editBrand(${brand.id})" title="Editar" style="border:0; background:none; cursor:pointer; color: var(--color-primary);">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        </button>
                        <button class="btn-delete" onclick="deleteBrand(${brand.id})" title="Eliminar" style="border:0; background:none; cursor:pointer; color: #ef4444;">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    if (brandAdminForm) {
        brandAdminForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const payload = {
                id: document.getElementById('brand-id').value,
                name: document.getElementById('brand-name').value.trim(),
                logo_url: document.getElementById('brand-logo').value.trim(),
                image_url: document.getElementById('brand-image').value.trim(),
                description: document.getElementById('brand-description').value.trim(),
                status: document.getElementById('brand-status').value,
                sort_order: parseInt(document.getElementById('brand-order').value) || 0,
                official_url: document.getElementById('brand-url').value.trim(),
                slug: document.getElementById('brand-slug').value.trim(),
                history: document.getElementById('brand-history').value.trim()
            };
            
            fetch('api/brands.php', {
                method: 'POST',
                headers: getAuthHeaders(),
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    alert(res.message);
                    brandAdminForm.reset();
                    document.getElementById('brand-id').value = '';
                    document.getElementById('brandFormTitle').textContent = 'Agregar Nueva Marca';
                    document.getElementById('cancelBrandEditBtn').style.display = 'none';
                    loadBrands();
                } else {
                    alert('Error: ' + res.message);
                }
            })
            .catch(err => console.error("Error saving brand:", err));
        });
        
        document.getElementById('cancelBrandEditBtn').addEventListener('click', () => {
            brandAdminForm.reset();
            document.getElementById('brand-id').value = '';
            document.getElementById('brandFormTitle').textContent = 'Agregar Nueva Marca';
            document.getElementById('cancelBrandEditBtn').style.display = 'none';
        });
    }

    window.editBrand = function(id) {
        const brand = brandsCache.find(b => b.id == id);
        if (!brand) return;
        
        document.getElementById('brand-id').value = brand.id;
        document.getElementById('brand-name').value = brand.name;
        document.getElementById('brand-logo').value = brand.logo_url || '';
        document.getElementById('brand-image').value = brand.image_url || '';
        document.getElementById('brand-description').value = brand.description || '';
        document.getElementById('brand-slug').value = brand.slug || '';
        document.getElementById('brand-history').value = brand.history || '';
        document.getElementById('brand-status').value = brand.status;
        document.getElementById('brand-order').value = brand.sort_order;
        document.getElementById('brand-url').value = brand.official_url || '';
        
        document.getElementById('brandFormTitle').textContent = 'Editar Marca: ' + brand.name;
        document.getElementById('cancelBrandEditBtn').style.display = 'inline-flex';
        document.getElementById('brand-name').focus();
    };

    window.deleteBrand = function(id) {
        if (confirm('Atención: ¿Seguro que deseas eliminar esta marca? Se eliminarán todos los productos asociados.')) {
            fetch(`api/brands.php?action=delete&id=${id}`, {
                method: 'POST',
                headers: getAuthHeaders()
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    alert(res.message);
                    loadBrands();
                } else {
                    alert('Error al eliminar la marca: ' + res.message);
                }
            })
            .catch(err => console.error("Error deleting brand:", err));
        }
    };

    // --- 5. Products Operations ---
    let productsCache = [];

    function loadProducts() {
        const headers = getAuthHeaders();
        
        const fetchProducts = () => {
            fetch('api/products.php', { headers })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        productsCache = res.data;
                        renderProductsTable();
                    }
                })
                .catch(err => console.error("Error loading products:", err));
        };

        if (brandsCache.length === 0) {
            fetch('api/brands.php', { headers })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        brandsCache = res.data;
                        populateBrandDropdown();
                    }
                    fetchProducts();
                })
                .catch(err => {
                    console.error("Error loading brands before products:", err);
                    fetchProducts();
                });
        } else {
            fetchProducts();
        }
    }

    function renderProductsTable() {
        const container = document.getElementById('productsTableBody');
        if (!container) return;
        
        if (productsCache.length === 0) {
            container.innerHTML = `<tr><td colspan="6" class="text-center">No hay productos registrados.</td></tr>`;
            return;
        }
        
        container.innerHTML = productsCache.map(product => {
            const brand = brandsCache.find(b => b.id == product.brand_id);
            const brandName = brand ? brand.name : 'Desconocida';
            
            return `
                <tr>
                    <td><img src="${product.image_url}" alt="Img" style="height: 30px; width: 30px; object-fit: cover; border-radius:4px;" onerror="this.src='https://placehold.co/100x100/f7f5f3/5d4637?text=Prod'"></td>
                    <td><strong>${product.name}</strong></td>
                    <td>${brandName}</td>
                    <td>${product.category || '-'}</td>
                    <td><span class="badge" style="background-color: ${product.featured == 1 ? 'var(--color-secondary)' : '#6b7280'}; font-size: 0.75rem; padding: 0.15rem 0.5rem; margin:0;">${product.featured == 1 ? 'Sí' : 'No'}</span></td>
                    <td style="text-align: center;">
                        <div style="display: flex; gap: 0.5rem; justify-content: center;">
                            <button class="btn-edit" onclick="editProduct(${product.id})" title="Editar" style="border:0; background:none; cursor:pointer; color: var(--color-primary);">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            </button>
                            <button class="btn-delete" onclick="deleteProduct(${product.id})" title="Eliminar" style="border:0; background:none; cursor:pointer; color: #ef4444;">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function populateBrandDropdown() {
        const dropdown = document.getElementById('product-brand');
        if (!dropdown) return;
        
        dropdown.innerHTML = '<option value="" disabled selected>Selecciona una marca</option>' + 
            brandsCache.map(b => `<option value="${b.id}">${b.name}</option>`).join('');
    }

    if (productAdminForm) {
        productAdminForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const payload = {
                id: document.getElementById('product-id').value,
                name: document.getElementById('product-name').value.trim(),
                brand_id: document.getElementById('product-brand').value,
                image_url: document.getElementById('product-image').value.trim(),
                description: document.getElementById('product-description').value.trim(),
                category: document.getElementById('product-category').value.trim(),
                featured: document.getElementById('product-featured').checked ? 1 : 0,
                sort_order: parseInt(document.getElementById('product-order').value) || 0
            };
            
            fetch('api/products.php', {
                method: 'POST',
                headers: getAuthHeaders(),
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    alert(res.message);
                    productAdminForm.reset();
                    document.getElementById('product-id').value = '';
                    document.getElementById('productFormTitle').textContent = 'Agregar Nuevo Producto';
                    document.getElementById('cancelProductEditBtn').style.display = 'none';
                    loadProducts();
                } else {
                    alert('Error: ' + res.message);
                }
            })
            .catch(err => console.error("Error saving product:", err));
        });
        
        document.getElementById('cancelProductEditBtn').addEventListener('click', () => {
            productAdminForm.reset();
            document.getElementById('product-id').value = '';
            document.getElementById('productFormTitle').textContent = 'Agregar Nuevo Producto';
            document.getElementById('cancelProductEditBtn').style.display = 'none';
        });
    }

    window.editProduct = function(id) {
        const product = productsCache.find(p => p.id == id);
        if (!product) return;
        
        document.getElementById('product-id').value = product.id;
        document.getElementById('product-name').value = product.name;
        document.getElementById('product-brand').value = product.brand_id;
        document.getElementById('product-image').value = product.image_url || '';
        document.getElementById('product-description').value = product.description || '';
        document.getElementById('product-category').value = product.category || '';
        document.getElementById('product-featured').checked = product.featured == 1;
        document.getElementById('product-order').value = product.sort_order;
        
        document.getElementById('productFormTitle').textContent = 'Editar Producto: ' + product.name;
        document.getElementById('cancelProductEditBtn').style.display = 'inline-flex';
        document.getElementById('product-name').focus();
    };

    window.deleteProduct = function(id) {
        if (confirm('¿Seguro que deseas eliminar este producto del catálogo?')) {
            fetch(`api/products.php?action=delete&id=${id}`, {
                method: 'POST',
                headers: getAuthHeaders()
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    alert(res.message);
                    loadProducts();
                } else {
                    alert('Error al eliminar el producto: ' + res.message);
                }
            })
            .catch(err => console.error("Error deleting product:", err));
        }
    };

    // --- Offers Operations ---
    function loadOffers() {
        const headers = getAuthHeaders();
        fetch('api/offers.php', { headers })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    offersCache = res.data;
                    renderOffersTable();
                }
            })
            .catch(err => console.error("Error loading offers:", err));
    }
    
    function renderOffersTable() {
        const container = document.getElementById('offersTableBody');
        if (!container) return;
        
        if (offersCache.length === 0) {
            container.innerHTML = `<tr><td colspan="5" class="text-center">No hay ofertas o banners registrados.</td></tr>`;
            return;
        }
        
        container.innerHTML = offersCache.map(offer => `
            <tr>
                <td><strong>${offer.title}</strong></td>
                <td><span style="font-size: 0.85rem; font-weight: 600; color: var(--color-primary);">${offer.type || 'Campañas comerciales'}</span></td>
                <td><span class="badge" style="background-color: ${offer.status === 'Activa' ? '#1a5d2e' : '#ef4444'}; font-size: 0.75rem; padding: 0.15rem 0.5rem; margin:0;">${offer.status}</span></td>
                <td>${offer.sort_order}</td>
                <td style="text-align: center;">
                    <div style="display: flex; gap: 0.5rem; justify-content: center;">
                        <button class="btn-edit" onclick="editOffer(${offer.id})" title="Editar" style="border:0; background:none; cursor:pointer; color: var(--color-primary);">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        </button>
                        <button class="btn-delete" onclick="deleteOffer(${offer.id})" title="Eliminar" style="border:0; background:none; cursor:pointer; color: #ef4444;">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }
    
    const offerAdminForm = document.getElementById('offerAdminForm');
    if (offerAdminForm) {
        offerAdminForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const payload = {
                id: document.getElementById('offer-id').value,
                title: document.getElementById('offer-title').value.trim(),
                image_url: document.getElementById('offer-image').value.trim(),
                description: document.getElementById('offer-description').value.trim(),
                link_url: document.getElementById('offer-link').value.trim(),
                status: document.getElementById('offer-status').value,
                type: document.getElementById('offer-type').value,
                sort_order: parseInt(document.getElementById('offer-order').value) || 0
            };
            
            fetch('api/offers.php', {
                method: 'POST',
                headers: getAuthHeaders(),
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    alert(res.message);
                    offerAdminForm.reset();
                    document.getElementById('offer-id').value = '';
                    document.getElementById('offerFormTitle').textContent = 'Agregar Nueva Oferta';
                    document.getElementById('cancelOfferEditBtn').style.display = 'none';
                    loadOffers();
                } else {
                    alert('Error: ' + res.message);
                }
            })
            .catch(err => console.error("Error saving offer:", err));
        });
        
        document.getElementById('cancelOfferEditBtn').addEventListener('click', () => {
            offerAdminForm.reset();
            document.getElementById('offer-id').value = '';
            document.getElementById('offerFormTitle').textContent = 'Agregar Nueva Oferta';
            document.getElementById('cancelOfferEditBtn').style.display = 'none';
        });
    }
    
    window.editOffer = function(id) {
        const offer = offersCache.find(o => o.id == id);
        if (!offer) return;
        
        document.getElementById('offer-id').value = offer.id;
        document.getElementById('offer-title').value = offer.title;
        document.getElementById('offer-image').value = offer.image_url || '';
        document.getElementById('offer-description').value = offer.description || '';
        document.getElementById('offer-link').value = offer.link_url || '#';
        document.getElementById('offer-status').value = offer.status;
        document.getElementById('offer-type').value = offer.type || 'Campañas comerciales';
        document.getElementById('offer-order').value = offer.sort_order;
        
        document.getElementById('offerFormTitle').textContent = 'Editar Oferta: ' + offer.title;
        document.getElementById('cancelOfferEditBtn').style.display = 'inline-flex';
        document.getElementById('offer-title').focus();
    };
    
    window.deleteOffer = function(id) {
        if (confirm('¿Seguro que deseas eliminar esta oferta?')) {
            fetch(`api/offers.php?action=delete&id=${id}`, {
                method: 'POST',
                headers: getAuthHeaders()
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    alert(res.message);
                    loadOffers();
                } else {
                    alert('Error al eliminar la oferta: ' + res.message);
                }
            })
            .catch(err => console.error("Error deleting offer:", err));
        }
    };

    // --- Coverage Operations ---
    function loadCoverage() {
        const headers = getAuthHeaders();
        fetch('api/coverage.php', { headers })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    coverageCache = res.data;
                    renderCoverageTable();
                }
            })
            .catch(err => console.error("Error loading coverage:", err));
    }
    
    function renderCoverageTable() {
        const container = document.getElementById('coverageTableBody');
        if (!container) return;
        
        if (coverageCache.length === 0) {
            container.innerHTML = `<tr><td colspan="4" class="text-center">No hay comunas registradas.</td></tr>`;
            return;
        }
        
        container.innerHTML = coverageCache.map(zone => `
            <tr>
                <td><strong>${zone.name}</strong></td>
                <td>${zone.region}</td>
                <td>${zone.sort_order}</td>
                <td style="text-align: center;">
                    <div style="display: flex; gap: 0.5rem; justify-content: center;">
                        <button class="btn-edit" onclick="editCoverage(${zone.id})" title="Editar" style="border:0; background:none; cursor:pointer; color: var(--color-primary);">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        </button>
                        <button class="btn-delete" onclick="deleteCoverage(${zone.id})" title="Eliminar" style="border:0; background:none; cursor:pointer; color: #ef4444;">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }
    
    const coverageAdminForm = document.getElementById('coverageAdminForm');
    if (coverageAdminForm) {
        coverageAdminForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const payload = {
                id: document.getElementById('coverage-id').value,
                name: document.getElementById('coverage-name').value.trim(),
                region: document.getElementById('coverage-region').value.trim(),
                sort_order: parseInt(document.getElementById('coverage-order').value) || 0
            };
            
            fetch('api/coverage.php', {
                method: 'POST',
                headers: getAuthHeaders(),
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    alert(res.message);
                    coverageAdminForm.reset();
                    document.getElementById('coverage-id').value = '';
                    document.getElementById('coverageFormTitle').textContent = 'Agregar Nueva Zona';
                    document.getElementById('cancelCoverageEditBtn').style.display = 'none';
                    loadCoverage();
                } else {
                    alert('Error: ' + res.message);
                }
            })
            .catch(err => console.error("Error saving coverage zone:", err));
        });
        
        document.getElementById('cancelCoverageEditBtn').addEventListener('click', () => {
            coverageAdminForm.reset();
            document.getElementById('coverage-id').value = '';
            document.getElementById('coverageFormTitle').textContent = 'Agregar Nueva Zona';
            document.getElementById('cancelCoverageEditBtn').style.display = 'none';
        });
    }
    
    window.editCoverage = function(id) {
        const zone = coverageCache.find(z => z.id == id);
        if (!zone) return;
        
        document.getElementById('coverage-id').value = zone.id;
        document.getElementById('coverage-name').value = zone.name;
        document.getElementById('coverage-region').value = zone.region || "Región de O'Higgins";
        document.getElementById('coverage-order').value = zone.sort_order;
        
        document.getElementById('coverageFormTitle').textContent = 'Editar Zona: ' + zone.name;
        document.getElementById('cancelCoverageEditBtn').style.display = 'inline-flex';
        document.getElementById('coverage-name').focus();
    };
    
    window.deleteCoverage = function(id) {
        if (confirm('¿Seguro que deseas eliminar esta zona de cobertura?')) {
            fetch(`api/coverage.php?action=delete&id=${id}`, {
                method: 'POST',
                headers: getAuthHeaders()
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    alert(res.message);
                    loadCoverage();
                } else {
                    alert('Error al eliminar la zona de cobertura: ' + res.message);
                }
            })
            .catch(err => console.error("Error deleting coverage zone:", err));
        }
    };

    // --- 6. Leads Database Operations ---
    function loadLeads() {
        const headers = getAuthHeaders();
        
        const search = document.getElementById('filter-lead-search') ? document.getElementById('filter-lead-search').value.trim() : '';
        const region = document.getElementById('filter-lead-region') ? document.getElementById('filter-lead-region').value : '';
        const status = document.getElementById('filter-lead-status') ? document.getElementById('filter-lead-status').value : '';
        const startDate = document.getElementById('filter-lead-start') ? document.getElementById('filter-lead-start').value : '';
        const endDate = document.getElementById('filter-lead-end') ? document.getElementById('filter-lead-end').value : '';
        
        let url = 'api/leads.php?';
        const params = new URLSearchParams();
        if (search) params.append('search', search);
        if (region) params.append('region', region);
        if (status) params.append('status', status);
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
        
        url += params.toString();
        
        fetch(url, { headers })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    leadsCache = res.data;
                    renderLeadsTable();
                }
            })
            .catch(err => console.error("Error loading leads database:", err));
    }

    function renderLeadsTable() {
        const container = document.getElementById('leadsTableBody');
        if (!container) return;
        
        if (leadsCache.length === 0) {
            container.innerHTML = `<tr><td colspan="10" class="text-center">No hay registros de contacto que coincidan con la búsqueda.</td></tr>`;
            return;
        }
        
        container.innerHTML = leadsCache.map(lead => `
            <tr>
                <td style="white-space: nowrap;">${lead.created_at}</td>
                <td><strong>${lead.name}</strong></td>
                <td>${lead.company || '-'}</td>
                <td>${lead.role || '-'}</td>
                <td>${lead.phone || '-'}</td>
                <td><a href="mailto:${lead.email}" style="text-decoration: underline; color: var(--color-primary);">${lead.email}</a></td>
                <td>${lead.region || '-'}</td>
                <td style="max-width: 200px; font-size: 0.85rem; line-height: 1.4;">${lead.comments || '-'}</td>
                <td><span style="font-size: 0.75rem; background: #e5e7eb; padding: 2px 6px; border-radius: 4px; font-weight: 700; color: var(--color-primary); white-space: nowrap;">${lead.origin || 'General'}</span></td>
                <td>
                    <select onchange="updateLeadStatus(${lead.id}, this.value)" style="padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; border: 1px solid var(--color-border); background: white; font-weight: 700; cursor: pointer; color: ${lead.status === 'Nuevo' ? '#2563eb' : lead.status === 'Contactado' ? '#d97706' : lead.status === 'En Seguimiento' ? '#7c3aed' : '#059669'}">
                        <option value="Nuevo" ${lead.status === 'Nuevo' ? 'selected' : ''}>Nuevo</option>
                        <option value="Contactado" ${lead.status === 'Contactado' ? 'selected' : ''}>Contactado</option>
                        <option value="En Seguimiento" ${lead.status === 'En Seguimiento' ? 'selected' : ''}>En Seguimiento</option>
                        <option value="Cliente" ${lead.status === 'Cliente' ? 'selected' : ''}>Cliente</option>
                    </select>
                </td>
            </tr>
        `).join('');
    }

    window.updateLeadStatus = function(id, newStatus) {
        fetch('api/leads.php?action=update_status', {
            method: 'POST',
            headers: getAuthHeaders(),
            body: JSON.stringify({ id, status: newStatus })
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                loadLeads();
            } else {
                alert('Error al actualizar estado: ' + res.message);
            }
        })
        .catch(err => console.error("Error updating lead status:", err));
    };

    // Lead filter event listeners
    const applyLeadFiltersBtn = document.getElementById('applyLeadFiltersBtn');
    const clearLeadFiltersBtn = document.getElementById('clearLeadFiltersBtn');
    
    if (applyLeadFiltersBtn) {
        applyLeadFiltersBtn.addEventListener('click', loadLeads);
    }
    
    if (clearLeadFiltersBtn) {
        clearLeadFiltersBtn.addEventListener('click', () => {
            if (document.getElementById('filter-lead-search')) document.getElementById('filter-lead-search').value = '';
            if (document.getElementById('filter-lead-region')) document.getElementById('filter-lead-region').value = '';
            if (document.getElementById('filter-lead-status')) document.getElementById('filter-lead-status').value = '';
            if (document.getElementById('filter-lead-start')) document.getElementById('filter-lead-start').value = '';
            if (document.getElementById('filter-lead-end')) document.getElementById('filter-lead-end').value = '';
            loadLeads();
        });
    }

    // Export Buttons Action
    const exportCsvBtn = document.getElementById('exportCsvBtn');
    const exportXlsBtn = document.getElementById('exportXlsBtn');
    
    if (exportCsvBtn) {
        exportCsvBtn.addEventListener('click', () => {
            const passcode = sessionStorage.getItem('laCanastaAdminPasscode');
            const search = document.getElementById('filter-lead-search') ? document.getElementById('filter-lead-search').value.trim() : '';
            const region = document.getElementById('filter-lead-region') ? document.getElementById('filter-lead-region').value : '';
            const status = document.getElementById('filter-lead-status') ? document.getElementById('filter-lead-status').value : '';
            const startDate = document.getElementById('filter-lead-start') ? document.getElementById('filter-lead-start').value : '';
            const endDate = document.getElementById('filter-lead-end') ? document.getElementById('filter-lead-end').value : '';
            
            const params = new URLSearchParams();
            params.append('format', 'csv');
            params.append('passcode', passcode);
            if (search) params.append('search', search);
            if (region) params.append('region', region);
            if (status) params.append('status', status);
            if (startDate) params.append('start_date', startDate);
            if (endDate) params.append('end_date', endDate);
            
            window.open(`api/export.php?${params.toString()}`, '_blank');
        });
    }
    
    if (exportXlsBtn) {
        exportXlsBtn.addEventListener('click', () => {
            const passcode = sessionStorage.getItem('laCanastaAdminPasscode');
            const search = document.getElementById('filter-lead-search') ? document.getElementById('filter-lead-search').value.trim() : '';
            const region = document.getElementById('filter-lead-region') ? document.getElementById('filter-lead-region').value : '';
            const status = document.getElementById('filter-lead-status') ? document.getElementById('filter-lead-status').value : '';
            const startDate = document.getElementById('filter-lead-start') ? document.getElementById('filter-lead-start').value : '';
            const endDate = document.getElementById('filter-lead-end') ? document.getElementById('filter-lead-end').value : '';
            
            const params = new URLSearchParams();
            params.append('format', 'xls');
            params.append('passcode', passcode);
            if (search) params.append('search', search);
            if (region) params.append('region', region);
            if (status) params.append('status', status);
            if (startDate) params.append('start_date', startDate);
            if (endDate) params.append('end_date', endDate);
            
            window.open(`api/export.php?${params.toString()}`, '_blank');
        });
    }

    // --- 7. Settings & Config Panel ---
    function loadSettings() {
        fetch('api/settings.php')
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success' && res.data) {
                    const settings = res.data;
                    document.getElementById('settings-logo').value = settings.logo_url || 'assets/canasta-logo.webp';
                    document.getElementById('settings-wa-enabled').checked = settings.whatsapp_enabled === '1';
                    document.getElementById('settings-wa-number').value = settings.whatsapp_number || '+56 9 4256 7472';
                    
                    // Welcome Promo Popup
                    document.getElementById('settings-popup-enabled').checked = settings.welcome_popup_enabled === '1';
                    document.getElementById('settings-popup-image').value = settings.welcome_popup_image || 'assets/welcome-popup-banner.webp';
                    document.getElementById('settings-popup-title').value = settings.welcome_popup_title || '¡Bienvenidos a La Canasta!';
                    document.getElementById('settings-popup-desc').value = settings.welcome_popup_description || 'Somos el distribuidor mayorista líder de la Región de O\'Higgins. Abastécete con marcas de alta rotación directo en tu local.';
                    document.getElementById('settings-popup-btn').value = settings.welcome_popup_btn_text || '¡Comenzar Pedido!';
                }
            })
            .catch(err => console.error("Error loading settings:", err));
    }

    if (settingsAdminForm) {
        settingsAdminForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const payload = {
                logo_url: document.getElementById('settings-logo').value.trim(),
                whatsapp_enabled: document.getElementById('settings-wa-enabled').checked ? '1' : '0',
                whatsapp_number: document.getElementById('settings-wa-number').value.trim(),
                
                // Welcome Promo Popup
                welcome_popup_enabled: document.getElementById('settings-popup-enabled').checked ? '1' : '0',
                welcome_popup_image: document.getElementById('settings-popup-image').value.trim(),
                welcome_popup_title: document.getElementById('settings-popup-title').value.trim(),
                welcome_popup_description: document.getElementById('settings-popup-desc').value.trim(),
                welcome_popup_btn_text: document.getElementById('settings-popup-btn').value.trim()
            };
            
            fetch('api/settings.php', {
                method: 'POST',
                headers: getAuthHeaders(),
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    alert(res.message);
                    loadSettings();
                } else {
                    alert('Error: ' + res.message);
                }
            })
            .catch(err => console.error("Error saving settings:", err));
        });
    }

    // --- 8. Partners (Confían en Nosotros) Operations ---
    function loadPartners() {
        const headers = getAuthHeaders();
        fetch('api/partners.php', { headers })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    partnersCache = res.data;
                    renderPartnersTable();
                }
            })
            .catch(err => console.error("Error loading partners:", err));
    }
    
    function renderPartnersTable() {
        const container = document.getElementById('partnersTableBody');
        if (!container) return;
        
        if (partnersCache.length === 0) {
            container.innerHTML = `<tr><td colspan="5" class="text-center">No hay empresas asociadas registradas.</td></tr>`;
            return;
        }
        
        container.innerHTML = partnersCache.map(partner => `
            <tr>
                <td>
                    <img src="${partner.logo_url}" alt="${partner.name}" style="height: 40px; object-fit: contain; background: #f3f4f6; border-radius: 4px; padding: 4px;">
                </td>
                <td><strong>${partner.name}</strong><br><small style="color: var(--color-text-muted);">${partner.description || ''}</small></td>
                <td>
                    <span style="font-size: 0.8rem; padding: 2px 6px; border-radius: 4px; font-weight: bold; background: ${partner.status === 'Activo' ? '#d1fae5; color: #065f46;' : '#fee2e2; color: #991b1b;'}">
                        ${partner.status}
                    </span>
                </td>
                <td>${partner.sort_order}</td>
                <td style="text-align: center;">
                    <div style="display: flex; gap: 0.5rem; justify-content: center;">
                        <button class="btn-edit" onclick="editPartner(${partner.id})" title="Editar" style="border:0; background:none; cursor:pointer; color: var(--color-primary);">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        </button>
                        <button class="btn-delete" onclick="deletePartner(${partner.id})" title="Eliminar" style="border:0; background:none; cursor:pointer; color: #ef4444;">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }
    
    if (partnerAdminForm) {
        partnerAdminForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const payload = {
                id: document.getElementById('partner-id').value,
                name: document.getElementById('partner-name').value.trim(),
                logo_url: document.getElementById('partner-logo').value.trim(),
                description: document.getElementById('partner-description').value.trim(),
                link_url: document.getElementById('partner-link').value.trim(),
                status: document.getElementById('partner-status').value,
                sort_order: parseInt(document.getElementById('partner-order').value) || 0
            };
            
            fetch('api/partners.php', {
                method: 'POST',
                headers: getAuthHeaders(),
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    alert(res.message);
                    partnerAdminForm.reset();
                    document.getElementById('partner-id').value = '';
                    document.getElementById('partnerFormTitle').textContent = 'Agregar Nueva Empresa Aliada';
                    document.getElementById('cancelPartnerEditBtn').style.display = 'none';
                    loadPartners();
                } else {
                    alert('Error: ' + res.message);
                }
            })
            .catch(err => console.error("Error saving partner:", err));
        });
        
        document.getElementById('cancelPartnerEditBtn').addEventListener('click', () => {
            partnerAdminForm.reset();
            document.getElementById('partner-id').value = '';
            document.getElementById('partnerFormTitle').textContent = 'Agregar Nueva Empresa Aliada';
            document.getElementById('cancelPartnerEditBtn').style.display = 'none';
        });
    }
    
    window.editPartner = function(id) {
        const partner = partnersCache.find(p => p.id == id);
        if (!partner) return;
        
        document.getElementById('partner-id').value = partner.id;
        document.getElementById('partner-name').value = partner.name;
        document.getElementById('partner-logo').value = partner.logo_url;
        document.getElementById('partner-description').value = partner.description || '';
        document.getElementById('partner-link').value = partner.link_url || '#';
        document.getElementById('partner-status').value = partner.status;
        document.getElementById('partner-order').value = partner.sort_order;
        
        document.getElementById('partnerFormTitle').textContent = 'Editar Empresa: ' + partner.name;
        document.getElementById('cancelPartnerEditBtn').style.display = 'inline-flex';
        document.getElementById('partner-name').focus();
    };
    
    window.deletePartner = function(id) {
        if (confirm('¿Seguro que deseas eliminar esta empresa asociada?')) {
            fetch(`api/partners.php?action=delete&id=${id}`, {
                method: 'POST',
                headers: getAuthHeaders()
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    alert(res.message);
                    loadPartners();
                } else {
                    alert('Error al eliminar la empresa asociada: ' + res.message);
                }
            })
            .catch(err => console.error("Error deleting partner:", err));
        }
    };

    // --- 9. Sub-Brands Operations ---
    function loadSubBrands() {
        const headers = getAuthHeaders();
        fetch('api/sub_brands.php', { headers })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    subBrandsCache = res.data;
                    renderSubBrandsTable();
                }
            })
            .catch(err => console.error("Error loading sub brands:", err));
    }
    
    function renderSubBrandsTable() {
        const container = document.getElementById('subBrandsTableBody');
        if (!container) return;
        
        if (subBrandsCache.length === 0) {
            container.innerHTML = `<tr><td colspan="5" class="text-center">No hay marcas individuales registradas.</td></tr>`;
            return;
        }
        
        container.innerHTML = subBrandsCache.map(sb => `
            <tr>
                <td>
                    <img src="${sb.logo_url}" alt="${sb.name}" style="height: 30px; max-width: 80px; object-fit: contain; background: #f3f4f6; border-radius: 4px; padding: 4px;" onerror="this.src='https://placehold.co/120x80/0f2c59/ffffff?text=${sb.name}'">
                </td>
                <td><strong>${sb.name}</strong></td>
                <td>
                    <span style="font-size: 0.8rem; padding: 2px 6px; border-radius: 4px; font-weight: bold; background: ${sb.status === 'Activo' ? '#d1fae5; color: #065f46;' : '#fee2e2; color: #991b1b;'}">
                        ${sb.status}
                    </span>
                </td>
                <td>${sb.sort_order}</td>
                <td style="text-align: center;">
                    <div style="display: flex; gap: 0.5rem; justify-content: center;">
                        <button class="btn-edit" onclick="editSubBrand(${sb.id})" title="Editar" style="border:0; background:none; cursor:pointer; color: var(--color-primary);">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        </button>
                        <button class="btn-delete" onclick="deleteSubBrand(${sb.id})" title="Eliminar" style="border:0; background:none; cursor:pointer; color: #ef4444;">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }
    
    if (subBrandAdminForm) {
        subBrandAdminForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const payload = {
                id: document.getElementById('subbrand-id').value,
                name: document.getElementById('subbrand-name').value.trim(),
                logo_url: document.getElementById('subbrand-logo').value.trim(),
                status: document.getElementById('subbrand-status').value,
                sort_order: parseInt(document.getElementById('subbrand-order').value) || 0
            };
            
            fetch('api/sub_brands.php', {
                method: 'POST',
                headers: getAuthHeaders(),
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    alert(res.message);
                    subBrandAdminForm.reset();
                    document.getElementById('subbrand-id').value = '';
                    document.getElementById('subBrandFormTitle').textContent = 'Agregar Nueva Marca de Producto';
                    document.getElementById('cancelSubBrandEditBtn').style.display = 'none';
                    loadSubBrands();
                } else {
                    alert('Error: ' + res.message);
                }
            })
            .catch(err => console.error("Error saving sub brand:", err));
        });
        
        document.getElementById('cancelSubBrandEditBtn').addEventListener('click', () => {
            subBrandAdminForm.reset();
            document.getElementById('subbrand-id').value = '';
            document.getElementById('subBrandFormTitle').textContent = 'Agregar Nueva Marca de Producto';
            document.getElementById('cancelSubBrandEditBtn').style.display = 'none';
        });
    }
    
    window.editSubBrand = function(id) {
        const sb = subBrandsCache.find(p => p.id == id);
        if (!sb) return;
        
        document.getElementById('subbrand-id').value = sb.id;
        document.getElementById('subbrand-name').value = sb.name;
        document.getElementById('subbrand-logo').value = sb.logo_url;
        document.getElementById('subbrand-status').value = sb.status;
        document.getElementById('subbrand-order').value = sb.sort_order;
        
        document.getElementById('subBrandFormTitle').textContent = 'Editar Marca: ' + sb.name;
        document.getElementById('cancelSubBrandEditBtn').style.display = 'inline-flex';
        document.getElementById('subbrand-name').focus();
    };
    
    window.deleteSubBrand = function(id) {
        if (confirm('¿Seguro que deseas eliminar esta marca de producto?')) {
            fetch(`api/sub_brands.php?action=delete&id=${id}`, {
                method: 'POST',
                headers: getAuthHeaders()
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    alert(res.message);
                    loadSubBrands();
                } else {
                    alert('Error al eliminar la marca de producto: ' + res.message);
                }
            })
            .catch(err => console.error("Error deleting sub brand:", err));
        }
    };

    // --- 10. Dynamic Image File Upload Helper ---
    const imageInputs = [
        'brand-logo',
        'brand-image',
        'product-image',
        'offer-image',
        'subbrand-logo',
        'partner-logo',
        'settings-logo'
    ];

    imageInputs.forEach(inputId => {
        const textInput = document.getElementById(inputId);
        if (!textInput) return;

        // Create container for file upload
        const uploadContainer = document.createElement('div');
        uploadContainer.style.display = 'flex';
        uploadContainer.style.alignItems = 'center';
        uploadContainer.style.gap = '0.75rem';
        uploadContainer.style.marginTop = '0.5rem';

        // Create file input (hidden)
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.accept = 'image/*';
        fileInput.style.display = 'none';

        // Create custom styled upload button
        const uploadBtn = document.createElement('button');
        uploadBtn.type = 'button'; // Prevent default form submit
        uploadBtn.style.padding = '0.45rem 1rem';
        uploadBtn.style.fontSize = '0.75rem';
        uploadBtn.style.fontWeight = '600';
        uploadBtn.style.fontFamily = 'var(--font-heading)';
        uploadBtn.style.borderRadius = '50px';
        uploadBtn.style.border = '1px solid var(--color-primary)';
        uploadBtn.style.backgroundColor = 'var(--color-bg-white)';
        uploadBtn.style.color = 'var(--color-primary)';
        uploadBtn.style.cursor = 'pointer';
        uploadBtn.style.display = 'inline-flex';
        uploadBtn.style.alignItems = 'center';
        uploadBtn.style.gap = '0.4rem';
        uploadBtn.style.boxShadow = 'var(--shadow-sm)';
        uploadBtn.style.transition = 'var(--transition)';

        uploadBtn.innerHTML = `
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" style="display: block;">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="17 8 12 3 7 8"></polyline>
                <line x1="12" y1="3" x2="12" y2="15"></line>
            </svg>
            Subir desde Computador
        `;

        // Create progress / status label
        const statusLabel = document.createElement('span');
        statusLabel.style.fontSize = '0.75rem';
        statusLabel.style.color = 'var(--color-text-muted)';
        statusLabel.textContent = 'Ningún archivo seleccionado';

        // Button action
        uploadBtn.addEventListener('click', () => {
            fileInput.click();
        });

        // Hover effects
        uploadBtn.addEventListener('mouseenter', () => {
            uploadBtn.style.backgroundColor = 'var(--color-primary)';
            uploadBtn.style.color = 'var(--color-bg-white)';
            uploadBtn.style.transform = 'translateY(-1px)';
        });
        uploadBtn.addEventListener('mouseleave', () => {
            uploadBtn.style.backgroundColor = 'var(--color-bg-white)';
            uploadBtn.style.color = 'var(--color-primary)';
            uploadBtn.style.transform = 'translateY(0)';
        });

        uploadContainer.appendChild(fileInput);
        uploadContainer.appendChild(uploadBtn);
        uploadContainer.appendChild(statusLabel);

        // Insert upload container right after the text input
        textInput.parentNode.insertBefore(uploadContainer, textInput.nextSibling);

        // Add change event listener to file input
        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;

            statusLabel.textContent = `Subiendo: ${file.name}...`;
            statusLabel.style.color = 'var(--color-secondary)';
            uploadBtn.style.opacity = '0.6';
            uploadBtn.style.pointerEvents = 'none';

            const formData = new FormData();
            formData.append('file', file);

            fetch('api/upload.php', {
                method: 'POST',
                headers: {
                    'X-Admin-Passcode': sessionStorage.getItem('laCanastaAdminPasscode')
                },
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                uploadBtn.style.opacity = '1';
                uploadBtn.style.pointerEvents = 'auto';
                if (res.status === 'success') {
                    textInput.value = res.url;
                    statusLabel.textContent = '¡Subido con éxito!';
                    statusLabel.style.color = '#10b981'; // Green
                    textInput.dispatchEvent(new Event('input'));
                } else {
                    statusLabel.textContent = 'Error: ' + res.message;
                    statusLabel.style.color = '#ef4444'; // Red
                }
            })
            .catch(err => {
                uploadBtn.style.opacity = '1';
                uploadBtn.style.pointerEvents = 'auto';
                console.error("Upload error:", err);
                statusLabel.textContent = 'Error de conexión';
                statusLabel.style.color = '#ef4444';
            });
            });
        });

        // --- 11. Claims Operations (Fase 11) ---
        function loadClaims() {
            const headers = getAuthHeaders();
            
            const search = document.getElementById('filter-claim-search') ? document.getElementById('filter-claim-search').value.trim() : '';
            const type = document.getElementById('filter-claim-type') ? document.getElementById('filter-claim-type').value : '';
            const status = document.getElementById('filter-claim-status') ? document.getElementById('filter-claim-status').value : '';
            const startDate = document.getElementById('filter-claim-start') ? document.getElementById('filter-claim-start').value : '';
            const endDate = document.getElementById('filter-claim-end') ? document.getElementById('filter-claim-end').value : '';
            
            let url = 'api/claims.php?';
            const params = new URLSearchParams();
            if (search) params.append('search', search);
            if (type) params.append('claim_type', type);
            if (status) params.append('status', status);
            if (startDate) params.append('start_date', startDate);
            if (endDate) params.append('end_date', endDate);
            
            url += params.toString();
            
            fetch(url, { headers })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        claimsCache = res.data;
                        renderClaimsTable();
                    }
                })
                .catch(err => console.error("Error loading claims database:", err));
        }

        function renderClaimsTable() {
            const container = document.getElementById('claimsTableBody');
            if (!container) return;
            
            if (claimsCache.length === 0) {
                container.innerHTML = `<tr><td colspan="8" class="text-center">No hay registros de reclamos que coincidan con la búsqueda.</td></tr>`;
                return;
            }
            
            container.innerHTML = claimsCache.map(claim => `
                <tr>
                    <td style="white-space: nowrap;">${claim.created_at}</td>
                    <td><strong>${claim.name}</strong></td>
                    <td>${claim.company || '-'}<br><span style="font-size: 0.8rem; color: var(--color-text-muted);">${claim.rut || ''}</span></td>
                    <td>${claim.phone || '-'}<br><a href="mailto:${claim.email}" style="font-size: 0.85rem; color: var(--color-primary);">${claim.email}</a></td>
                    <td><span style="font-size: 0.8rem; background: #fee2e2; color: #991b1b; padding: 2px 6px; border-radius: 4px; font-weight: 600;">${claim.claim_type}</span></td>
                    <td>${claim.invoice_number || '-'}</td>
                    <td style="max-width: 250px; font-size: 0.85rem; line-height: 1.4;">${claim.comments}</td>
                    <td>
                        <select onchange="updateClaimStatus(${claim.id}, this.value)" style="padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; border: 1px solid var(--color-border); background: white; font-weight: 700; cursor: pointer; color: ${claim.status === 'Nuevo' ? '#2563eb' : claim.status === 'En Revisión' ? '#d97706' : claim.status === 'Resuelto' ? '#059669' : '#dc2626'}">
                            <option value="Nuevo" ${claim.status === 'Nuevo' ? 'selected' : ''}>Nuevo</option>
                            <option value="En Revisión" ${claim.status === 'En Revisión' ? 'selected' : ''}>En Revisión</option>
                            <option value="Resuelto" ${claim.status === 'Resuelto' ? 'selected' : ''}>Resuelto</option>
                            <option value="Rechazado" ${claim.status === 'Rechazado' ? 'selected' : ''}>Rechazado</option>
                        </select>
                    </td>
                </tr>
            `).join('');
        }

        window.updateClaimStatus = function(id, newStatus) {
            fetch('api/claims.php?action=update_status', {
                method: 'POST',
                headers: getAuthHeaders(),
                body: JSON.stringify({ id, status: newStatus })
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    loadClaims();
                } else {
                    alert('Error al actualizar estado del reclamo: ' + res.message);
                }
            })
            .catch(err => console.error("Error updating claim status:", err));
        };

        // Claims filter event listeners
        const applyClaimFiltersBtn = document.getElementById('applyClaimFiltersBtn');
        const clearClaimFiltersBtn = document.getElementById('clearClaimFiltersBtn');
        
        if (applyClaimFiltersBtn) {
            applyClaimFiltersBtn.addEventListener('click', loadClaims);
        }
        
        if (clearClaimFiltersBtn) {
            clearClaimFiltersBtn.addEventListener('click', () => {
                if (document.getElementById('filter-claim-search')) document.getElementById('filter-claim-search').value = '';
                if (document.getElementById('filter-claim-type')) document.getElementById('filter-claim-type').value = '';
                if (document.getElementById('filter-claim-status')) document.getElementById('filter-claim-status').value = '';
                if (document.getElementById('filter-claim-start')) document.getElementById('filter-claim-start').value = '';
                if (document.getElementById('filter-claim-end')) document.getElementById('filter-claim-end').value = '';
                loadClaims();
            });
        }

        // Claims Export Buttons Action
        const exportClaimsCsvBtn = document.getElementById('exportClaimsCsvBtn');
        const exportClaimsXlsBtn = document.getElementById('exportClaimsXlsBtn');
        
        if (exportClaimsCsvBtn) {
            exportClaimsCsvBtn.addEventListener('click', () => {
                const passcode = sessionStorage.getItem('laCanastaAdminPasscode');
                const search = document.getElementById('filter-claim-search') ? document.getElementById('filter-claim-search').value.trim() : '';
                const type = document.getElementById('filter-claim-type') ? document.getElementById('filter-claim-type').value : '';
                const status = document.getElementById('filter-claim-status') ? document.getElementById('filter-claim-status').value : '';
                const startDate = document.getElementById('filter-claim-start') ? document.getElementById('filter-claim-start').value : '';
                const endDate = document.getElementById('filter-claim-end') ? document.getElementById('filter-claim-end').value : '';
                
                const params = new URLSearchParams();
                params.append('action', 'export');
                params.append('format', 'csv');
                params.append('passcode', passcode);
                if (search) params.append('search', search);
                if (type) params.append('claim_type', type);
                if (status) params.append('status', status);
                if (startDate) params.append('start_date', startDate);
                if (endDate) params.append('end_date', endDate);
                
                window.open(`api/claims.php?${params.toString()}`, '_blank');
            });
        }
        
        if (exportClaimsXlsBtn) {
            exportClaimsXlsBtn.addEventListener('click', () => {
                const passcode = sessionStorage.getItem('laCanastaAdminPasscode');
                const search = document.getElementById('filter-claim-search') ? document.getElementById('filter-claim-search').value.trim() : '';
                const type = document.getElementById('filter-claim-type') ? document.getElementById('filter-claim-type').value : '';
                const status = document.getElementById('filter-claim-status') ? document.getElementById('filter-claim-status').value : '';
                const startDate = document.getElementById('filter-claim-start') ? document.getElementById('filter-claim-start').value : '';
                const endDate = document.getElementById('filter-claim-end') ? document.getElementById('filter-claim-end').value : '';
                
                const params = new URLSearchParams();
                params.append('action', 'export');
                params.append('format', 'xls');
                params.append('passcode', passcode);
                if (search) params.append('search', search);
                if (type) params.append('claim_type', type);
                if (status) params.append('status', status);
                if (startDate) params.append('start_date', startDate);
                if (endDate) params.append('end_date', endDate);

                window.open(`api/claims.php?${params.toString()}`, '_blank');
            });
        }
    });





