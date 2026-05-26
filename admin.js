/**
 * La Canasta - Admin Panel JS
 */

const PASSCODE = 'admin123';
const DEFAULT_IMG = 'https://placehold.co/400x300/fdf6e3/1a5d2e?text=Oferta';

// Default mock offers for testing
const DEFAULT_OFFERS = [
    {
        id: '1',
        name: 'Queso Mantecoso Colun (Pieza ~3kg)',
        description: 'Venta al por mayor. Calidad premium, ideal para rebanar o fundir.',
        normalPrice: 28990,
        promoPrice: 22490,
        image: 'https://images.unsplash.com/photo-1486299267070-83823f5448dd?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.0.3'
    },
    {
        id: '2',
        name: 'Aceite de Cocina Maravilla 1L (Caja 12 un)',
        description: 'Formato mayorista cerrado. Precio por botella sale a $1.290.',
        normalPrice: 21990,
        promoPrice: 15480,
        image: 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.0.3'
    },
    {
        id: '3',
        name: 'Harina de Trigo Selecta (Saco 25kg)',
        description: 'Harina sin polvos de hornear. Ideal para panaderías y amasanderías.',
        normalPrice: 19990,
        promoPrice: 14500,
        image: 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.0.3'
    },
    {
        id: '4',
        name: 'Azúcar Blanca Granulada Iansa (Saco 25kg)',
        description: 'Saco cerrado. Azúcar de primera calidad, indispensable en tu almacén.',
        normalPrice: 27990,
        promoPrice: 20990,
        image: 'https://images.unsplash.com/photo-1581798459219-318e76ae1db8?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.0.3'
    }
];

