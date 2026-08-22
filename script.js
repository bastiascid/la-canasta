/**
 * La Canasta Distribuidora - Client Side JavaScript
 */

let globalProducts = [];
let globalBrands = [];
let globalSettings = {
    whatsapp_enabled: "0",
    whatsapp_number: "+56 9 4256 7472",
    logo_url: "assets/canasta-logo.png"
};

// Open WhatsApp helper using settings config
function openWhatsApp(text) {
    const rawNumber = globalSettings.whatsapp_number.replace(/[^\d]/g, '');
    const encodedText = encodeURIComponent(text);
    const url = `https://wa.me/${rawNumber}?text=${encodedText}`;
    
    if (typeof fbq === 'function') {
        fbq('trackCustom', 'WhatsAppClick', { origin: 'dynamic_button' });
    }
    
    if (typeof gtag === 'function') {
        gtag('event', 'click_whatsapp', { origin: 'dynamic_button' });
    }
    
    window.open(url, '_blank', 'noopener,noreferrer');
}

document.addEventListener('DOMContentLoaded', () => {

    // --- 1. Load Configurations & Toggle WhatsApp ---
    function loadSettings() {
        fetch('api/settings.php')
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success' && res.data) {
                    globalSettings = res.data;
                    // Set Carousel Opacity
                    if(globalSettings.carousel_opacity !== undefined) {
                        const overlay = document.querySelector('.carousel-overlay');
                        if (overlay) {
                            overlay.style.opacity = globalSettings.carousel_opacity;
                        }
                    }
                    
                    // Handle Logo replacement (if image exists, use it, else keep text)
                    const logoContainer = document.getElementById('logoContainer');
                    const footerLogoTextNode = document.getElementById('footerLogoTextNode');
                    if (globalSettings.logo_url) {
                        const logoUrlWithVersion = globalSettings.logo_url + '?v=2';
                        const imgHtml = `<img src="${logoUrlWithVersion}" alt="La Canasta Logo" style="height: 50px; max-width: 150px; object-fit: contain;">`;
                        if (logoContainer) {
                            logoContainer.innerHTML = imgHtml;
                            logoContainer.style.height = '50px';
                        }
                        if (footerLogoTextNode) {
                            const footerImgHtml = `<img src="${logoUrlWithVersion}" alt="La Canasta Logo" style="height: 38px; max-width: 130px; object-fit: contain; background: white; padding: 4px; border-radius: 4px; box-shadow: var(--shadow-sm);">`;
                            footerLogoTextNode.innerHTML = footerImgHtml;
                        }
                    }
                    
                    // Handle WhatsApp Toggle visibility
                    const isWhatsAppActive = globalSettings.whatsapp_enabled === '1';
                    document.querySelectorAll('.whatsapp-only-node').forEach(node => {
                        if (isWhatsAppActive) {
                            if (node.classList.contains('whatsapp-float-container')) {
                                node.style.display = 'flex';
                            } else {
                                node.style.display = 'block';
                            }
                        } else {
                            node.style.display = 'none';
                        }
                    });
                    
                    // Setup Welcome Popup with dynamic settings
                    setupWelcomePopup();
                }
            })
            .catch(err => console.error("Error loading settings:", err));
    }

    // --- 2. Load Brands and Products ---
    function loadCatalogData() {
        // Fetch brands first
        fetch('api/brands.php')
            .then(res => res.json())
            .then(brandRes => {
                if (brandRes.status === 'success') {
                    globalBrands = brandRes.data;
                    
                    // Fetch products
                    return fetch('api/products.php');
                }
                throw new Error("Failed to load brands");
            })
            .then(res => res.json())
            .then(productRes => {
                if (productRes.status === 'success') {
                    globalProducts = productRes.data;
                    
                    renderBrands();
                    renderCatalogFilters();
                    renderCatalog('Todos');
                }
            })
            .catch(err => {
                console.error("Error loading catalog data:", err);
                const grids = ['brandsCarouselTrack', 'catalogGrid'];
                grids.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.innerHTML = `<p class="text-center" style="width: 100%; color: var(--color-secondary);">Error al cargar los datos del catálogo. Por favor, intente más tarde.</p>`;
                });
            });
    }

    // Carousel state variables
    let carouselIndex = 0;
    let carouselInterval = null;
    let cardsToShow = 1;

    // Render Brand Cards
    function renderBrands() {
        const grid = document.getElementById('brandsGrid');
        if (!grid) return;
        
        if (globalBrands.length === 0) {
            grid.innerHTML = `<p class="text-center" style="width: 100%; color: var(--color-text-muted); padding: 2rem 0;">No hay marcas registradas en este momento.</p>`;
            return;
        }
        
        grid.innerHTML = '';
        globalBrands.forEach(brand => {
            const card = document.createElement('div');
            card.className = 'brand-showcase-card';
            card.style.display = 'flex';
            card.style.flexDirection = 'column';
            card.style.width = '290px';
            card.style.flexShrink = '0';
            
            card.innerHTML = `
                <div class="brand-header-flex">
                    <img src="${brand.logo_url}" alt="${brand.name} Logo" class="brand-logo-img" onerror="this.src='https://placehold.co/120x80/0f2c59/ffffff?text=${brand.name}'">
                    <h3 style="color: var(--color-primary); font-size: 1.5rem; margin: 0; font-weight: 800; font-family: var(--font-heading);">${brand.name}</h3>
                </div>
                <p style="font-size: 0.95rem; line-height: 1.5; color: var(--color-text-muted); margin: 0; flex-grow: 1;">${brand.description || 'Distribución mayorista autorizada.'}</p>
            `;
            
            grid.appendChild(card);
            scrollObserver.observe(card);
        });
    }

    // Brands Carousel Logic
    function initBrandsCarousel() {
        const track = document.getElementById('brandsCarouselTrack');
        const prevBtn = document.getElementById('brandPrevBtn');
        const nextBtn = document.getElementById('brandNextBtn');
        const indicatorsContainer = document.getElementById('brandIndicators');
        
        if (!track || !prevBtn || !nextBtn || !indicatorsContainer) return;
        
        const slides = track.querySelectorAll('.carousel-slide');
        const totalSlides = slides.length;
        
        function getCardsToShow() {
            if (window.innerWidth >= 1024) return 3;
            if (window.innerWidth >= 768) return 2;
            return 1;
        }
        
        cardsToShow = getCardsToShow();
        carouselIndex = 0;
        
        // Reset translation
        track.style.transform = `translateX(0)`;
        
        // Set setup limit
        const maxIndex = Math.max(0, totalSlides - cardsToShow);
        
        // If there are not enough slides to scroll, hide controls
        if (totalSlides <= cardsToShow) {
            prevBtn.classList.add('hidden');
            nextBtn.classList.add('hidden');
            indicatorsContainer.classList.add('hidden');
            return;
        } else {
            prevBtn.classList.remove('hidden');
            nextBtn.classList.remove('hidden');
            indicatorsContainer.classList.remove('hidden');
        }
        
        // Setup Indicators (dots)
        indicatorsContainer.innerHTML = '';
        for (let i = 0; i <= maxIndex; i++) {
            const indicator = document.createElement('button');
            indicator.className = `carousel-indicator ${i === 0 ? 'active' : ''}`;
            indicator.setAttribute('aria-label', `Ir a marca ${i + 1}`);
            indicator.addEventListener('click', () => {
                goToSlide(i);
                resetAutoPlay();
            });
            indicatorsContainer.appendChild(indicator);
        }
        
        function updateCarousel() {
            // Keep index in bounds
            if (carouselIndex > maxIndex) carouselIndex = 0;
            if (carouselIndex < 0) carouselIndex = maxIndex;
            
            // Translate track (each slide occupies 100/cardsToShow percent)
            const offset = carouselIndex * (100 / cardsToShow);
            track.style.transform = `translateX(-${offset}%)`;
            
            // Update indicators active state
            const indicators = indicatorsContainer.querySelectorAll('.carousel-indicator');
            indicators.forEach((ind, i) => {
                if (i === carouselIndex) {
                    ind.classList.add('active');
                } else {
                    ind.classList.remove('active');
                }
            });
        }
        
        function goToSlide(index) {
            carouselIndex = index;
            updateCarousel();
        }
        
        function nextSlide() {
            carouselIndex++;
            if (carouselIndex > maxIndex) {
                carouselIndex = 0;
            }
            updateCarousel();
        }
        
        function prevSlide() {
            carouselIndex--;
            if (carouselIndex < 0) {
                carouselIndex = maxIndex;
            }
            updateCarousel();
        }
        
        // Set up button listeners
        prevBtn.onclick = () => {
            prevSlide();
            resetAutoPlay();
        };
        
        nextBtn.onclick = () => {
            nextSlide();
            resetAutoPlay();
        };
        
        // Autoplay implementation (4s interval)
        function startAutoPlay() {
            if (carouselInterval) clearInterval(carouselInterval);
            carouselInterval = setInterval(nextSlide, 4000);
        }
        
        function stopAutoPlay() {
            if (carouselInterval) clearInterval(carouselInterval);
        }
        
        function resetAutoPlay() {
            stopAutoPlay();
            startAutoPlay();
        }
        
        // Pause when mouse is over elements
        track.onmouseenter = stopAutoPlay;
        track.onmouseleave = startAutoPlay;
        prevBtn.onmouseenter = stopAutoPlay;
        prevBtn.onmouseleave = startAutoPlay;
        nextBtn.onmouseenter = stopAutoPlay;
        nextBtn.onmouseleave = startAutoPlay;
        
        // Debounced resize listener
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                const newCardsToShow = getCardsToShow();
                if (newCardsToShow !== cardsToShow) {
                    initBrandsCarousel();
                }
            }, 100);
        });
        
        // Start autoplay initially
        startAutoPlay();
    }

    // Advantages Carousel Logic
    function initAdvantagesCarousel() {
        const track = document.getElementById('advantagesCarouselTrack');
        const prevBtn = document.getElementById('advPrevBtn');
        const nextBtn = document.getElementById('advNextBtn');
        const indicatorsContainer = document.getElementById('advIndicators');
        
        if (!track || !prevBtn || !nextBtn || !indicatorsContainer) return;
        
        const slides = track.querySelectorAll('.carousel-slide');
        const totalSlides = slides.length;
        let cardsToShow = 3;
        let carouselIndex = 0;
        let carouselInterval = null;
        
        function getCardsToShow() {
            if (window.innerWidth >= 1024) return 3;
            if (window.innerWidth >= 768) return 2;
            return 1;
        }
        
        cardsToShow = getCardsToShow();
        
        // Reset translation
        track.style.transform = `translateX(0)`;
        
        // Set setup limit
        const maxIndex = Math.max(0, totalSlides - cardsToShow);
        
        // If there are not enough slides to scroll, hide controls
        if (totalSlides <= cardsToShow) {
            prevBtn.classList.add('hidden');
            nextBtn.classList.add('hidden');
            indicatorsContainer.classList.add('hidden');
            return;
        } else {
            prevBtn.classList.remove('hidden');
            nextBtn.classList.remove('hidden');
            indicatorsContainer.classList.remove('hidden');
        }
        
        // Setup Indicators (dots)
        indicatorsContainer.innerHTML = '';
        for (let i = 0; i <= maxIndex; i++) {
            const indicator = document.createElement('button');
            indicator.className = `carousel-indicator ${i === 0 ? 'active' : ''}`;
            indicator.setAttribute('aria-label', `Ir a ventaja ${i + 1}`);
            indicator.addEventListener('click', () => {
                goToSlide(i);
                resetAutoPlay();
            });
            indicatorsContainer.appendChild(indicator);
        }
        
        function updateCarousel() {
            if (carouselIndex > maxIndex) carouselIndex = 0;
            if (carouselIndex < 0) carouselIndex = maxIndex;
            
            const offset = carouselIndex * (100 / cardsToShow);
            track.style.transform = `translateX(-${offset}%)`;
            
            const indicators = indicatorsContainer.querySelectorAll('.carousel-indicator');
            indicators.forEach((ind, i) => {
                if (i === carouselIndex) {
                    ind.classList.add('active');
                } else {
                    ind.classList.remove('active');
                }
            });
        }
        
        function goToSlide(index) {
            carouselIndex = index;
            updateCarousel();
        }
        
        function nextSlide() {
            carouselIndex++;
            if (carouselIndex > maxIndex) {
                carouselIndex = 0;
            }
            updateCarousel();
        }
        
        function prevSlide() {
            carouselIndex--;
            if (carouselIndex < 0) {
                carouselIndex = maxIndex;
            }
            updateCarousel();
        }
        
        prevBtn.onclick = () => {
            prevSlide();
            resetAutoPlay();
        };
        
        nextBtn.onclick = () => {
            nextSlide();
            resetAutoPlay();
        };
        
        function startAutoPlay() {
            if (carouselInterval) clearInterval(carouselInterval);
            carouselInterval = setInterval(nextSlide, 5000);
        }
        
        function stopAutoPlay() {
            if (carouselInterval) clearInterval(carouselInterval);
        }
        
        function resetAutoPlay() {
            stopAutoPlay();
            startAutoPlay();
        }
        
        track.onmouseenter = stopAutoPlay;
        track.onmouseleave = startAutoPlay;
        prevBtn.onmouseenter = stopAutoPlay;
        prevBtn.onmouseleave = startAutoPlay;
        nextBtn.onmouseenter = stopAutoPlay;
        nextBtn.onmouseleave = startAutoPlay;
        
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                const newCardsToShow = getCardsToShow();
                if (newCardsToShow !== cardsToShow) {
                    initAdvantagesCarousel();
                }
            }, 100);
        });
        
        startAutoPlay();
    }

    // Render Catalog Filters
    function renderCatalogFilters() {
        const filterBar = document.getElementById('categoryFilterBar');
        if (!filterBar) return;
        
        // Get unique categories from active products
        const categories = ['Todos', ...new Set(globalProducts.map(p => p.category).filter(Boolean))];
        
        filterBar.innerHTML = '';
        categories.forEach(cat => {
            const btn = document.createElement('button');
            btn.className = `filter-btn ${cat === 'Todos' ? 'active' : ''}`;
            btn.textContent = cat;
            btn.addEventListener('click', () => {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                renderCatalog(cat);
            });
            filterBar.appendChild(btn);
        });
    }

    // Render Products Grid
    function renderCatalog(selectedCategory) {
        const catalogGrid = document.getElementById('catalogGrid');
        if (!catalogGrid) return;
        
        const filtered = selectedCategory === 'Todos' 
            ? globalProducts 
            : globalProducts.filter(p => p.category === selectedCategory);
            
        if (filtered.length === 0) {
            catalogGrid.innerHTML = `<p class="text-center" style="grid-column: 1/-1; color: var(--color-text-muted); padding: 3rem;">No hay productos registrados en esta categoría.</p>`;
            return;
        }
        
        catalogGrid.innerHTML = '';
        
        // Group filtered products by brand
        globalBrands.forEach(brand => {
            const brandProducts = filtered.filter(p => p.brand_id == brand.id);
            if (brandProducts.length === 0) return;
            
            const brandSection = document.createElement('div');
            brandSection.className = 'brand-group-section';
            brandSection.style.width = '100%';
            
            const headerHtml = `
                <div class="brand-group-header">
                    <img src="${brand.logo_url}" alt="${brand.name} Logo" class="brand-group-logo" onerror="this.style.display='none'">
                    <h3 class="brand-group-title">${brand.name}</h3>
                </div>
            `;
            
            const gridContainer = document.createElement('div');
            gridContainer.className = 'offers-grid';
            gridContainer.style.width = '100%';
            gridContainer.style.gap = '1rem';
            gridContainer.style.justifyContent = 'center';
            
            function renderProductCard(product) {
                const cart = getCart();
                const inCart = cart.some(item => item.id == product.id);
                const buttonText = inCart ? 'Añadido ✓' : 'Añadir al Pedido';
                const buttonClass = inCart ? 'add-to-cart-btn btn-secondary' : 'add-to-cart-btn';
                
                return `
                    <div class="offer-card" data-product-id="${product.id}" style="width: 100%; max-width: 190px;">
                        ${product.featured == 1 ? '<span class="offer-badge" style="background-color: var(--color-secondary); font-size: 0.65rem; padding: 3px 6px;">Destacado</span>' : ''}
                        <div class="offer-img-wrapper" style="height: 110px;">
                            <img src="${product.image_url}" alt="${product.name}" onerror="this.src='https://placehold.co/400x300/f4f6f9/0f2c59?text=Producto'" loading="lazy">
                        </div>
                        <div class="offer-details" style="display: flex; flex-direction: column; height: calc(100% - 110px); padding: 0.75rem;">
                            <span style="font-size: 0.65rem; font-weight: 700; color: var(--color-secondary); text-transform: uppercase;">${product.category}</span>
                            <h3 class="offer-name" style="margin-top: 3px; min-height: 36px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; font-size: 0.9rem; font-weight: 700; color: var(--color-primary);">${product.name}</h3>
                            <p class="offer-desc" style="flex-grow: 1; min-height: 40px; margin-bottom: 0.75rem; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; font-size: 0.75rem; color: var(--color-text-muted);">${product.description || 'Abarrotes para venta exclusiva.'}</p>
                            <button class="${buttonClass}" onclick="handleAddToCartClick(${product.id})" style="padding: 0.4rem; font-size: 0.8rem;">
                                <span>🛒</span> <span class="btn-text">${buttonText}</span>
                            </button>
                        </div>
                    </div>
                `;
            }
            
            const initialProducts = brandProducts.slice(0, 5);
            const hiddenProducts = brandProducts.slice(5);
            
            let currentProducts = [...initialProducts];
            let isExpanded = false;
            
            // Internal function to redraw the products of this brand
            function updateGrid() {
                gridContainer.innerHTML = '';
                if (currentProducts.length === 0) {
                    gridContainer.innerHTML = `<p class="text-center" style="grid-column: 1/-1; color: var(--color-text-muted); padding: 1rem 0; width: 100%;">No hay productos en esta categoría.</p>`;
                } else {
                    currentProducts.forEach(product => {
                        const wrapperDiv = document.createElement('div');
                        wrapperDiv.innerHTML = renderProductCard(product);
                        const cardElement = wrapperDiv.firstElementChild;
                        gridContainer.appendChild(cardElement);
                        scrollObserver.observe(cardElement);
                    });
                }
            }
            
            updateGrid();
            
            brandSection.innerHTML = headerHtml;
            brandSection.appendChild(gridContainer);
            
            if (hiddenProducts.length > 0) {
                const btnContainer = document.createElement('div');
                btnContainer.className = 'brand-toggle-btn-container';
                
                const toggleBtn = document.createElement('button');
                toggleBtn.className = 'brand-toggle-btn';
                toggleBtn.textContent = `Ver catálogo completo de ${brand.name}`;
                
                toggleBtn.addEventListener('click', () => {
                    isExpanded = !isExpanded;
                    if (isExpanded) {
                        currentProducts = [...brandProducts];
                        toggleBtn.textContent = `Ver menos`;
                    } else {
                        currentProducts = [...initialProducts];
                        toggleBtn.textContent = `Ver catálogo completo de ${brand.name}`;
                    }
                    updateGrid();
                });
                
                btnContainer.appendChild(toggleBtn);
                brandSection.appendChild(btnContainer);
            }
            
            catalogGrid.appendChild(brandSection);
        });
    }

    // Inquiry action for products - scrolls to form and prefills
    window.inquireProduct = function(productName, brandName) {
        const bottomAction = document.getElementById('bottom-action');
        const bottomComments = document.getElementById('bottom-comments');
        const contactSection = document.getElementById('contacto');
        
        if (bottomAction) bottomAction.value = 'Solicita información';
        if (bottomComments) {
            bottomComments.value = `Hola, me interesa recibir informacion comercial y cotizar el producto "${productName}" de la marca "${brandName}" para mi negocio.`;
        }
        
        if (contactSection) {
            contactSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    };

    // --- 3. Form Submissions ---
    function handleFormSubmit(formId, successId) {
        const form = document.getElementById(formId);
        const successDiv = document.getElementById(successId);
        
        if (!form) return;
        
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            
            // Email Validation
            const emailInput = form.querySelector('input[type="email"]');
            if (emailInput) {
                const email = emailInput.value.trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    alert('Por favor, ingresa un correo electrónico válido.');
                    emailInput.focus();
                    return;
                }
            }
            
            let payload = {};
            if (formId === 'heroContactForm') {
                payload = {
                    name: document.getElementById('hero-name').value.trim(),
                    rut: document.getElementById('hero-rut').value.trim(),
                    email: document.getElementById('hero-email').value.trim(),
                    company: document.getElementById('hero-company').value.trim(),
                    role: document.getElementById('hero-role').value.trim(),
                    phone: document.getElementById('hero-phone').value.trim(),
                    region: document.getElementById('hero-region').value,
                    comuna: document.getElementById('hero-comuna').value.trim(),
                    comments: document.getElementById('hero-comments').value.trim(),
                    origin: 'Formulario Hero'
                };
            } else if (formId === 'middleContactForm') {
                payload = {
                    name: document.getElementById('middle-name').value.trim(),
                    rut: document.getElementById('middle-rut').value.trim(),
                    email: document.getElementById('middle-email').value.trim(),
                    company: document.getElementById('middle-company').value.trim(),
                    role: document.getElementById('middle-role').value.trim(),
                    phone: document.getElementById('middle-phone').value.trim(),
                    region: document.getElementById('middle-region').value,
                    comuna: document.getElementById('middle-comuna').value.trim(),
                    comments: document.getElementById('middle-comments').value.trim(),
                    origin: 'Formulario Mitad Página'
                };
            } else {
                payload = {
                    name: document.getElementById('bottom-name').value.trim(),
                    rut: document.getElementById('bottom-rut').value.trim(),
                    email: document.getElementById('bottom-email').value.trim(),
                    company: document.getElementById('bottom-company').value.trim(),
                    role: document.getElementById('bottom-role').value.trim(),
                    phone: document.getElementById('bottom-phone').value.trim(),
                    region: document.getElementById('bottom-region').value,
                    comuna: document.getElementById('bottom-comuna').value.trim(),
                    comments: document.getElementById('bottom-comments').value.trim(),
                    origin: 'Formulario Final'
                };
            }
            
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Enviando...';
            }
            
            fetch('api/leads.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    if (typeof fbq === 'function') {
                        fbq('track', 'Lead');
                    }
                    if (typeof gtag === 'function') {
                        gtag('event', 'generate_lead');
                    }
                    form.style.display = 'none';
                    if (successDiv) {
                        successDiv.style.display = 'block';
                        successDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                } else {
                    alert('Error: ' + res.message);
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    }
                }
            })
            .catch(err => {
                console.error("Error submitting form:", err);
                alert("Hubo un error de conexión al procesar tu registro. Por favor, intenta de nuevo.");
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            });
        });
    }

    // --- 4. Load Coverage Zones ---
    function loadCoverageZones() {
        const grid = document.getElementById('coberturaGrid');
        if (!grid) return;
        
        fetch('api/coverage.php')
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success' && res.data.length > 0) {
                    window.loadedCommunes = res.data; // Save loaded communes globally for search
                    // Populate Comuna selectors in index.html
                    const comunaSelects = document.querySelectorAll('#hero-comuna, #middle-comuna, #bottom-comuna');
                    if (comunaSelects.length > 0) {
                        let optionsHTML = '<option value="" disabled selected>Selecciona tu comuna</option>';
                        res.data.forEach(zone => {
                            optionsHTML += `<option value="${zone.name}">${zone.name}</option>`;
                        });
                        optionsHTML += '<option value="Otra Comuna">Otra Comuna (Región de O\'Higgins)</option>';
                        
                        comunaSelects.forEach(select => {
                            if(select.tagName === 'SELECT') {
                                select.innerHTML = optionsHTML;
                            }
                        });
                    }
                    grid.innerHTML = '';
                    res.data.forEach(zone => {
                        const card = document.createElement('div');
                        card.className = 'commune-badge animate-on-scroll';
                        
                        card.innerHTML = `
                            <span class="badge-icon">📍</span>
                            <span>${zone.name}</span>
                        `;
                        
                        // Click interaction to zoom/pan the Leaflet map and open popup
                        card.addEventListener('click', () => {
                            // Remove active state from all badges
                            document.querySelectorAll('.commune-badge').forEach(b => b.classList.remove('active'));
                            // Add active state to clicked badge
                            card.classList.add('active');
                            
                            const nameKey = zone.name.trim().toLowerCase();
                            if (window.coverageMap && window.coverageMarkers && window.coverageMarkers[nameKey]) {
                                const marker = window.coverageMarkers[nameKey];
                                window.coverageMap.setView(marker.getLatLng(), 12, { animate: true });
                                marker.openPopup();
                            }
                        });
                        
                        grid.appendChild(card);
                        scrollObserver.observe(card);
                    });
                } else {
                    grid.innerHTML = '<p style="width: 100%; color: var(--color-text-muted);">Zonas de cobertura en actualización.</p>';
                }
            })
            .catch(err => {
                console.error("Error loading coverage zones:", err);
                grid.innerHTML = '<p style="width: 100%; color: var(--color-secondary);">Error al cargar cobertura.</p>';
            });
    }

    // --- 5. Load Offers and Banners ---
    function loadOffersBanners() {
        const grid = document.getElementById('offersGrid');
        if (!grid) return;
        
        fetch('api/offers.php')
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success' && res.data.length > 0) {
                    grid.innerHTML = '';
                    res.data.forEach(offer => {
                        const card = document.createElement('div');
                        card.className = 'offer-card animate-on-scroll';
                        card.style.display = 'flex';
                        card.style.flexDirection = 'column';
                        
                        card.innerHTML = `
                            <div class="offer-img-wrapper" style="height: 200px;">
                                <img src="${offer.image_url}" alt="${offer.title}" onerror="this.src='https://placehold.co/400x250/f4f6f9/0f2c59?text=Campa%C3%B1a'" loading="lazy">
                            </div>
                            <div class="offer-details" style="display: flex; flex-direction: column; flex-grow: 1; padding: 1.5rem;">
                                <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--color-primary); margin: 0 0 0.5rem 0;">${offer.title}</h3>
                                <p style="font-size: 0.9rem; color: var(--color-text-muted); line-height: 1.5; flex-grow: 1; margin: 0 0 1.5rem 0;">${offer.description}</p>
                                <a href="${offer.link_url}" class="btn btn-secondary btn-sm" style="text-align: center; justify-content: center; width: 100%; border-radius: 6px;">Más Información</a>
                            </div>
                        `;
                        grid.appendChild(card);
                        scrollObserver.observe(card);
                    });
                } else {
                    grid.innerHTML = '<p class="text-center" style="grid-column: 1/-1; color: var(--color-text-muted);">No hay ofertas vigentes en este momento.</p>';
                }
            })
            .catch(err => {
                console.error("Error loading offers:", err);
                grid.innerHTML = '<p class="text-center" style="grid-column: 1/-1; color: var(--color-secondary);">Error al cargar campañas.</p>';
            });
    }

    // --- 5. Load Partners / Trusted Clients ---
    function loadPartners() {
        const container = document.getElementById('partnersContainer');
        if (!container) return;
        
        fetch('api/partners.php')
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success' && res.data.length > 0) {
                    container.innerHTML = '';
                    res.data.forEach(p => {
                        const card = document.createElement('div');
                        card.className = 'offer-card animate-on-scroll';
                        card.style.display = 'flex';
                        card.style.flexDirection = 'column';
                        
                        card.innerHTML = `
                            <div class="offer-img-wrapper" style="height: 200px; padding: 1.5rem; background: white; display: flex; align-items: center; justify-content: center;">
                                <img src="${p.logo_url}" alt="${p.name} Logo" style="max-height: 100%; max-width: 100%; object-fit: contain;" onerror="this.src='https://placehold.co/400x250/f4f6f9/0f2c59?text=${encodeURIComponent(p.name)}'" loading="lazy">
                            </div>
                            <div class="offer-details" style="display: flex; flex-direction: column; flex-grow: 1; padding: 1.5rem;">
                                <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--color-primary); margin: 0 0 0.5rem 0;">${p.name}</h3>
                                <p style="font-size: 0.9rem; color: var(--color-text-muted); line-height: 1.5; flex-grow: 1; margin: 0;">${p.description || ''}</p>
                            </div>
                        `;
                        container.appendChild(card);
                        scrollObserver.observe(card);
                    });
                } else {
                    container.innerHTML = '<p class="text-center" style="grid-column: 1 / -1; color: var(--color-text-muted);">Pronto agregaremos más empresas asociadas.</p>';
                }
            })
            .catch(err => {
                console.error("Error loading partners:", err);
                container.innerHTML = '<p class="text-center" style="grid-column: 1 / -1; color: var(--color-text-muted);">Error al cargar empresas asociadas.</p>';
            });
    }

    // --- 6. Scroll Animations Setup ---
    const observerOptions = { root: null, rootMargin: '0px', threshold: 0.1 };
    const scrollObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.animate-on-scroll').forEach(el => scrollObserver.observe(el));

    // --- 7. Load Sub-Brands Ticker (Horizontal Banner) ---
    function loadSubBrandsTicker() {
        const ticker = document.getElementById('subBrandsTicker');
        if (!ticker) return;
        
        fetch('api/sub_brands.php')
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success' && res.data.length > 0) {
                    const activeBrands = res.data.filter(b => b.status === 'Activo');
                    if (activeBrands.length === 0) {
                        ticker.parentElement.parentElement.style.display = 'none';
                        return;
                    }
                    
                    // Duplicate elements to ensure smooth continuous scrolling
                    const items = [...activeBrands, ...activeBrands, ...activeBrands, ...activeBrands];
                    
                    ticker.innerHTML = items.map(sb => `
                        <img src="${sb.logo_url}" alt="${sb.name}" title="${sb.name}" onerror="this.style.display='none'">
                    `).join('');
                } else {
                    ticker.parentElement.parentElement.style.display = 'none';
                }
            })
            .catch(err => {
                console.error("Error loading sub brands for ticker:", err);
                ticker.parentElement.parentElement.style.display = 'none';
            });
    }
    
    // --- 9. Interactive Coverage Search ---
    function setupCoverageSearch() {
        const searchInput = document.getElementById('coverageSearchInput');
        const searchBtn = document.getElementById('coverageSearchBtn');
        const searchResult = document.getElementById('coverageSearchResult');
        
        if (!searchInput || !searchBtn || !searchResult) return;
        
        function performSearch() {
            const inputVal = searchInput.value;
            if (!inputVal.trim()) {
                searchResult.style.display = 'none';
                searchResult.className = 'coverage-search-result';
                searchResult.innerHTML = '';
                return;
            }
            
            if (!window.loadedCommunes || window.loadedCommunes.length === 0) {
                searchResult.className = 'coverage-search-result error';
                searchResult.innerHTML = 'Cargando información de comunas, por favor intenta en unos segundos.';
                return;
            }
            
            // Clean inputs for comparison (strip accents, convert to lowercase)
            const cleanString = (str) => {
                return str.toLowerCase()
                          .normalize("NFD")
                          .replace(/[\u0300-\u036f]/g, "")
                          .replace(/['’]/g, "") // support O'Higgins or other characters
                          .trim();
            };
            
            const query = cleanString(inputVal);
            
            // Look for a matching commune
            const match = window.loadedCommunes.find(zone => {
                const zoneClean = cleanString(zone.name);
                // Matches if input contains the commune name (e.g. "calle santiago, rancagua" contains "rancagua")
                // or if commune name contains input (e.g. "rancag" matches "rancagua")
                return query.includes(zoneClean) || (query.length >= 3 && zoneClean.includes(query));
            });
            
            if (match) {
                searchResult.className = 'coverage-search-result success';
                searchResult.innerHTML = `✅ Despachamos a tu zona. ¡Haz tu pedido! (Comuna: <strong>${match.name}</strong>)`;
                
                // Pan/zoom map to the matched commune and open its popup if it exists
                const nameKey = match.name.trim().toLowerCase();
                if (window.coverageMap && window.coverageMarkers && window.coverageMarkers[nameKey]) {
                    const marker = window.coverageMarkers[nameKey];
                    window.coverageMap.setView(marker.getLatLng(), 12, { animate: true });
                    marker.openPopup();
                }
                
                // Highlight corresponding list badge
                document.querySelectorAll('.commune-badge').forEach(b => {
                    const badgeText = b.querySelector('span:not(.badge-icon)').textContent.trim().toLowerCase();
                    if (badgeText === nameKey) {
                        b.classList.add('active');
                        b.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    } else {
                        b.classList.remove('active');
                    }
                });
            } else {
                searchResult.className = 'coverage-search-result error';
                searchResult.innerHTML = `❌ Por el momento no despachamos a tu zona. Contáctanos para evaluar una nueva ruta de reparto.`;
            }
        }
        
        searchBtn.addEventListener('click', performSearch);
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    }

    // Initialize Page functions
    loadSettings();
    loadCatalogData();
    loadCoverageZones();
    loadOffersBanners();
    loadPartners();
    loadSubBrandsTicker();
    initAdvantagesCarousel();
    setupCoverageSearch();
    handleFormSubmit('heroContactForm', 'heroFormSuccess');
    handleFormSubmit('middleContactForm', 'middleFormSuccess');
    handleFormSubmit('bottomContactForm', 'bottomFormSuccess');

    // --- FASE 11: B2B Shopping Cart Logic ---
    function getCart() {
        try {
            return JSON.parse(localStorage.getItem('b2b_cart')) || [];
        } catch (e) {
            return [];
        }
    }
    
    function saveCart(cart) {
        localStorage.setItem('b2b_cart', JSON.stringify(cart));
        updateCartBadge();
    }
    
    function updateCartBadge() {
        const cart = getCart();
        const count = cart.reduce((sum, item) => sum + item.quantity, 0);
        const badge = document.getElementById('cartCountBadge');
        const floatBtn = document.getElementById('cartFloatingBtn');
        
        if (badge) badge.textContent = count;
        if (floatBtn) {
            if (count > 0) {
                floatBtn.style.display = 'flex';
            } else {
                floatBtn.style.display = 'none';
                closeCart();
            }
        }
    }
    
    window.handleAddToCartClick = function(productId) {
        const product = globalProducts.find(p => p.id == productId);
        if (!product) return;
        
        let cart = getCart();
        const existingIndex = cart.findIndex(item => item.id == productId);
        
        if (existingIndex > -1) {
            cart[existingIndex].quantity += 1;
        } else {
            cart.push({
                id: product.id,
                name: product.name,
                brand_name: product.brand_name,
                image_url: product.image_url,
                category: product.category,
                quantity: 1
            });
        }
        
        saveCart(cart);
        
        // Update product card button in DOM
        const productCards = document.querySelectorAll(`.offer-card[data-product-id="${productId}"]`);
        productCards.forEach(card => {
            const btn = card.querySelector('.add-to-cart-btn');
            if (btn) {
                btn.className = 'add-to-cart-btn btn-secondary';
                const btnText = btn.querySelector('.btn-text');
                if (btnText) btnText.textContent = 'Añadido ✓';
            }
        });
        
        openCart();
    };
    
    function openCart() {
        const drawer = document.getElementById('cartDrawer');
        const overlay = document.getElementById('cartDrawerOverlay');
        if (drawer) drawer.classList.add('active');
        if (overlay) overlay.classList.add('active');
        renderCart();
    }
    
    function closeCart() {
        const drawer = document.getElementById('cartDrawer');
        const overlay = document.getElementById('cartDrawerOverlay');
        if (drawer) drawer.classList.remove('active');
        if (overlay) overlay.classList.remove('active');
    }
    
    function renderCart() {
        const itemsContainer = document.getElementById('cartDrawerItems');
        if (!itemsContainer) return;
        
        const cart = getCart();
        
        if (cart.length === 0) {
            itemsContainer.innerHTML = `<p class="empty-cart-msg">Tu pedido está vacío. Añade productos desde el catálogo.</p>`;
            return;
        }
        
        itemsContainer.innerHTML = '';
        cart.forEach(item => {
            const itemEl = document.createElement('div');
            itemEl.className = 'cart-item';
            itemEl.innerHTML = `
                <img src="${item.image_url}" alt="${item.name}" class="cart-item-img" onerror="this.src='https://placehold.co/80x80/f4f6f9/0f2c59?text=Producto'">
                <div class="cart-item-info">
                    <span class="cart-item-brand">${item.brand_name}</span>
                    <h4 class="cart-item-title">${item.name}</h4>
                    <div class="cart-item-qty">
                        <button class="qty-btn" onclick="changeQty(${item.id}, -1)">&minus;</button>
                        <span class="qty-val">${item.quantity}</span>
                        <button class="qty-btn" onclick="changeQty(${item.id}, 1)">&plus;</button>
                    </div>
                </div>
                <button class="remove-cart-item" onclick="removeCartItem(${item.id})" aria-label="Eliminar item">&times;</button>
            `;
            itemsContainer.appendChild(itemEl);
        });
    }
    
    window.changeQty = function(productId, delta) {
        let cart = getCart();
        const idx = cart.findIndex(item => item.id == productId);
        if (idx === -1) return;
        
        cart[idx].quantity += delta;
        
        if (cart[idx].quantity <= 0) {
            cart.splice(idx, 1);
            resetCardButton(productId);
        }
        
        saveCart(cart);
        renderCart();
    };
    
    window.removeCartItem = function(productId) {
        let cart = getCart();
        cart = cart.filter(item => item.id != productId);
        resetCardButton(productId);
        saveCart(cart);
        renderCart();
    };
    
    function resetCardButton(productId) {
        const productCards = document.querySelectorAll(`.offer-card[data-product-id="${productId}"]`);
        productCards.forEach(card => {
            const btn = card.querySelector('.add-to-cart-btn');
            if (btn) {
                btn.className = 'add-to-cart-btn';
                const btnText = btn.querySelector('.btn-text');
                if (btnText) btnText.textContent = 'Añadir al Pedido';
            }
        });
    }
    
    function setupCartDrawerEvents() {
        const floatBtn = document.getElementById('cartFloatingBtn');
        const closeBtn = document.getElementById('closeCartDrawer');
        const overlay = document.getElementById('cartDrawerOverlay');
        const checkoutBtn = document.getElementById('checkoutCartBtn');
        
        if (floatBtn) floatBtn.addEventListener('click', openCart);
        if (closeBtn) closeBtn.addEventListener('click', closeCart);
        if (overlay) overlay.addEventListener('click', closeCart);
        
        if (checkoutBtn) {
            checkoutBtn.addEventListener('click', () => {
                const cart = getCart();
                if (cart.length === 0) return;
                
                let commentsText = "Detalle de Pedido Mayorista:\n";
                cart.forEach(item => {
                    commentsText += `- [${item.brand_name}] ${item.name} (Cantidad: ${item.quantity})\n`;
                });
                
                const bottomComments = document.getElementById('bottom-comments');
                const heroComments = document.getElementById('hero-comments');
                const middleComments = document.getElementById('middle-comments');
                
                if (bottomComments) bottomComments.value = commentsText;
                if (heroComments) heroComments.value = commentsText;
                if (middleComments) middleComments.value = commentsText;
                
                closeCart();
                
                const contactSection = document.getElementById('contacto');
                if (contactSection) {
                    contactSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        }
    }

    // --- FASE 11: Welcome Promo Popup Logic ---
    function setupWelcomePopup() {
        const popup = document.getElementById('welcomePopup');
        const closeBtn = document.getElementById('closeWelcomePopup');
        const ctaBtn = document.getElementById('welcomePopupCta');
        
        if (!popup) return;
        
        // Only show if enabled in globalSettings
        if (globalSettings.welcome_popup_enabled !== '1') {
            popup.style.display = 'none';
            return;
        }
        
        // Update popup values dynamically
        const bannerImg = popup.querySelector('.welcome-popup-banner img');
        if (bannerImg && globalSettings.welcome_popup_image) {
            bannerImg.src = globalSettings.welcome_popup_image + '?v=18';
        }
        const titleH2 = popup.querySelector('.welcome-popup-content h2');
        if (titleH2 && globalSettings.welcome_popup_title) {
            titleH2.textContent = globalSettings.welcome_popup_title;
        }
        const descP = popup.querySelector('.welcome-popup-content p');
        if (descP && globalSettings.welcome_popup_description) {
            descP.textContent = globalSettings.welcome_popup_description;
        }
        if (ctaBtn && globalSettings.welcome_popup_btn_text) {
            ctaBtn.textContent = globalSettings.welcome_popup_btn_text;
        }
        
        if (!sessionStorage.getItem('welcome_popup_shown')) {
            setTimeout(() => {
                popup.style.display = 'flex';
                // Trigger transition
                setTimeout(() => {
                    popup.classList.add('active');
                }, 50);
            }, 1500);
        }
        
        function dismissPopup() {
            popup.classList.remove('active');
            setTimeout(() => {
                popup.style.display = 'none';
            }, 300);
            sessionStorage.setItem('welcome_popup_shown', 'true');
        }
        
        if (closeBtn) closeBtn.addEventListener('click', dismissPopup);
        if (popup) {
            popup.addEventListener('click', (e) => {
                if (e.target === popup) {
                    dismissPopup();
                }
            });
        }
        
        if (ctaBtn) {
            ctaBtn.addEventListener('click', () => {
                dismissPopup();
                const catalogSection = document.getElementById('catalogo');
                if (catalogSection) {
                    catalogSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        }
    }

    // Init Cart
    updateCartBadge();
    setupCartDrawerEvents();
});

// Dynamic Carousel Logic
document.addEventListener('DOMContentLoaded', () => {
    const bgContainer = document.getElementById('hero-carousel-bg');
    if (!bgContainer) return;
    
    // Create style for crossfade animation
    const style = document.createElement('style');
    style.innerHTML = `
        .carousel-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            opacity: 0;
            transition: opacity 1.5s ease-in-out;
            z-index: 0;
        }
        .carousel-slide.active {
            opacity: 1;
        }
    `;
    document.head.appendChild(style);

    fetch('api/carousel.php')
        .then(res => res.json())
        .then(response => {
            const images = response.data || [];
            if (images.length === 0) return;
            
            // Create slides
            const slides = [];
            images.forEach((imgObj, index) => {
                const div = document.createElement('div');
                div.className = 'carousel-slide' + (index === 0 ? ' active' : '');
                div.style.backgroundImage = `url('${imgObj.imagen || imgObj.image_url}')`;
                bgContainer.appendChild(div);
                slides.push(div);
            });
            
            // Rotate slides if more than 1
            if (slides.length > 1) {
                let currentIndex = 0;
                setInterval(() => {
                    slides[currentIndex].classList.remove('active');
                    currentIndex = (currentIndex + 1) % slides.length;
                    slides[currentIndex].classList.add('active');
                }, 5000); // 5 seconds
            }
        })
        .catch(err => console.error('Error fetching carousel images:', err));
});

// RUT Validation function
function validateRUT(rut) {
    if (!rut || rut.trim() === '') return true; // Optional fields shouldn't fail validation if empty
    
    // Clean RUT: remove dots and hyphens
    let value = rut.replace(/\./g, '').replace(/-/g, '');
    
    if (value.length < 8) return false;
    
    // Extract body and verifier digit
    const body = value.slice(0, -1);
    const dv = value.slice(-1).toUpperCase();
    
    // Calculate expected DV
    let sum = 0;
    let multiplier = 2;
    for (let i = body.length - 1; i >= 0; i--) {
        sum += parseInt(body.charAt(i)) * multiplier;
        multiplier = multiplier === 7 ? 2 : multiplier + 1;
    }
    
    const remainder = sum % 11;
    const expectedDv = 11 - remainder;
    
    let calculatedDv = expectedDv.toString();
    if (expectedDv === 11) calculatedDv = '0';
    if (expectedDv === 10) calculatedDv = 'K';
    
    return dv === calculatedDv;
}

// Hook RUT validation to all forms
document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        const rutInput = form.querySelector('[name="rut"]');
        if (rutInput) {
            form.addEventListener('submit', (e) => {
                const rutValue = rutInput.value.trim();
                if (rutValue !== '' && !validateRUT(rutValue)) {
                    e.preventDefault();
                    e.stopPropagation();
                    alert('El RUT ingresado no es válido. Por favor verifique el formato (ej: 12.345.678-9).');
                }
            }, true); // Use capture phase to intercept before fetch
        }
    });
});
