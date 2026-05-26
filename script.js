/**
 * La Canasta - JavaScript Logic
 */

const WHATSAPP_NUMBER = '56912345678'; 

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

function openWhatsApp(text) {
    const encodedText = encodeURIComponent(text);
    const url = `https://wa.me/${WHATSAPP_NUMBER}?text=${encodedText}`;
    window.open(url, '_blank', 'noopener,noreferrer');
}

document.addEventListener('DOMContentLoaded', () => {

    // --- Animations and Scroll ---
    const navbar = document.querySelector('.navbar');
    window.addEventListener('scroll', () => {
        if (navbar) {
            if (window.scrollY > 20) {
                navbar.style.boxShadow = '0 4px 6px -1px rgb(0 0 0 / 0.1)';
            } else {
                navbar.style.boxShadow = 'none';
            }
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

    // --- Offers Render Logic ---
    const offersGrid = document.getElementById('offersGrid');
    
    function formatCurrency(value) {
        return new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(value);
    }
    
    function loadOffers() {
        if (!offersGrid) return;
        
        let stored = localStorage.getItem('laCanastaOffers');
        let offers = [];
        
        if (stored === null) {
            // Initial load of defaults if empty/first visit
            offers = DEFAULT_OFFERS;
            localStorage.setItem('laCanastaOffers', JSON.stringify(offers));
        } else {
            offers = JSON.parse(stored);
        }
        
        if (offers.length === 0) {
            offersGrid.innerHTML = `
                <div class="text-center" style="grid-column: 1 / -1; padding: 3.5rem; background: var(--color-bg-white); border-radius: 12px; border: 1px dashed var(--color-border); width: 100%;">
                    <p style="font-size: 1.1rem; color: var(--color-text-muted); font-weight: 600;">No hay ofertas especiales vigentes en este momento.</p>
                    <p style="font-size: 0.95rem; margin-top: 5px;">Por favor, vuelve más tarde o contáctanos por WhatsApp para consultar precios.</p>
                </div>`;
            return;
        }
        
        offersGrid.innerHTML = '';
        offers.forEach(offer => {
            const card = document.createElement('div');
            card.className = 'offer-card animate-on-scroll';
            
            // Calculate discount percentage
            const discount = Math.round(((offer.normalPrice - offer.promoPrice) / offer.normalPrice) * 100);
            
            card.innerHTML = `
                <div class="offer-badge">${discount}% desc.</div>
                <div class="offer-img-wrapper">
                    <img src="${offer.image}" alt="${offer.name}" onerror="this.src='https://placehold.co/400x300/fdf6e3/1a5d2e?text=Oferta'" loading="lazy">
                </div>
                <div class="offer-details">
                    <h3 class="offer-name">${offer.name}</h3>
                    <p class="offer-desc">${offer.description}</p>
                    <div class="offer-pricing">
                        <span class="offer-price-normal">${formatCurrency(offer.normalPrice)}</span>
                        <span class="offer-price-promo">${formatCurrency(offer.promoPrice)}</span>
                    </div>
                    <button class="btn-order-offer" onclick="orderOffer('${offer.name.replace(/'/g, "\\'")}', ${offer.promoPrice})">
                        Pedir Oferta
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" class="icon" style="margin-left: 5px;"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                    </button>
                </div>
            `;
            offersGrid.appendChild(card);
            scrollObserver.observe(card);
        });
    }
    
    window.orderOffer = function(name, price) {
        const waMessage = `Hola La Canasta, me interesa pedir la oferta especial de "${name}" por valor de ${formatCurrency(price)}.`;
        openWhatsApp(waMessage);
    };
    
    loadOffers();

    // --- Contact Form Logic ---
    const contactForm = document.getElementById('contactForm');
    const formSuccess = document.getElementById('formSuccess');
    const sendToWhatsAppBtn = document.getElementById('sendToWhatsAppBtn');

    if (contactForm) {
        contactForm.addEventListener('submit', (e) => {
            e.preventDefault();

            // Get form values
            const name = document.getElementById('form-name').value.trim();
            const phone = document.getElementById('form-phone').value.trim();
            const business = document.getElementById('form-business').value;
            const message = document.getElementById('form-message').value.trim();

            // Simple validation
            if (!name || !phone || !business || !message) {
                alert('Por favor, completa todos los campos requeridos.');
                return;
            }

            // Hide form and show success message
            contactForm.style.display = 'none';
            if (formSuccess) {
                formSuccess.style.display = 'flex';
                formSuccess.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            // Configure WhatsApp secondary button action
            if (sendToWhatsAppBtn) {
                sendToWhatsAppBtn.onclick = () => {
                    const waMessage = `Hola La Canasta, me llamo ${name}. Tengo un negocio del tipo "${business}" y mi teléfono de contacto es ${phone}.\n\nConsulta:\n${message}`;
                    openWhatsApp(waMessage);
                };
            }
        });
    }

});