document.addEventListener('DOMContentLoaded', () => {
    
    // Auth Nodes
    const adminAuth = document.getElementById('adminAuth');
    const adminPanel = document.getElementById('adminPanel');
    const loginForm = document.getElementById('loginForm');
    const passcodeVal = document.getElementById('passcode');
    const loginError = document.getElementById('loginError');
    const logoutBtn = document.getElementById('logoutBtn');

    // CRUD Nodes
    const offerForm = document.getElementById('offerForm');
    const offerId = document.getElementById('offerId');
    const offerName = document.getElementById('offerName');
    const offerDesc = document.getElementById('offerDesc');
    const offerNormalPrice = document.getElementById('offerNormalPrice');
    const offerPromoPrice = document.getElementById('offerPromoPrice');
    const offerImage = document.getElementById('offerImage');
    const submitBtn = document.getElementById('submitBtn');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    const offersTableBody = document.getElementById('offersTableBody');
    
    const loadDefaultsBtn = document.getElementById('loadDefaultsBtn');
    const clearAllBtn = document.getElementById('clearAllBtn');

    // --- Authentication ---
    function checkLogin() {
        const loggedIn = sessionStorage.getItem('laCanastaAdminLoggedIn') === 'true';
        if (loggedIn) {
            adminAuth.style.display = 'none';
            adminPanel.style.display = 'block';
            loadOffersTable();
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
            checkLogin();
        });
    }

    // Initialize Login check
    checkLogin();

    // --- CRUD Logic ---
    function getOffers() {
        const stored = localStorage.getItem('laCanastaOffers');
        return stored ? JSON.parse(stored) : [];
    }

    function saveOffers(offers) {
        localStorage.setItem('laCanastaOffers', JSON.stringify(offers));
        loadOffersTable();
    }

    function formatCLP(val) {
        return new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(val);
    }

    function loadOffersTable() {
        if (!offersTableBody) return;
        const offers = getOffers();

        if (offers.length === 0) {
            offersTableBody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center" style="padding: 2.5rem; color: var(--color-text-muted);">
                        No hay ofertas vigentes en el sistema.<br>
                        <span style="font-size: 0.85rem;">Presiona "Cargar de Prueba" para agregar datos iniciales.</span>
                    </td>
                </tr>`;
            return;
        }

        offersTableBody.innerHTML = '';
        offers.forEach(offer => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <div style="font-weight: 600; color: var(--color-text-main);">${offer.name}</div>
                    <div style="font-size: 0.8rem; color: var(--color-text-muted); max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${offer.description}</div>
                </td>
                <td style="color: var(--color-text-muted); font-size: 0.95rem;">${formatCLP(offer.normalPrice)}</td>
                <td style="color: var(--color-secondary); font-weight: 700; font-size: 0.95rem;">${formatCLP(offer.promoPrice)}</td>
                <td style="text-align: center;">
                    <div style="display: flex; gap: 0.5rem; justify-content: center;">
                        <button class="btn-edit" onclick="editOffer('${offer.id}')" title="Editar" style="border:0; background:none; cursor:pointer; color: var(--color-primary);">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        </button>
                        <button class="btn-delete" onclick="deleteOffer('${offer.id}')" title="Eliminar" style="border:0; background:none; cursor:pointer; color: #ef4444;">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    </div>
                </td>
            `;
            offersTableBody.appendChild(tr);
        });
    }

    // Add / Update Submission
    if (offerForm) {
        offerForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const id = offerId.value;
            const name = offerName.value.trim();
            const desc = offerDesc.value.trim();
            const normal = parseInt(offerNormalPrice.value, 10);
            const promo = parseInt(offerPromoPrice.value, 10);
            const image = offerImage.value.trim() || DEFAULT_IMG;

            if (!name || !desc || isNaN(normal) || isNaN(promo)) return;

            let offers = getOffers();

            if (id) {
                // Update
                offers = offers.map(o => o.id === id ? { id, name, description: desc, normalPrice: normal, promoPrice: promo, image } : o);
                sessionStorage.removeItem('editMode');
            } else {
                // Insert
                const newOffer = {
                    id: Date.now().toString(),
                    name,
                    description: desc,
                    normalPrice: normal,
                    promoPrice: promo,
                    image
                };
                offers.push(newOffer);
            }

            saveOffers(offers);
            resetForm();
        });
    }

    // Global Functions for Edit/Delete (exposed to window)
    window.editOffer = function(id) {
        const offers = getOffers();
        const offer = offers.find(o => o.id === id);
        if (!offer) return;

        offerId.value = offer.id;
        offerName.value = offer.name;
        offerDesc.value = offer.description;
        offerNormalPrice.value = offer.normalPrice;
        offerPromoPrice.value = offer.promoPrice;
        offerImage.value = offer.image === DEFAULT_IMG ? '' : offer.image;

        submitBtn.textContent = 'Actualizar Oferta';
        cancelEditBtn.style.display = 'inline-flex';
        offerName.focus();
    };

    window.deleteOffer = function(id) {
        if (confirm('¿Estás seguro de que deseas eliminar esta oferta especial?')) {
            let offers = getOffers();
            offers = offers.filter(o => o.id !== id);
            saveOffers(offers);
            if (offerId.value === id) {
                resetForm();
            }
        }
    };

    function resetForm() {
        offerForm.reset();
        offerId.value = '';
        submitBtn.textContent = 'Guardar Oferta';
        cancelEditBtn.style.display = 'none';
    }

    if (cancelEditBtn) {
        cancelEditBtn.addEventListener('click', resetForm);
    }

    // Utilities Event Listeners
    if (loadDefaultsBtn) {
        loadDefaultsBtn.addEventListener('click', () => {
            if (confirm('Esto cargará las ofertas de prueba iniciales de abarrotes. ¿Deseas continuar?')) {
                const currentOffers = getOffers();
                // Add default offers that do not already exist by ID
                const merged = [...currentOffers];
                DEFAULT_OFFERS.forEach(def => {
                    if (!merged.some(o => o.name === def.name)) {
                        merged.push({
                            ...def,
                            id: Date.now().toString() + Math.random().toString(36).substr(2, 5)
                        });
                    }
                });
                saveOffers(merged);
            }
        });
    }

    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', () => {
            if (confirm('¿ATENCIÓN: Estás seguro de que deseas vaciar todas las ofertas del catálogo?')) {
                saveOffers([]);
                resetForm();
            }
        });
    }

});
