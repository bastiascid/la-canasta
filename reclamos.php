<?php
// reclamos.php
// Public claims submission form

require_once 'api/db.php';

try {
    // Fetch Settings (logo, whatsapp)
    $stmt_s = $pdo->query("SELECT `key`, `value` FROM settings");
    $settings = $stmt_s->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $logo_url = isset($settings['logo_url']) ? $settings['logo_url'] : 'assets/canasta-logo.webp';
    $whatsapp_enabled = isset($settings['whatsapp_enabled']) ? $settings['whatsapp_enabled'] : '0';
    $whatsapp_number = isset($settings['whatsapp_number']) ? $settings['whatsapp_number'] : '+56 9 4256 7472';

} catch (Exception $e) {
    // Fallback default variables if db error
    $logo_url = 'assets/canasta-logo.webp';
    $whatsapp_enabled = '0';
    $whatsapp_number = '+56 9 4256 7472';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Reclamos - La Canasta Comercializadora y Distribuidora</title>
    <meta name="description" content="Canal formal para el ingreso y seguimiento de reclamos, mermas o problemas con tu pedido. Atendemos tu caso a la brevedad.">
    <link rel="shortcut icon" href="favicon.png" type="image/png">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://www.lacanastacomercializadora.cl/reclamos.php">
    <meta property="og:title" content="Formulario de Reclamos - La Canasta">
    <meta property="og:description" content="Canal de postventa para el ingreso de requerimientos o reclamos. Comercializadora La Canasta.">
    <meta property="og:image" content="https://www.lacanastacomercializadora.cl/assets/canasta-logo.webp">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="styles.css?v=18">
    
    <style>
        .page-header-banner {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);
            color: white;
            padding: 6rem 0 3.5rem 0;
            text-align: center;
        }
        .reclamos-grid {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 4rem;
            align-items: flex-start;
            padding: 5rem 0;
        }
        .info-card-claim {
            background-color: var(--color-bg-light);
            border: 1px solid var(--color-border);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--shadow-sm);
        }
        .info-card-claim h3 {
            color: var(--color-primary);
            font-size: 1.35rem;
            margin-top: 0;
            margin-bottom: 1.25rem;
            font-weight: 700;
        }
        .info-card-claim p {
            font-size: 0.95rem;
            color: var(--color-text-main);
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        .claim-channel-item {
            display: flex;
            gap: 0.85rem;
            margin-bottom: 1.25rem;
            align-items: flex-start;
        }
        .claim-channel-icon {
            background-color: rgba(93, 70, 55, 0.1);
            color: var(--color-primary);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1rem;
        }
        .claim-channel-text strong {
            display: block;
            font-size: 0.9rem;
            color: var(--color-primary);
        }
        .claim-channel-text span, .claim-channel-text a {
            font-size: 0.85rem;
            color: var(--color-text-muted);
            text-decoration: none;
        }
        .claim-channel-text a:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .reclamos-grid {
                grid-template-columns: 1fr;
                gap: 2.5rem;
                padding: 3rem 0;
            }
        }
    </style>
