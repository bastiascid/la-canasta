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
                    const logoTextNode = document.getElementById('logoTextNode');
                    const footerLogoTextNode = document.getElementById('footerLogoTextNode');
                    if (globalSettings.logo_url && globalSettings.logo_url !== 'assets/canasta-logo.png') {
                        const imgHtml = `<img src="${globalSettings.logo_url}" alt="La Canasta Logo" style="height: 40px; max-width: 150px; object-fit: contain;">`;
                        if (logoTextNode) logoTextNode.innerHTML = imgHtml;
                        if (footerLogoTextNode) footerLogoTextNode.innerHTML = imgHtml;
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
                <div class="brand-showcase-card" style="margin: 0; width: 100%;">
                    <div class="brand-header-flex">
                        <img src="${brand.logo_url}" alt="${brand.name} Logo" class="brand-logo-img" onerror="this.src='https://placehold.co/120x80/0f2c59/ffffff?text=${brand.name}'">
                        <h3 style="color: var(--color-primary); font-size: 1.5rem; margin: 0;">${brand.name}</h3>
                    </div>
                    <p style="font-size: 0.95rem; line-height: 1.5; flex-grow: 1;">${brand.description || 'Distribución mayorista autorizada.'}</p>
                    ${brand.official_url && brand.official_url !== '#' ? `
                        <div style="margin-top: 1.25rem; font-size: 0.85rem;">
                            <a href="${brand.official_url}" target="_blank" rel="noopener" style="text-decoration: underline; font-weight: 600;">Visitar Sitio Oficial &rarr;</a>
                        </div>` : ''}
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
            
            // Determine elements based on ID
            let payload = {};
            if (formId === 'heroContactForm') {
                payload = {
                    name: document.getElementById('hero-name').value.trim(),
                    email: document.getElementById('hero-email').value.trim(),
                    company: document.getElementById('hero-company').value.trim(),
                    role: document.getElementById('hero-role').value.trim(),
                    phone: document.getElementById('hero-phone').value.trim(),
                    region: document.getElementById('hero-region').value,
                    comments: document.getElementById('hero-comments').value.trim()
                };
            } else {
                payload = {
                    name: document.getElementById('bottom-name').value.trim(),
                    email: document.getElementById('bottom-email').value.trim(),
                    company: document.getElementById('bottom-company').value.trim(),
                    role: document.getElementById('bottom-role').value.trim(),
                    phone: document.getElementById('bottom-phone').value.trim(),
                    region: document.getElementById('bottom-region').value,
                    comments: `[${document.getElementById('bottom-action').value}] ${document.getElementById('bottom-comments').value.trim()}`
                };
            }
            
            fetch('api/leads.php', {
                method: 'POST',
                headers: { 'Content-Type:': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    form.style.display = 'none';
                    if (successDiv) {
                        successDiv.style.display = 'flex';
                        successDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                } else {
                    alert('Error: ' + res.message);
                }
            })
            .catch(err => {
                console.error("Error submitting form:", err);
                alert("Hubo un error de conexión al procesar tu registro. Por favor, intenta de nuevo.");
            });
        });
    }

    // --- 4. Scroll Animations Setup ---
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

    // Initialize Page functions
    loadSettings();
    loadCatalogData();
    handleFormSubmit('heroContactForm', 'heroFormSuccess');
    handleFormSubmit('bottomContactForm', 'bottomFormSuccess');
});
