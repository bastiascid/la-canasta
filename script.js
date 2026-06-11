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
                    
                    // Handle Logo replacement (if image exists, use it, else keep text)
                    const logoContainer = document.getElementById('logoContainer');
                    const footerLogoTextNode = document.getElementById('footerLogoTextNode');
                    if (globalSettings.logo_url) {
                        const logoUrlWithVersion = globalSettings.logo_url + '?v=2';
                        const imgHtml = `<img src="${logoUrlWithVersion}" alt="La Canasta Logo" style="height: 60px; max-width: 160px; object-fit: contain;">`;
                        if (logoContainer) {
                            logoContainer.innerHTML = imgHtml;
                            logoContainer.style.height = '60px';
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
        const track = document.getElementById('brandsCarouselTrack');
        if (!track) return;
        
        if (globalBrands.length === 0) {
            track.innerHTML = `<p class="text-center" style="width: 100%; color: var(--color-text-muted); padding: 2rem 0;">No hay marcas registradas en este momento.</p>`;
            return;
        }
        
        track.innerHTML = '';
        globalBrands.forEach(brand => {
            const slide = document.createElement('div');
            slide.className = 'carousel-slide';
            
            slide.innerHTML = `
                <div class="brand-showcase-card" style="margin: 0; width: 100%; display: flex; flex-direction: column;">
                    <div class="brand-header-flex">
                        <img src="${brand.logo_url}" alt="${brand.name} Logo" class="brand-logo-img" onerror="this.src='https://placehold.co/120x80/0f2c59/ffffff?text=${brand.name}'">
                        <h3 style="color: var(--color-primary); font-size: 1.5rem; margin: 0;">${brand.name}</h3>
                    </div>
                    <p style="font-size: 0.95rem; line-height: 1.5; flex-grow: 1;">${brand.description || 'Distribución mayorista autorizada.'}</p>
                    <div style="margin-top: 1.25rem; font-size: 0.85rem; display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                        <a href="${brand.official_url && brand.official_url !== '#' ? brand.official_url : '#contacto'}" ${brand.official_url && brand.official_url !== '#' ? 'target="_blank" rel="noopener"' : ''} style="text-decoration: underline; font-weight: 700; color: var(--color-secondary);">
                            Sitio Oficial &rarr;
                        </a>
                    </div>
                </div>
            `;
            
            track.appendChild(slide);
            scrollObserver.observe(slide);
        });

        // Initialize Carousel
        initBrandsCarousel();
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
        filtered.forEach(product => {
            const card = document.createElement('div');
            card.className = 'offer-card animate-on-scroll';
            
            card.innerHTML = `
                <div class="offer-badge" style="background-color: var(--color-primary);">${product.category}</div>
                <div class="offer-img-wrapper" style="height: 200px;">
                    <img src="${product.image_url}" alt="${product.name}" onerror="this.src='https://placehold.co/400x300/f4f6f9/0f2c59?text=Producto'" loading="lazy">
                </div>
                <div class="offer-details" style="display: flex; flex-direction: column; height: calc(100% - 200px);">
                    <span style="font-size: 0.75rem; font-weight: 700; color: var(--color-secondary); text-transform: uppercase;">${product.brand_name}</span>
                    <h3 class="offer-name" style="margin-top: 5px; min-height: 48px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">${product.name}</h3>
                    <p class="offer-desc" style="flex-grow: 1; min-height: 60px; margin-bottom: 1.5rem; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">${product.description || 'Abarrotes para venta exclusiva en locales comerciales.'}</p>
                    <button class="btn btn-primary btn-sm" onclick="inquireProduct('${product.name.replace(/'/g, "\\'")}', '${product.brand_name.replace(/'/g, "\\'")}')" style="justify-content: center; width: 100%; border-radius: 6px;">
                        Solicitar Información
                    </button>
                </div>
            `;
            catalogGrid.appendChild(card);
            scrollObserver.observe(card);
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
                    email: document.getElementById('hero-email').value.trim(),
                    company: document.getElementById('hero-company').value.trim(),
                    role: document.getElementById('hero-role').value.trim(),
                    phone: document.getElementById('hero-phone').value.trim(),
                    region: document.getElementById('hero-region').value,
                    comments: document.getElementById('hero-comments').value.trim(),
                    origin: 'Formulario Hero'
                };
            } else if (formId === 'middleContactForm') {
                payload = {
                    name: document.getElementById('middle-name').value.trim(),
                    email: document.getElementById('middle-email').value.trim(),
                    company: document.getElementById('middle-company').value.trim(),
                    role: document.getElementById('middle-role').value.trim(),
                    phone: document.getElementById('middle-phone').value.trim(),
                    region: document.getElementById('middle-region').value,
                    comments: document.getElementById('middle-comments').value.trim(),
                    origin: 'Formulario Mitad Página'
                };
            } else {
                payload = {
                    name: document.getElementById('bottom-name').value.trim(),
                    email: document.getElementById('bottom-email').value.trim(),
                    company: document.getElementById('bottom-company').value.trim(),
                    role: document.getElementById('bottom-role').value.trim(),
                    phone: document.getElementById('bottom-phone').value.trim(),
                    region: document.getElementById('bottom-region').value,
                    comments: document.getElementById('bottom-comments').value.trim(),
                    origin: 'Formulario Footer'
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
                        card.className = 'brand-showcase-card animate-on-scroll';
                        card.style.padding = '2rem';
                        card.style.textAlign = 'center';
                        card.style.background = 'white';
                        card.style.border = '1px solid var(--color-border)';
                        card.style.borderRadius = 'var(--border-radius)';
                        card.style.boxShadow = 'var(--shadow-sm)';
                        card.style.display = 'flex';
                        card.style.flexDirection = 'column';
                        card.style.alignItems = 'center';
                        card.style.justifyContent = 'center';
                        card.style.transition = 'all 0.3s ease';
                        
                        card.innerHTML = `
                            <img src="${p.logo_url}" alt="${p.name} Logo" style="height: 60px; max-width: 100%; object-fit: contain; margin-bottom: 1rem;" onerror="this.src='https://placehold.co/120x60/0f2c59/ffffff?text=${encodeURIComponent(p.name)}'">
                            <h4 style="font-size: 1.1rem; color: var(--color-primary); margin: 0 0 0.5rem 0; font-weight: 700;">${p.name}</h4>
                            <p style="font-size: 0.85rem; color: var(--color-text-muted); line-height: 1.4; margin: 0 0 1rem 0;">${p.description || ''}</p>
                            ${p.link_url && p.link_url !== '#' ? `<a href="${p.link_url}" target="_blank" rel="noopener noreferrer" style="font-size: 0.8rem; font-weight: 700; color: var(--color-secondary); text-decoration: underline;">Saber más</a>` : ''}
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

    // Initialize Page functions
    loadSettings();
    loadCatalogData();
    loadCoverageZones();
    loadOffersBanners();
    loadPartners();
    loadSubBrandsTicker();
    initAdvantagesCarousel();
    handleFormSubmit('heroContactForm', 'heroFormSuccess');
    handleFormSubmit('middleContactForm', 'middleFormSuccess');
    handleFormSubmit('bottomContactForm', 'bottomFormSuccess');
});
