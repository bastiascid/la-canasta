/**
 * La Canasta Distribuidora - Admin Panel JS
 */

const PASSCODE = 'admin123';
let brandsCache = [];

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
            else if (tabId === 'tab-leads') loadLeads();
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
        
        // Fetch leads, brands, products count
        Promise.all([
            fetch('api/leads.php', { headers }).then(res => res.json()),
            fetch('api/brands.php', { headers }).then(res => res.json()),
            fetch('api/products.php', { headers }).then(res => res.json())
        ])
        .then(([leadsRes, brandsRes, productsRes]) => {
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
                official_url: document.getElementById('brand-url').value.trim()
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
        document.getElementById('brand-status').value = brand.status;
        document.getElementById('brand-order').value = brand.sort_order;
        document.getElementById('brand-url').value = brand.official_url || '';
        
        document.getElementById('brandFormTitle').textContent = 'Editar Marca: ' + brand.name;
        document.getElementById('cancelBrandEditBtn').style.display = 'inline-flex';
        document.getElementById('brand-name').focus();
    };

    window.deleteBrand = function(id) {
        if (confirm('Atencion: ¿Seguro que deseas eliminar esta marca? Se eliminaran todos los productos asociados.')) {
            fetch(`api/brands.php?action=delete&id=${id}`, {
                method: 'POST',
                headers: getAuthHeaders()
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    alert(res.message);
                    loadBrands();
                }
            })
            .catch(err => console.error("Error deleting brand:", err));
        }
    };

    function populateBrandDropdown() {
        const dropdown = document.getElementById('product-brand');
        if (!dropdown) return;
        
        dropdown.innerHTML = '<option value="" disabled selected>Selecciona una marca</option>' + 
            brandsCache.map(b => `<option value="${b.id}">${b.name}</option>`).join('');
    }

    // --- 5. Products Operations ---
    let productsCache = [];
    
    function loadProducts() {
        const headers = getAuthHeaders();
        
        // Load brands first to ensure dropdown and tables show correctly
        fetch('api/brands.php', { headers })
            .then(res => res.json())
            .then(brandRes => {
                if (brandRes.status === 'success') {
                    brandsCache = brandRes.data;
                    populateBrandDropdown();
                    
                    return fetch('api/products.php', { headers });
                }
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    productsCache = res.data;
                    renderProductsTable();
                }
            })
            .catch(err => console.error("Error loading products:", err));
    }

    function renderProductsTable() {
        const container = document.getElementById('productsTableBody');
        if (!container) return;
        
        if (productsCache.length === 0) {
            container.innerHTML = `<tr><td colspan="6" class="text-center">No hay productos en el catalogo.</td></tr>`;
            return;
        }
        
        container.innerHTML = productsCache.map(product => `
            <tr>
                <td><img src="${product.image_url}" alt="Img" style="height: 35px; max-width: 60px; object-fit: contain;" onerror="this.src='https://placehold.co/400x300/f4f6f9/0f2c59?text=Prod'"></td>
                <td><strong>${product.name}</strong></td>
                <td>${product.brand_name}</td>
                <td>${product.category || '-'}</td>
                <td><span class="badge" style="background-color: ${product.featured == 1 ? '#0f2c59' : '#6b7280'}; font-size: 0.75rem; padding: 0.15rem 0.5rem; margin:0;">${product.featured == 1 ? 'Si' : 'No'}</span></td>
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
        `).join('');
    }

    if (productAdminForm) {
        productAdminForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const payload = {
                id: document.getElementById('product-id').value,
                name: document.getElementById('product-name').value.trim(),
                brand_id: parseInt(document.getElementById('product-brand').value),
                category: document.getElementById('product-category').value.trim(),
                image_url: document.getElementById('product-image').value.trim(),
                description: document.getElementById('product-description').value.trim(),
                featured: parseInt(document.getElementById('product-featured').value) || 0,
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
        const prod = productsCache.find(p => p.id == id);
        if (!prod) return;
        
        document.getElementById('product-id').value = prod.id;
        document.getElementById('product-name').value = prod.name;
        document.getElementById('product-brand').value = prod.brand_id;
        document.getElementById('product-category').value = prod.category;
        document.getElementById('product-image').value = prod.image_url || '';
        document.getElementById('product-description').value = prod.description || '';
        document.getElementById('product-featured').value = prod.featured;
        document.getElementById('product-order').value = prod.sort_order;
        
        document.getElementById('productFormTitle').textContent = 'Editar Producto: ' + prod.name;
        document.getElementById('cancelProductEditBtn').style.display = 'inline-flex';
        document.getElementById('product-name').focus();
    };

    window.deleteProduct = function(id) {
        if (confirm('¿Seguro que deseas eliminar este producto del catalogo?')) {
            fetch(`api/products.php?action=delete&id=${id}`, {
                method: 'POST',
                headers: getAuthHeaders()
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    alert(res.message);
                    loadProducts();
                }
            })
            .catch(err => console.error("Error deleting product:", err));
        }
    };

    // --- 6. Leads Database & Exporting ---
    let leadsCache = [];
    
    function loadLeads() {
        const headers = getAuthHeaders();
        fetch('api/leads.php', { headers })
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
            container.innerHTML = `<tr><td colspan="8" class="text-center">No hay registros de contacto aun.</td></tr>`;
            return;
        }
        
        container.innerHTML = leadsCache.map(lead => `
            <tr>
                <td style="white-space: nowrap;">${lead.created_at}</td>
                <td><strong>${lead.name}</strong></td>
                <td>${lead.company || '-'}</td>
                <td>${lead.role || '-'}</td>
                <td>${lead.phone || '-'}</td>
                <td><a href="mailto:${lead.email}" style="text-decoration: underline;">${lead.email}</a></td>
                <td>${lead.region || '-'}</td>
                <td style="max-width: 250px; font-size: 0.85rem; line-height: 1.4;">${lead.comments || '-'}</td>
            </tr>
        `).join('');
    }

    // Export Buttons Action
    const exportCsvBtn = document.getElementById('exportCsvBtn');
    const exportXlsBtn = document.getElementById('exportXlsBtn');
    
    if (exportCsvBtn) {
        exportCsvBtn.addEventListener('click', () => {
            const passcode = sessionStorage.getItem('laCanastaAdminPasscode');
            window.open(`api/export.php?format=csv&passcode=${encodeURIComponent(passcode)}`, '_blank');
        });
    }
    
    if (exportXlsBtn) {
        exportXlsBtn.addEventListener('click', () => {
            const passcode = sessionStorage.getItem('laCanastaAdminPasscode');
            window.open(`api/export.php?format=xls&passcode=${encodeURIComponent(passcode)}`, '_blank');
        });
    }

    // --- 7. Settings & Config Panel ---
    function loadSettings() {
        fetch('api/settings.php')
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success' && res.data) {
                    const settings = res.data;
                    document.getElementById('settings-logo').value = settings.logo_url || 'assets/canasta-logo.png';
                    document.getElementById('settings-wa-enabled').checked = settings.whatsapp_enabled === '1';
                    document.getElementById('settings-wa-number').value = settings.whatsapp_number || '+56 9 4256 7472';
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
                whatsapp_number: document.getElementById('settings-wa-number').value.trim()
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

});
