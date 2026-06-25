<?php
// sobre-nosotros.php
// Corporate information page

require_once 'api/db.php';

try {
    // 1. Fetch Coverage zones
    $stmt_c = $pdo->query("SELECT name FROM coverage ORDER BY sort_order ASC, name ASC");
    $coverage_zones = $stmt_c->fetchAll(PDO::FETCH_COLUMN);

    // 2. Fetch Settings
    $stmt_s = $pdo->query("SELECT `key`, `value` FROM settings");
    $settings = $stmt_s->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $logo_url = isset($settings['logo_url']) ? $settings['logo_url'] : 'assets/canasta-logo.webp';
    $whatsapp_enabled = isset($settings['whatsapp_enabled']) ? $settings['whatsapp_enabled'] : '0';
    $whatsapp_number = isset($settings['whatsapp_number']) ? $settings['whatsapp_number'] : '+56 9 4256 7472';

} catch (Exception $e) {
    // Fallback variables
    $coverage_zones = ['Rancagua', 'Machalí', 'Graneros', 'Mostazal', 'San Fernando', 'Santa Cruz'];
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
    <title>Sobre Nosotros - La Canasta Distribuidora</title>
    <meta name="description" content="Conoce la historia, misión, visión e infraestructura logística de La Canasta Distribuidora, operador comercial líder en el canal tradicional.">
    <link rel="shortcut icon" href="favicon.png" type="image/png">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://www.lacanastadistribuidora.cl/sobre-nosotros.php">
    <meta property="og:title" content="Sobre Nosotros - La Canasta Distribuidora">
    <meta property="og:description" content="Conoce la historia, misión, visión e infraestructura de La Canasta. Distribución mayorista en la Sexta Región.">
    <meta property="og:image" content="https://www.lacanastadistribuidora.cl/assets/canasta-logo.webp">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="styles.css?v=17">
    
    <!-- Analítica & Tracking -->
    <!-- Google Analytics 4 -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-XXXXXXXXXX');
    </script>
    
    <style>
        .page-header-banner {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);
            color: white;
            padding: 6rem 0 3.5rem 0;
            text-align: center;
        }
        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            padding: 5rem 0;
        }
        .values-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-top: 3rem;
            padding: 4rem 0;
            border-top: 1px solid var(--color-border);
            border-bottom: 1px solid var(--color-border);
        }
        .value-card {
            background: var(--color-bg-light);
            padding: 2rem;
            border-radius: var(--border-radius);
            border-top: 4px solid var(--color-secondary);
        }
        .value-card h3 {
            font-size: 1.25rem;
            color: var(--color-primary);
            margin: 0 0 0.5rem 0;
            font-weight: 700;
        }
        .value-card p {
            margin: 0;
            font-size: 0.95rem;
            color: var(--color-text-muted);
            line-height: 1.6;
        }
        .pillar-card {
            background: white;
            border: 1px solid var(--color-border);
            border-radius: var(--border-radius);
            padding: 2rem;
            text-align: center;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
        }
        .pillar-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }
        .pillar-card h4 {
            font-size: 1.15rem;
            color: var(--color-primary);
            margin: 0 0 0.5rem 0;
            font-weight: 700;
        }
        .pillar-card p {
            margin: 0;
            font-size: 0.85rem;
            color: var(--color-text-muted);
            line-height: 1.5;
        }
        @media (max-width: 768px) {
            .about-grid, .values-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
        }
    </style>
