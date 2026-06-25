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
    
    $logo_url = isset($settings['logo_url']) ? $settings['logo_url'] : 'assets/canasta-logo.png';
    $whatsapp_enabled = isset($settings['whatsapp_enabled']) ? $settings['whatsapp_enabled'] : '0';
    $whatsapp_number = isset($settings['whatsapp_number']) ? $settings['whatsapp_number'] : '+56 9 4256 7472';

} catch (Exception $e) {
    // Fallback variables
    $coverage_zones = ['Rancagua', 'Machalí', 'Graneros', 'Mostazal', 'San Fernando', 'Santa Cruz'];
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
    <title>Sobre Nosotros - La Canasta Distribuidora</title>
    <meta name="description" content="Conoce la historia, misión, visión e infraestructura logística de La Canasta Distribuidora, operador comercial líder en el canal tradicional.">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="styles.css">
    
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
                <span style="margin-right: 1.5rem;">📧 <a href="mailto:contacto@lacanastadistribuidora.cl" style="color: white; text-decoration: none;">contacto@lacanastadistribuidora.cl</a></span>
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
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem;">
                <div class="pillar-card">
                    <h4>Experiencia Logística</h4>
                    <p>Contamos con bodegas equipadas y camiones de reparto propios para garantizar entregas exactas y puntuales.</p>
                </div>
                <div class="pillar-card">
                    <h4>Cobertura Territorial</h4>
                    <p>Conectamos comunas rurales y urbanas de la provincia, llevando marcas representadas donde otros no llegan.</p>
                </div>
                <div class="pillar-card">
                    <h4>Desarrollo del Almacenero</h4>
                    <p>Facilitamos el acceso a material promocional, catálogos digitales e información comercial para aumentar tus ventas.</p>
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

</body>
</html>
