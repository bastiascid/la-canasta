<?php
// hazte-cliente.php
// Public B2B acquisition landing page

require_once 'api/db.php';

try {
    // 1. Fetch Coverage zones
    $stmt_c = $pdo->query("SELECT name FROM coverage ORDER BY sort_order ASC, name ASC");
    $coverage_zones = $stmt_c->fetchAll(PDO::FETCH_COLUMN);

    // 2. Fetch Active Brands
    $stmt_b = $pdo->query("SELECT name, logo_url, slug FROM brands WHERE status = 'Activa' ORDER BY sort_order ASC");
    $brands = $stmt_b->fetchAll(PDO::FETCH_ASSOC);

    // 3. Fetch Settings (logo, whatsapp)
    $stmt_s = $pdo->query("SELECT `key`, `value` FROM settings");
    $settings = $stmt_s->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $logo_url = isset($settings['logo_url']) ? $settings['logo_url'] : 'assets/canasta-logo.png';
    $whatsapp_enabled = isset($settings['whatsapp_enabled']) ? $settings['whatsapp_enabled'] : '0';
    $whatsapp_number = isset($settings['whatsapp_number']) ? $settings['whatsapp_number'] : '+56 9 4256 7472';

} catch (Exception $e) {
    // Fallback default variables if db error
    $coverage_zones = ['Rancagua', 'Machalí', 'Graneros', 'Mostazal', 'San Fernando', 'Santa Cruz'];
    $brands = [];
    $logo_url = 'assets/canasta-logo.png';
    $whatsapp_enabled = '0';
    $whatsapp_number = '+56 9 4256 7472';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hazte Cliente - La Canasta Distribuidora</title>
    <meta name="description" content="Únete a nuestra red de distribución B2B. Abastecimiento de marcas líderes, logística eficiente y atención personalizada para almacenes y minimarkets.">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="styles.css">
    
    
    <!-- Analítica & Tracking (Preparados para Producción) -->
    <!-- Google Analytics 4 -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-XXXXXXXXXX'); // Reemplazar con ID oficial
    </script>
    
    <!-- Meta Pixel (Facebook) -->
    <script>
      !function(f,b,e,v,n,t,s)
      {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
      n.callMethod.apply(n,arguments):n.queue.push(arguments)};
      if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
      n.queue=[];t=b.createElement(e);t.async=!0;
      t.src=v;s=b.getElementsByTagName(e)[0];
      s.parentNode.insertBefore(t,s)}(window, document,'script',
      'https://connect.facebook.net/en_US/fbevents.js');
      fbq('init', 'YOUR_PIXEL_ID'); // Reemplazar con ID oficial
      fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=YOUR_PIXEL_ID&ev=PageView&noscript=1" /></noscript>
    
    <style>
        .page-header-banner {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);
            color: white;
            padding: 6rem 0 3.5rem 0;
            text-align: center;
        }
        .hazte-cliente-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: flex-start;
            padding: 5rem 0;
        }
        .benefit-item {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            align-items: flex-start;
        }
        .benefit-icon {
            background-color: rgba(216, 0, 50, 0.1);
            color: var(--color-secondary);
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-weight: 700;
        }
        .benefit-content h3 {
            font-size: 1.15rem;
            color: var(--color-primary);
            margin: 0 0 0.25rem 0;
            font-weight: 700;
        }
        .benefit-content p {
            margin: 0;
            font-size: 0.95rem;
            color: var(--color-text-muted);
            line-height: 1.5;
        }
        .step-incorporation {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-top: 2rem;
            padding: 1.5rem;
            background: var(--color-bg-light);
            border-radius: var(--border-radius);
        }
        .step-card {
            text-align: center;
        }
        .step-num {
            background-color: var(--color-primary);
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        .step-card h4 {
            font-size: 1rem;
            margin: 0 0 0.25rem 0;
            color: var(--color-primary);
            font-weight: 700;
        }
        .step-card p {
            margin: 0;
            font-size: 0.8rem;
            color: var(--color-text-muted);
        }
        .client-form-card {
            background: white;
            border: 1px solid var(--color-border);
            border-radius: var(--border-radius);
            padding: 3rem;
            box-shadow: var(--shadow-lg);
        }
        .brand-pill-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 1rem;
        }
        .brand-pill {
            background: white;
            border: 1px solid var(--color-border);
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--color-primary);
            box-shadow: var(--shadow-sm);
        }
        @media (max-width: 991px) {
            .hazte-cliente-grid {
                grid-template-columns: 1fr;
                gap: 3rem;
            }
            .client-form-card {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>

    <!-- Upper Utility Bar -->
    <div style="background-color: var(--color-primary); color: white; font-size: 0.8rem; padding: 0.5rem 0; border-bottom: 1px solid rgba(255,255,255,0.1);">
        <div class="container" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
            <div>
                <span style="margin-right: 1.5rem;">📧 <a href="mailto:contacto@lacanastadistribuidora.cl" style="color: white; text-decoration: none;">contacto@lacanastadistribuidora.cl</a></span>
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
            <a href="index.html" class="logo" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none;">
                <?php if (!empty($logo_url)): ?>
                    <img src="<?php echo htmlspecialchars($logo_url); ?>?v=2" alt="La Canasta Logo" style="height: 60px; max-width: 150px; object-fit: contain;">
                <?php endif; ?>
                <div style="display: flex; flex-direction: column; justify-content: center;">
                    <span class="logo-bold" style="line-height: 1.1; font-weight: 800; color: var(--color-primary); font-family: var(--font-heading); font-size: 1.4rem;">La Canasta</span>
                    <span class="logo-light" style="font-size: 0.7rem; color: var(--color-text-muted); font-weight: 700; margin-top: 2px;">Distribuidora</span>
                </div>
            </a>
            
            <ul class="nav-links">
                <li><a href="index.html">Inicio</a></li>
                <li><a href="sobre-nosotros.php">Sobre Nosotros</a></li>
                <li><a href="index.html#marcas">Marcas</a></li>
                <li><a href="index.html#catalogo">Catálogo</a></li>
                <li><a href="hazte-cliente.php" class="btn-secondary text-white btn-sm" style="padding: 0.5rem 1rem; text-decoration: none; display: inline-flex; align-items: center; border-radius: 4px;">Quiero ser Cliente</a></li>
            </ul>
        </div>
    </nav>

    <!-- Page Header Banner -->
    <section class="page-header-banner">
        <div class="container">
            <h1 style="font-size: 2.75rem; font-weight: 800; margin: 0 0 0.5rem 0;">Hazte Cliente</h1>
            <p style="font-size: 1.15rem; color: white; opacity: 0.9; max-width: 700px; margin: 0 auto; line-height: 1.6;">
                Comienza a abastecer tu comercio con marcas líderes nacionales, precios competitivos y un servicio logístico de primer nivel.
            </p>
        </div>
    </section>

    <!-- Content & Form Grid -->
    <main class="container">
        <div class="hazte-cliente-grid">
            
            <!-- Information Column -->
            <div>
                <h2 style="font-size: 1.8rem; font-weight: 700; color: var(--color-primary); margin-bottom: 2rem;">¿Por qué elegir a La Canasta?</h2>
                
                <div class="benefit-item">
                    <div class="benefit-icon">✓</div>
                    <div class="benefit-content">
                        <h3>Marcas de Alta Rotación</h3>
                        <p>Accede directamente al catálogo oficial de Angelmo, Iansa, Watt's, Traverso y Mercado Nacional para asegurar la demanda de tu local.</p>
                    </div>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon">✓</div>
                    <div class="benefit-content">
                        <h3>Despacho Eficiente en 48 Horas</h3>
                        <p>Entregas programadas directo en tu local comercial para que nunca te quedes sin stock de abarrotes esenciales.</p>
                    </div>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon">✓</div>
                    <div class="benefit-content">
                        <h3>Atención y Asesoramiento Comercial</h3>
                        <p>Ejecutivos comerciales en terreno dedicados a apoyarte en la selección de formatos mayoristas con mejor rentabilidad.</p>
                    </div>
                </div>
                
                <!-- Coverage Zonas -->
                <div style="margin-top: 3rem; border-top: 1px solid var(--color-border); padding-top: 2rem;">
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--color-primary); margin-bottom: 1rem;">Nuestra Cobertura Logística</h3>
                    <p style="font-size: 0.95rem; color: var(--color-text-muted); line-height: 1.5; margin-bottom: 1rem;">
                        Realizamos entregas regulares en las siguientes comunas y localidades:
                    </p>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 0.5rem;">
                        <?php foreach ($coverage_zones as $zone): ?>
                            <div style="font-weight: 600; font-size: 0.9rem; color: var(--color-text-dark); display: flex; align-items: center; gap: 5px;">
                                <span style="color: var(--color-secondary);">📍</span> <?php echo htmlspecialchars($zone); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Marcas Representadas -->
                <div style="margin-top: 3rem; border-top: 1px solid var(--color-border); padding-top: 2rem;">
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--color-primary); margin: 0;">Marcas que Distribuimos</h3>
                    <div class="brand-pill-grid">
                        <?php if (count($brands) > 0): ?>
                            <?php foreach ($brands as $b): ?>
                                <span class="brand-pill"><?php echo htmlspecialchars($b['name']); ?></span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="brand-pill">Angelmo</span>
                            <span class="brand-pill">Iansa</span>
                            <span class="brand-pill">Watt's</span>
                            <span class="brand-pill">Traverso</span>
                            <span class="brand-pill">Mercado Nacional</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Incorporación Steps -->
                <div style="margin-top: 3rem;">
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--color-primary); margin-bottom: 1rem;">Proceso de Incorporación</h3>
                    <div class="step-incorporation">
                        <div class="step-card">
                            <span class="step-num">1</span>
                            <h4>Registro</h4>
                            <p>Envías tus datos comerciales.</p>
                        </div>
                        <div class="step-card">
                            <span class="step-num">2</span>
                            <h4>Validación</h4>
                            <p>Un ejecutivo evalúa tu local.</p>
                        </div>
                        <div class="step-card">
                            <span class="step-num">3</span>
                            <h4>Despacho</h4>
                            <p>Recibes tu primer pedido.</p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Form Column -->
            <div id="contacto-seccion">
                <div class="client-form-card">
                    <h3 style="font-size: 1.5rem; font-weight: 800; color: var(--color-primary); margin-bottom: 0.5rem; text-align: center;">Solicitud Comercial</h3>
                    <p style="font-size: 0.85rem; color: var(--color-text-muted); text-align: center; margin-bottom: 2rem;">Completa el formulario y un ejecutivo te contactará en menos de 24 horas hábiles.</p>
                    
                    <form id="hazteClienteForm" class="contact-form" style="display: flex; flex-direction: column; gap: 1rem;">
                        <input type="hidden" name="origin" value="Página Hazte Cliente">
                        
                        <div class="form-group">
                            <label style="font-weight: 700; font-size: 0.85rem; color: var(--color-text-dark);">Nombre Completo *</label>
                            <input type="text" name="name" required placeholder="Ej: Juan Pérez" style="padding: 0.75rem; border-radius: var(--border-radius); border: 1px solid var(--color-border); font-size: 0.9rem;">
                        </div>

                        <div class="form-group">
                            <label style="font-weight: 700; font-size: 0.85rem; color: var(--color-text-dark);">Nombre de la Empresa / Almacén *</label>
                            <input type="text" name="company" required placeholder="Ej: Minimarket La Esquina" style="padding: 0.75rem; border-radius: var(--border-radius); border: 1px solid var(--color-border); font-size: 0.9rem;">
                        </div>

                        <div class="form-group">
                            <label style="font-weight: 700; font-size: 0.85rem; color: var(--color-text-dark);">Cargo o Función *</label>
                            <input type="text" name="role" required placeholder="Ej: Dueño / Administrador" style="padding: 0.75rem; border-radius: var(--border-radius); border: 1px solid var(--color-border); font-size: 0.9rem;">
                        </div>

                        <div class="form-group">
                            <label style="font-weight: 700; font-size: 0.85rem; color: var(--color-text-dark);">Teléfono de Contacto *</label>
                            <input type="tel" name="phone" required placeholder="Ej: +56 9 1234 5678" style="padding: 0.75rem; border-radius: var(--border-radius); border: 1px solid var(--color-border); font-size: 0.9rem;">
                        </div>

                        <div class="form-group">
                            <label style="font-weight: 700; font-size: 0.85rem; color: var(--color-text-dark);">Correo Electrónico *</label>
                            <input type="email" name="email" required placeholder="Ej: juan@empresa.cl" style="padding: 0.75rem; border-radius: var(--border-radius); border: 1px solid var(--color-border); font-size: 0.9rem;">
                        </div>

                        <div class="form-group">
                            <label style="font-weight: 700; font-size: 0.85rem; color: var(--color-text-dark);">Comuna / Región *</label>
                            <select name="region" required style="padding: 0.75rem; border-radius: var(--border-radius); border: 1px solid var(--color-border); font-size: 0.9rem; background: white;">
                                <option value="" disabled selected>Selecciona tu comuna</option>
                                <?php foreach ($coverage_zones as $zone): ?>
                                    <option value="<?php echo htmlspecialchars($zone); ?>"><?php echo htmlspecialchars($zone); ?></option>
                                <?php endforeach; ?>
                                <option value="Otra Comuna O'Higgins">Otra Comuna (Región de O'Higgins)</option>
                                <option value="Fuera de Región">Fuera de Región de O'Higgins</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label style="font-weight: 700; font-size: 0.85rem; color: var(--color-text-dark);">Comentarios / Marcas de Interés</label>
                            <textarea name="comments" rows="3" placeholder="Ej: Me interesa distribuir azúcar Iansa y quesos Angelmo..." style="padding: 0.75rem; border-radius: var(--border-radius); border: 1px solid var(--color-border); font-size: 0.9rem; font-family: inherit; resize: none;"></textarea>
                        </div>

                        <button type="submit" class="btn btn-secondary" style="width: 100%; justify-content: center; padding: 0.85rem; font-weight: 700; font-size: 1rem; border: 0; cursor: pointer; border-radius: var(--border-radius);">
                            Solicitar Contacto Comercial
                        </button>
                    </form>
                    
                    <div id="formFeedback" style="margin-top: 1.5rem; text-align: center; font-weight: 600; display: none;"></div>
                </div>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container footer-grid">
            <div class="footer-brand">
                <a href="index.html" class="logo logo-white" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; margin-bottom: 0.5rem;">
                    <?php if (!empty($logo_url)): ?>
                        <img src="<?php echo htmlspecialchars($logo_url); ?>?v=2" alt="La Canasta Logo" style="height: 38px; max-width: 130px; object-fit: contain; background: white; padding: 4px; border-radius: 4px; box-shadow: var(--shadow-sm);">
                    <?php endif; ?>
                    <div style="display: flex; flex-direction: column; justify-content: center;">
                        <span class="logo-bold" style="line-height: 1.1; font-weight: 800; color: white; font-family: var(--font-heading); font-size: 1.4rem;">La Canasta</span>
                        <span class="logo-light" style="font-size: 0.7rem; color: rgba(255,255,255,0.6); font-weight: 700; margin-top: 2px;">Distribuidora</span>
                    </div>
                </a>
                <p style="margin-top: 1rem; font-size: 0.9rem; opacity: 0.7; line-height: 1.6;">
                    Operador comercial y logístico B2B. Conectando marcas líderes con el canal tradicional y comercio local de la Región de O'Higgins.
                </p>
            </div>
            
            <div class="footer-links">
                <h3>Navegación</h3>
                <ul>
                    <li><a href="index.html">Inicio</a></li>
                    <li><a href="sobre-nosotros.php">Sobre Nosotros</a></li>
                    <li><a href="index.html#marcas">Nuestras Marcas</a></li>
                    <li><a href="index.html#catalogo">Catálogo</a></li>
                </ul>
            </div>
            
            <div class="footer-contact" style="display: flex; flex-direction: column; gap: 0.85rem;">
                <h3 style="margin-bottom: 0.25rem;">Contacto Corporativo</h3>
                <div style="display: flex; flex-direction: column; gap: 0.15rem;">
                    <span style="font-size: 0.75rem; text-transform: uppercase; color: #9ca3af; font-weight: 700; display: block;">Correo Ventas</span>
                    <a href="mailto:contacto@lacanastadistribuidora.cl" style="color: white; text-decoration: none; font-size: 0.9rem; opacity: 0.85;">contacto@lacanastadistribuidora.cl</a>
                </div>
                <div style="display: flex; flex-direction: column; gap: 0.15rem;">
                    <span style="font-size: 0.75rem; text-transform: uppercase; color: #9ca3af; font-weight: 700; display: block;">Correo Despacho</span>
                    <a href="mailto:despacho@lacanastadistribuidora.cl" style="color: white; text-decoration: none; font-size: 0.9rem; opacity: 0.85;">despacho@lacanastadistribuidora.cl</a>
                </div>
                <div style="display: flex; flex-direction: column; gap: 0.15rem;">
                    <span style="font-size: 0.75rem; text-transform: uppercase; color: #9ca3af; font-weight: 700; display: block;">Correo Gerencia</span>
                    <a href="mailto:gerencia@lacanastadistribuidora.cl" style="color: white; text-decoration: none; font-size: 0.9rem; opacity: 0.85;">gerencia@lacanastadistribuidora.cl</a>
                </div>
                <div style="display: flex; flex-direction: column; gap: 0.15rem;">
                    <span style="font-size: 0.75rem; text-transform: uppercase; color: #9ca3af; font-weight: 700; display: block;">WhatsApp Business</span>
                    <a href="https://wa.me/56942567472" target="_blank" rel="noopener" style="color: #25d366; text-decoration: none; font-size: 0.9rem; font-weight: 700;">+56 9 4256 7472</a>
                </div>
                <div style="display: flex; flex-direction: column; gap: 0.15rem;">
                    <span style="font-size: 0.75rem; text-transform: uppercase; color: #9ca3af; font-weight: 700; display: block;">Call Center</span>
                    <a href="tel:56942567472" style="color: white; text-decoration: none; font-size: 0.9rem; opacity: 0.85;">+56 9 4256 7472</a>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="container" style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <p>&copy; <?php echo date('Y'); ?> La Canasta Distribuidora. Todos los derechos reservados. | Desarrollado por <a href="https://bastiascid.github.io/portafolio" target="_blank" rel="noopener" style="color: var(--color-secondary-light); text-decoration: none; font-weight: bold;">Cristian Bastias Cid</a></p>
                <div style="display: flex; gap: 1.5rem;">
                    <a href="privacidad.html" style="color: rgba(255,255,255,0.6); font-size: 0.8rem; text-decoration: none;">Política de Privacidad</a>
                    <a href="terminos.html" style="color: rgba(255,255,255,0.6); font-size: 0.8rem; text-decoration: none;">Términos y Condiciones</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Floating WhatsApp Button (Conditional) -->
    <?php if ($whatsapp_enabled === '1'): ?>
        <a href="https://wa.me/<?php echo preg_replace('/[^\d]/', '', $whatsapp_number); ?>?text=<?php echo urlencode("Hola, me interesa ser cliente de La Canasta Distribuidora."); ?>" class="whatsapp-float-container" target="_blank" rel="noopener noreferrer" style="display: flex; position: fixed; bottom: 20px; right: 20px; z-index: 999; background-color: #25d366; width: 60px; height: 60px; border-radius: 50%; box-shadow: 0 4px 10px rgba(0,0,0,0.3); align-items: center; justify-content: center;">
            <svg viewBox="0 0 24 24" fill="white" width="34" height="34">
                <path d="M12.012 2c-5.506 0-9.989 4.478-9.99 9.984a9.96 9.96 0 001.37 5.028L2 22l5.13-1.346a9.92 9.92 0 004.882 1.28h.005c5.507 0 9.99-4.478 9.99-9.985C22.007 6.478 17.519 2 12.012 2zm6.786 14.182c-.279.78-1.619 1.431-2.235 1.503-.574.067-1.127.319-3.666-.688-3.245-1.287-5.328-4.577-5.49-4.793-.163-.215-1.302-1.724-1.302-3.29 0-1.565.814-2.336 1.103-2.642.279-.297.63-.372.842-.372.196 0 .393.003.565.011.182.008.423-.072.665.507.25.597.85 2.062.923 2.211.072.15.121.324.021.522-.099.198-.15.323-.298.497-.149.174-.312.389-.446.522-.15.15-.307.314-.132.613.176.3.782 1.284 1.678 2.079.914.811 1.684 1.062 1.984 1.187.3.125.474.104.65-.099.177-.202.756-.877.958-1.176.203-.3.407-.251.686-.149.279.102 1.768.834 2.073.987.305.153.509.227.583.352.073.125.073.725-.206 1.507z"/>
            </svg>
        </a>
    <?php endif; ?>

    <!-- AJAX form submission -->
    <script>
        document.getElementById('hazteClienteForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const feedbackDiv = document.getElementById('formFeedback');
            const submitBtn = form.querySelector('button[type="submit"]');
            
            // Front-end email validation
            const email = form.querySelector('[name="email"]').value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                feedbackDiv.style.display = 'block';
                feedbackDiv.style.color = '#ef4444';
                feedbackDiv.textContent = 'Por favor, ingresa un correo electrónico válido.';
                return;
            }
            
            const payload = {
                name: form.querySelector('[name="name"]').value.trim(),
                company: form.querySelector('[name="company"]').value.trim(),
                role: form.querySelector('[name="role"]').value.trim(),
                phone: form.querySelector('[name="phone"]').value.trim(),
                email: email,
                region: form.querySelector('[name="region"]').value,
                comments: form.querySelector('[name="comments"]').value.trim(),
                origin: form.querySelector('[name="origin"]').value
            };
            
            // UI States
            feedbackDiv.style.display = 'block';
            feedbackDiv.style.color = 'var(--color-primary)';
            feedbackDiv.textContent = 'Enviando solicitud comercial...';
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.7';
            
            fetch('api/leads.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    feedbackDiv.style.color = '#2e7d32'; // Green
                    feedbackDiv.textContent = res.message;
                    form.reset();
                } else {
                    feedbackDiv.style.color = '#ef4444'; // Red
                    feedbackDiv.textContent = 'Error: ' + res.message;
                    submitBtn.disabled = false;
                    submitBtn.style.opacity = '1';
                }
            })
            .catch(err => {
                console.error("Error submitting form:", err);
                feedbackDiv.style.color = '#ef4444';
                feedbackDiv.textContent = 'Hubo un problema al enviar tu solicitud. Inténtalo de nuevo.';
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
            });
        });
    </script>

</body>
</html>