</head>
<body>

    <!-- Upper Utility Bar -->
    <div style="background-color: var(--color-primary); color: white; font-size: 0.8rem; padding: 0.5rem 0; border-bottom: 1px solid rgba(255,255,255,0.1);">
        <div class="container" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
            <div>
                <span style="margin-right: 1.5rem;">📧 <a href="mailto:contacto@comercializadoralacanasta.cl" style="color: white; text-decoration: none;">contacto@comercializadoralacanasta.cl</a></span>
                <span>📞 Call Center: +56 9 4256 7472</span>
            </div>
            <div style="display: flex; gap: 1.5rem;">
                <a href="sobre-nosotros.php" style="color: var(--color-secondary); text-decoration: none; font-weight: 700;">Sobre Nosotros</a>
                <a href="hazte-cliente.php" style="color: white; text-decoration: none; font-weight: 600;">Hazte Cliente</a>
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
                <span style="font-size: 0.6rem; color: var(--color-text-muted); font-weight: 700; line-height: 1.1; margin-top: -2px;">Comercializadora y Distribuidora</span>
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
            <h1 style="font-size: 2.75rem; font-weight: 800; margin: 0 0 0.5rem 0;">Sobre Nosotros</h1>
            <p style="font-size: 1.15rem; color: white; opacity: 0.9; max-width: 700px; margin: 0 auto; line-height: 1.6;">
                Operador comercial y logístico líder, especializado en la distribución e impulso del canal tradicional.
            </p>
        </div>
    </section>

    <!-- History and Presentation Section -->
    <main class="container">
        <div class="about-grid">
            <div>
                <h2 style="font-size: 1.8rem; font-weight: 700; color: var(--color-primary); margin-bottom: 1.5rem;">Nuestra Historia</h2>
                <p style="font-size: 1.05rem; color: var(--color-text-dark); line-height: 1.8; margin-bottom: 1.5rem;">
                    Nacimos como un proyecto enfocado en resolver las complejidades de distribución logística que enfrentaban los pequeños comerciantes, almacenes y minimarkets de barrio en la Región de O'Higgins.
                </p>
                <p style="font-size: 1.05rem; color: var(--color-text-dark); line-height: 1.8;">
                    A lo largo de los años, nos hemos consolidado como un operador comercial integral de confianza. No solo suministramos abarrotes esenciales de marcas líderes nacionales, sino que también aportamos valor estratégico a través de un servicio de despacho rápido, precios mayoristas reales y ejecutivos comerciales en terreno.
                </p>
            </div>
            <div style="border-radius: var(--border-radius); overflow: hidden; box-shadow: var(--shadow-lg); height: 350px;">
                <img src="https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=800&auto=format&fit=crop&q=60" alt="La Canasta Operaciones" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
        </div>

        <!-- Mission & Vision -->
        <div class="values-grid">
            <div class="value-card">
                <h3>Nuestra Misión</h3>
                <p>
                    Fortalecer y rentabilizar el canal tradicional de distribución conectando a marcas líderes con locales de barrio y comercios minoristas, asegurando un abastecimiento continuo, ágil y de excelencia operacional.
                </p>
            </div>
            <div class="value-card">
                <h3>Nuestra Visión</h3>
                <p>
                    Ser reconocidos para el 2030 como el principal operador comercial y logístico B2B de la Región de O'Higgins, destacando por la digitalización de nuestros procesos, la calidad del servicio al cliente y el apoyo al crecimiento del comercio local.
                </p>
            </div>
        </div>

        <!-- Values / Value Proposition pillars -->
        <div style="padding: 5rem 0;">
            <div class="section-header" style="text-align: center; margin-bottom: 3rem;">
                <h2 style="font-size: 1.8rem; font-weight: 700; color: var(--color-primary);">Propuesta de Valor Comercial</h2>
                <p style="color: var(--color-text-muted);">Los pilares que sostienen nuestra operación diaria.</p>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 2rem;">
                <div class="pillar-card">
                    <h4>Experiencia Logística</h4>
                    <p>Contamos con bodegas equipadas y camiones de reparto propios para garantizar entregas exactas y puntuales.</p>
                </div>
                <div class="pillar-card">
                    <h4>Cobertura Territorial</h4>
                    <p>Conectamos comunas rurales y urbanas de la provincia, llevando marcas representadas donde otros no llegan.</p>
                </div>
                <div class="pillar-card">
                    <h4>Fuerza de Ventas en Terreno</h4>
                    <p>Contamos con una activa <strong>fuerza de ventas en terreno</strong> para el desarrollo de los negocios, asesorando y visitando periódicamente a cada comercio.</p>
                </div>
                <div class="pillar-card">
                    <h4>Contacto Permanente</h4>
                    <p>Mantenemos un <strong>contacto permanente</strong> y directo con nuestros clientes, asegurando respuestas ágiles y soporte continuo para potenciar sus ventas.</p>
                </div>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer>
        <div class="container footer-content">
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
                <h4 style="margin-bottom: 0.25rem;">Contacto Corporativo</h4>
                <div style="display: flex; flex-direction: column; gap: 0.15rem;">
                    <span style="font-size: 0.75rem; text-transform: uppercase; color: #9ca3af; font-weight: 700; display: block;">Correo de Contacto Único</span>
                    <a href="mailto:contacto@comercializadoralacanasta.cl" style="color: white; text-decoration: none; font-size: 0.9rem;">contacto@comercializadoralacanasta.cl</a>
                </div>
                <?php if ($whatsapp_enabled === '1'): ?>
                <div style="display: flex; flex-direction: column; gap: 0.15rem;">
                    <span style="font-size: 0.75rem; text-transform: uppercase; color: #9ca3af; font-weight: 700; display: block;">WhatsApp Business</span>
                    <a href="https://wa.me/<?php echo preg_replace('/[^\d]/', '', $whatsapp_number); ?>?text=<?php echo urlencode("Hola, me interesa conocer más sobre los productos y servicios de La Canasta."); ?>" target="_blank" rel="noopener noreferrer" style="color: #25d366; text-decoration: none; font-size: 0.9rem; font-weight: 700;"><?php echo htmlspecialchars($whatsapp_number); ?></a>
                </div>
                <?php endif; ?>
                <div style="display: flex; flex-direction: column; gap: 0.15rem;">
                    <span style="font-size: 0.75rem; text-transform: uppercase; color: #9ca3af; font-weight: 700; display: block;">Call Center</span>
                    <a href="tel:56942567472" style="color: white; text-decoration: none; font-size: 0.9rem;">+56 9 4256 7472</a>
                </div>
                <div style="margin-top: 0.5rem;">
                    <a href="admin.html" style="color: #6b7280; font-size: 0.8rem; text-decoration: underline;">Acceso Admin</a>
                </div>
            </div>
            
            <div class="footer-social">
                <h4>Síguenos</h4>
                <div class="social-icons">
                    <a href="#" aria-label="Facebook"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg></a>
                    <a href="#" aria-label="Instagram"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg></a>
                </div>
                
                <div style="margin-top: 2rem;">
                    <h4 style="margin-bottom: 0.75rem; font-size: 1.1rem; color: white;">Navegación</h4>
                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.5rem;">
                        <li><a href="index.html" style="color: #9ca3af; text-decoration: none; font-size: 0.9rem; transition: var(--transition);">Inicio</a></li>
                        <li><a href="sobre-nosotros.php" style="color: #9ca3af; text-decoration: none; font-size: 0.9rem; transition: var(--transition);">Sobre Nosotros</a></li>
                        <li><a href="index.html#marcas" style="color: #9ca3af; text-decoration: none; font-size: 0.9rem; transition: var(--transition);">Nuestras Marcas</a></li>
                        <li><a href="index.html#catalogo" style="color: #9ca3af; text-decoration: none; font-size: 0.9rem; transition: var(--transition);">Catálogo</a></li>
                        <li><a href="reclamos.php" style="color: #9ca3af; text-decoration: none; font-size: 0.9rem; transition: var(--transition);">Formulario de Reclamos</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="container" style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 1rem; width: 100%;">
                <p>&copy; <?php echo date('Y'); ?> La Canasta Comercializadora y Distribuidora. Todos los derechos reservados. | Desarrollado por <a href="https://bastiascid.github.io/portafolio" target="_blank" rel="noopener" style="color: var(--color-secondary-light); text-decoration: none; font-weight: bold;">Cristian Bastias Cid</a></p>
                <div style="display: flex; gap: 1.5rem;">
                    <a href="privacidad.html" style="color: #9ca3af; font-size: 0.8rem; text-decoration: none;">Política de Privacidad</a>
                    <a href="terminos.html" style="color: #9ca3af; font-size: 0.8rem; text-decoration: none;">Términos y Condiciones</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Floating WhatsApp Button -->
    <?php if ($whatsapp_enabled === '1'): ?>
        <div class="whatsapp-float-container" style="display: block;">
            <div class="whatsapp-tooltip">¿Cómo puedo ayudarte?</div>
            <a href="https://wa.me/<?php echo preg_replace('/[^\d]/', '', $whatsapp_number); ?>?text=<?php echo urlencode("Hola, me interesa conocer más sobre los productos y servicios de La Canasta Distribuidora."); ?>" class="whatsapp-float-btn" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp" title="Escríbenos">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
            </a>
        </div>
    <?php endif; ?>

</body>
</html>
