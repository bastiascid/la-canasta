/**
 * La Canasta - JavaScript Logic
 */

const WHATSAPP_NUMBER = '56912345678'; 

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