</head>
<body>
    
    <!-- Upper Utility Bar -->
    <div style="background-color: var(--color-primary); color: white; font-size: 0.8rem; padding: 0.5rem 0; border-bottom: 1px solid rgba(255,255,255,0.1);">
        <div class="container" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
            <div>
                <span style="margin-right: 1.5rem;">📧 <a href="mailto:contacto@lacanastacomercializadora.cl" style="color: white; text-decoration: none;">contacto@lacanastacomercializadora.cl</a></span>
                <span>📞 Call Center: +56 9 4256 7472</span>
            </div>
            <div style="display: flex; gap: 1.5rem;">
                <a href="sobre-nosotros.php" style="color: white; text-decoration: none; font-weight: 600;">Sobre Nosotros</a>
                <a href="hazte-cliente.php" style="color: var(--color-secondary); text-decoration: none; font-weight: 700;">Hazte Cliente</a>
            </div>
        </div>
    </div>

    <!-- Header / Navigation -->
    <nav class="navbar" style="position: relative; box-shadow: var(--shadow-sm); background: white; padding: 1rem 0;">
        <div class="container nav-content">
            <a href="index.html" class="logo" style="display: flex; flex-direction: column; align-items: center; text-decoration: none; text-align: center; gap: 0.15rem; margin-top: -5px; margin-bottom: -5px;">
                <?php if (!empty($logo_url)): ?>
                    <img src="<?php echo htmlspecialchars($logo_url); ?>?v=2" alt="La Canasta Logo" style="height: 50px; max-width: 150px; object-fit: contain;">
                <?php endif; ?>
                <span style="font-size: 0.6rem; color: var(--color-text-muted); font-weight: 700; line-height: 1.1; margin-top: 3px;">Comercializadora y Distribuidora</span>
            </a>
            
            <ul class="nav-links">
                <li><a href="index.html">Inicio</a></li>
                <li><a href="sobre-nosotros.php">Sobre Nosotros</a></li>
                <li><a href="index.html#marcas">Marcas</a></li>
                <li><a href="index.html#cobertura">Cobertura</a></li>
                <li><a href="index.html#promociones">Ofertas</a></li>
                <li><a href="index.html#catalogo">Catálogo</a></li>
                <li><a href="hazte-cliente.php" class="btn-secondary text-white btn-sm" style="padding: 0.5rem 1rem;">Hazte Cliente</a></li>
            </ul>
        </div>
    </nav>

    <!-- Header Banner -->
    <header class="page-header-banner">
        <div class="container">
            <h1 style="font-family: var(--font-heading); font-size: 2.5rem; margin: 0 0 0.5rem 0; font-weight: 800;">Ingreso de Reclamos y Mermas</h1>
            <p style="font-size: 1.1rem; margin: 0; opacity: 0.9;">Canal formal de postventa y soporte comercial para comercios asociados</p>
        </div>
    </header>

    <div class="container">
        <div class="reclamos-grid">
            <!-- Left Side: Instructions and Support -->
            <div class="animate-on-scroll">
                <div class="info-card-claim">
                    <h3>Atención Postventa La Canasta</h3>
                    <p>En Comercializadora y Distribuidora La Canasta valoramos la calidad de nuestro servicio de despacho. Si has experimentado un problema con tu pedido, diferencias en facturación, mermas de origen o demoras, por favor completa este formulario.</p>
                    
                    <div style="margin: 2rem 0; border-top: 1px dashed var(--color-border); padding-top: 1.5rem;">
                        <div class="claim-channel-item">
                            <div class="claim-channel-icon">📝</div>
                            <div class="claim-channel-text">
                                <strong>Plazo de Reporte</strong>
                                <span>Reporta mermas físicas dentro de las primeras 48 horas hábiles de recibido el despacho.</span>
                            </div>
                        </div>
                        <div class="claim-channel-item">
                            <div class="claim-channel-icon">📧</div>
                            <div class="claim-channel-text">
                                <strong>Correo de Atención Único</strong>
                                <a href="mailto:contacto@lacanastacomercializadora.cl">contacto@lacanastacomercializadora.cl</a>
                            </div>
                        </div>
                        <div class="claim-channel-item">
                            <div class="claim-channel-icon">📞</div>
                            <div class="claim-channel-text">
                                <strong>Línea Telefónica</strong>
                                <span>+56 9 4256 7472 (Lunes a Sábado de 08:30 a 18:30)</span>
                            </div>
                        </div>
                    </div>
                    
                    <div style="background-color: rgba(93, 70, 55, 0.05); padding: 1rem 1.25rem; border-radius: 6px; border-left: 3px solid var(--color-primary); font-size: 0.85rem; color: var(--color-text-dark); line-height: 1.4;">
                        <strong>Nota Importante:</strong> Ten a mano tu número de Factura o Guía de Despacho física para agilizar la revisión y la emisión de Notas de Crédito correspondientes.
                    </div>
                </div>
            </div>

            <!-- Right Side: The Form -->
            <div class="contact-form-container animate-on-scroll" style="margin-top: 0;">
                <div class="form-slogan">Soporte y Garantía</div>
                <h3 class="form-title" style="margin-bottom: 2rem; text-align: center;">Ingresa tu <span class="highlight">Requerimiento</span></h3>
                
                <form id="claimSubmissionForm" class="contact-form">
                    <div class="form-grid">
                        <div class="form-group">
                            <label style="font-weight: 700; font-size: 0.85rem; color: var(--color-text-dark);">Nombre Completo *</label>
                            <input type="text" name="name" required placeholder="Ej: Juan Pérez" style="padding: 0.75rem; border-radius: var(--border-radius); border: 1px solid var(--color-border); font-size: 0.9rem;">
                        </div>
                        <div class="form-group">
                            <label style="font-weight: 700; font-size: 0.85rem; color: var(--color-text-dark);">Nombre del Negocio (Razón Social)</label>
                            <input type="text" name="company" placeholder="Ej: Minimarket El Bosque" style="padding: 0.75rem; border-radius: var(--border-radius); border: 1px solid var(--color-border); font-size: 0.9rem;">
                        </div>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label style="font-weight: 700; font-size: 0.85rem; color: var(--color-text-dark);">RUT Comercial / Negocio</label>
                            <input type="text" name="rut" placeholder="Ej: 76.xxx.xxx-x" style="padding: 0.75rem; border-radius: var(--border-radius); border: 1px solid var(--color-border); font-size: 0.9rem;">
                        </div>
                        <div class="form-group">
                            <label style="font-weight: 700; font-size: 0.85rem; color: var(--color-text-dark);">Teléfono de Contacto *</label>
                            <input type="tel" name="phone" required placeholder="Ej: +56 9 1234 5678" style="padding: 0.75rem; border-radius: var(--border-radius); border: 1px solid var(--color-border); font-size: 0.9rem;">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label style="font-weight: 700; font-size: 0.85rem; color: var(--color-text-dark);">Correo Electrónico *</label>
                        <input type="email" name="email" required placeholder="Ej: contacto@minisuper.cl" style="padding: 0.75rem; border-radius: var(--border-radius); border: 1px solid var(--color-border); font-size: 0.9rem;">
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label style="font-weight: 700; font-size: 0.85rem; color: var(--color-text-dark);">Tipo de Reclamo *</label>
                            <select name="claim_type" required style="padding: 0.75rem; border-radius: var(--border-radius); border: 1px solid var(--color-border); font-size: 0.9rem; background: white;">
                                <option value="" disabled selected>Selecciona una opción</option>
                                <option value="Merma física o Producto Defectuoso">Merma física / Producto Defectuoso</option>
                                <option value="Diferencia de Facturación / Precios">Diferencia de Facturación o Precios</option>
                                <option value="Problemas con la Entrega / Chofer">Problemas con la Entrega</option>
                                <option value="Productos Faltantes en Pedido">Productos Faltantes en Pedido</option>
                                <option value="Otros Requerimientos">Otros Requerimientos</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label style="font-weight: 700; font-size: 0.85rem; color: var(--color-text-dark);">Número de Factura / Guía</label>
                            <input type="text" name="invoice_number" placeholder="Ej: Factura N° 12450" style="padding: 0.75rem; border-radius: var(--border-radius); border: 1px solid var(--color-border); font-size: 0.9rem;">
                        </div>
                    </div>

                    <div class="form-group">
                        <label style="font-weight: 700; font-size: 0.85rem; color: var(--color-text-dark);">Detalle del Reclamo / Requerimiento *</label>
                        <textarea name="comments" rows="4" required placeholder="Describe de manera detallada el problema y los productos afectados (marca, unidades, lote si corresponde)..." style="padding: 0.75rem; border-radius: var(--border-radius); border: 1px solid var(--color-border); font-size: 0.9rem; font-family: inherit; resize: none;"></textarea>
                    </div>

                    <div style="font-size: 0.8rem; color: var(--color-text-muted); margin-bottom: 1.25rem; text-align: center;">
                        * Campos requeridos obligatorios. Se enviará comprobante de caso al correo proporcionado.
                    </div>

                    <button type="submit" id="submitClaimBtn" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 0.9rem; border-radius: 6px; font-weight: 700;">
                        Enviar Reclamo Formal
                    </button>
                </form>

                <div id="claimFormSuccess" class="form-success-alert" style="display: none; padding: 2rem 1.5rem; text-align: center; gap: 0.75rem; margin-top: 1.5rem;">
                    <div class="success-icon" style="width: 48px; height: 48px; display: inline-flex; align-items: center; justify-content: center; background-color: #2e7d32; color: white; border-radius: 50%; margin: 0 auto 10px auto;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="width: 24px; height: 24px;"><polyline points="20 6 9 17 4 12"></polyline></svg></div>
                    <h4 style="color: var(--color-primary); font-size: 1.35rem; font-weight: 800; margin: 0;">¡Reclamo Registrado!</h4>
                    <p id="successCaseText" style="font-size: 0.95rem; color: var(--color-text-main); margin: 5px 0 0 0;"></p>
                    <p style="font-size: 0.85rem; color: var(--color-text-muted); margin-top: 10px;">Hemos enviado una confirmación de caso a tu correo. Un ejecutivo del área de operaciones y postventa te contactará en un plazo máximo de 24-48 horas hábiles.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container footer-grid">
            <div class="footer-brand">
                <a href="index.html" class="logo logo-white" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; margin-bottom: 0.5rem;">
                    <?php if (!empty($logo_url)): ?>
                        <img src="<?php echo htmlspecialchars($logo_url); ?>?v=2" alt="La Canasta Logo" style="height: 38px; max-width: 130px; object-fit: contain; background: white; padding: 4px; border-radius: 4px; box-shadow: var(--shadow-sm);">
                    <?php endif; ?>
                    <div style="display: flex; flex-direction: column; justify-content: center;">
                        <span class="logo-light" style="font-size: 0.65rem; color: rgba(255,255,255,0.6); font-weight: 700; margin-top: 2px; line-height: 1.2;">Comercializadora y<br>Distribuidora</span>
                    </div>
                </a>
                <p style="margin-top: 1rem; font-size: 0.9rem; opacity: 0.7; line-height: 1.6;">
                    Operador comercial y logístico B2B. Conectando marcas líderes con el canal tradicional y comercio local de la Región de O'Higgins.
                </p>
            </div>
            
            <div class="footer-links" style="display: flex; flex-direction: column; gap: 0.85rem;">
                <h4 style="color: white; margin-bottom: 0.25rem;">Contacto Corporativo</h4>
                <div style="display: flex; flex-direction: column; gap: 0.15rem;">
                    <span style="font-size: 0.75rem; text-transform: uppercase; color: #9ca3af; font-weight: 700; display: block;">Correo de Contacto Único</span>
                    <a href="mailto:contacto@lacanastacomercializadora.cl" style="color: white; text-decoration: none; font-size: 0.9rem;">contacto@lacanastacomercializadora.cl</a>
                </div>
                <div style="display: flex; flex-direction: column; gap: 0.15rem;">
                    <span style="font-size: 0.75rem; text-transform: uppercase; color: #9ca3af; font-weight: 700; display: block;">Línea Directa / Call Center</span>
                    <a href="tel:56942567472" style="color: white; text-decoration: none; font-size: 0.9rem;">+56 9 4256 7472</a>
                </div>
            </div>

            <div class="footer-links" style="display: flex; flex-direction: column; gap: 0.85rem;">
                <h4 style="color: white; margin-bottom: 0.25rem;">Enlaces del Portal</h4>
                <a href="index.html" style="color: #9ca3af; text-decoration: none; font-size: 0.9rem;">Inicio / Catálogo</a>
                <a href="sobre-nosotros.php" style="color: #9ca3af; text-decoration: none; font-size: 0.9rem;">Sobre Nosotros</a>
                <a href="hazte-cliente.php" style="color: #9ca3af; text-decoration: none; font-size: 0.9rem;">Hazte Cliente</a>
                <a href="reclamos.php" style="color: white; text-decoration: underline; font-weight: bold; font-size: 0.9rem;">Formulario de Reclamos</a>
            </div>
        </div>

        <div class="footer-bottom">
            <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 1rem; width: 100%;">
                <p style="color: #9ca3af; font-size: 0.85rem; margin: 0;">&copy; 2026 La Canasta Comercializadora y Distribuidora. Todos los derechos reservados.</p>
                <div style="display: flex; gap: 1.5rem;">
                    <a href="privacidad.html" style="color: #9ca3af; font-size: 0.8rem; text-decoration: none;">Política de Privacidad</a>
                    <a href="terminos.html" style="color: #9ca3af; font-size: 0.8rem; text-decoration: none;">Términos y Condiciones</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('claimSubmissionForm');
            const successDiv = document.getElementById('claimFormSuccess');
            const submitBtn = document.getElementById('submitClaimBtn');
            const successText = document.getElementById('successCaseText');
            
            if (form) {
                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    
                    submitBtn.disabled = true;
                    const originalBtnText = submitBtn.innerHTML;
                    submitBtn.innerHTML = "Enviando Reclamo...";
                    
                    const formData = {
                        name: form.querySelector('[name="name"]').value.trim(),
                        company: form.querySelector('[name="company"]').value.trim(),
                        rut: form.querySelector('[name="rut"]').value.trim(),
                        phone: form.querySelector('[name="phone"]').value.trim(),
                        email: form.querySelector('[name="email"]').value.trim(),
                        claim_type: form.querySelector('[name="claim_type"]').value,
                        invoice_number: form.querySelector('[name="invoice_number"]').value.trim(),
                        comments: form.querySelector('[name="comments"]').value.trim()
                    };
                    
                    fetch('api/claims.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === 'success') {
                            form.style.display = 'none';
                            successText.innerHTML = `Reclamo enviado exitosamente con el número de ticket: <strong>#${res.case_id}</strong>.`;
                            successDiv.style.display = 'flex';
                            successDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        } else {
                            alert(res.message || "Error al enviar el reclamo.");
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalBtnText;
                        }
                    })
                    .catch(err => {
                        console.error("Error submitting claim:", err);
                        alert("Error de conexión. Intente nuevamente en unos instantes.");
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    });
                });
            }
        });
    </script>
</body>
</html>
