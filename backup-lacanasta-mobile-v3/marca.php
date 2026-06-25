<?php
// marca.php
// Dynamic brand landing page for B2B portal

require_once 'api/db.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if (empty($slug)) {
    header("Location: index.html");
    exit;
}

try {
    // 1. Fetch Brand details
    $stmt = $pdo->prepare("SELECT * FROM brands WHERE slug = ? AND status = 'Activa' LIMIT 1");
    $stmt->execute([$slug]);
    $brand = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$brand) {
        // Redirect to main page if brand not found or inactive
        header("Location: index.html");
        exit;
    }

    // 2. Fetch Products associated with this brand
    $stmt_p = $pdo->prepare("SELECT * FROM products WHERE brand_id = ? ORDER BY featured DESC, sort_order ASC, name ASC");
    $stmt_p->execute([$brand['id']]);
    $products = $stmt_p->fetchAll(PDO::FETCH_ASSOC);

    // 3. Fetch Settings (logo, whatsapp)
    $stmt_s = $pdo->query("SELECT `key`, `value` FROM settings");
    $settings = $stmt_s->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $logo_url = isset($settings['logo_url']) ? $settings['logo_url'] : 'assets/canasta-logo.png';
    $whatsapp_enabled = isset($settings['whatsapp_enabled']) ? $settings['whatsapp_enabled'] : '0';
    $whatsapp_number = isset($settings['whatsapp_number']) ? $settings['whatsapp_number'] : '+56 9 4256 7472';

} catch (Exception $e) {
    // Fallback if db error
    header("Location: index.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distribución B2B <?php echo htmlspecialchars($brand['name']); ?> - La Canasta Distribuidora</title>
    <meta name="description" content="Distribuidor mayorista oficial de <?php echo htmlspecialchars($brand['name']); ?>. Abastecemos almacenes, minimarkets y comercio detallista en la Región de O'Higgins.">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        .brand-hero {
            background: linear-gradient(135deg, var(--color-primary) 0%, #153c73 100%);
            color: white;
            padding: 5rem 0 4rem 0;
            position: relative;
            overflow: hidden;
        }
        .brand-hero-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
        }
        .brand-hero-logo {
            background: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            max-width: 250px;
            margin-bottom: 1.5rem;
        }
        .brand-hero-logo img {
            max-height: 120px;
            max-width: 100%;
            object-fit: contain;
        }
        .brand-hero-banner {
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            height: 350px;
            border: 4px solid rgba(255, 255, 255, 0.1);
        }
        .brand-hero-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .brand-history-section {
            padding: 5rem 0;
            background-color: var(--color-bg-white);
        }
        .history-grid {
            display: grid;
            grid-template-columns: 3fr 2fr;
            gap: 4rem;
            align-items: flex-start;
        }
        .brand-products-section {
            padding: 5rem 0;
            background-color: var(--color-bg-light);
            border-top: 1px solid var(--color-border);
            border-bottom: 1px solid var(--color-border);
        }
        .brand-contact-section {
            padding: 5rem 0;
            background-color: var(--color-bg-white);
        }
        .brand-contact-card {
            background: var(--color-bg-light);
            border: 1px solid var(--color-border);
            border-radius: var(--border-radius);
            padding: 3rem;
            box-shadow: var(--shadow-md);
        }
        @media (max-width: 991px) {
            .brand-hero-content, .history-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            .brand-hero-banner {
                height: 250px;
            }
        }
    </style>
</head>
<body>

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
                <li><a href="index.html#nosotros">¿Por qué nosotros?</a></li>
                <li><a href="index.html#marcas">Marcas</a></li>
                <li><a href="index.html#catalogo">Catálogo</a></li>
                <li><a href="hazte-cliente.php" class="btn-secondary text-white btn-sm" style="padding: 0.5rem 1rem; text-decoration: none; display: inline-flex; align-items: center; border-radius: 4px;">Quiero ser Cliente</a></li>
            </ul>
        </div>
    </nav>

    <!-- Brand Hero Section -->
    <section class="brand-hero">
        <div class="container brand-hero-content">
            <div>
                <div class="brand-hero-logo">
                    <img src="<?php echo htmlspecialchars($brand['logo_url']); ?>" alt="<?php echo htmlspecialchars($brand['name']); ?> Logo" onerror="this.src='https://placehold.co/250x150/0f2c59/ffffff?text=<?php echo urlencode($brand['name']); ?>'">
                </div>
                <h1 style="font-size: 2.75rem; font-weight: 800; line-height: 1.2; margin-bottom: 1rem; color: white;">
                    Distribuidor Oficial <span style="color: var(--color-secondary);"><?php echo htmlspecialchars($brand['name']); ?></span>
                </h1>
                <p style="font-size: 1.15rem; opacity: 0.9; margin-bottom: 2rem; line-height: 1.6;">
                    <?php echo htmlspecialchars($brand['description']); ?>
                </p>
                <div class="btn-group">
                    <a href="#contacto-marca" class="btn btn-secondary">Solicitar Distribución</a>
                    <a href="#productos-marca" class="btn btn-primary" style="background: rgba(255,255,255,0.15); border-color: rgba(255,255,255,0.25);">Ver Catálogo</a>
                </div>
            </div>
            <div class="brand-hero-banner">
                <img src="<?php echo htmlspecialchars($brand['image_url']); ?>" alt="<?php echo htmlspecialchars($brand['name']); ?> Destacado" onerror="this.src='https://images.unsplash.com/photo-1542838132-92c53300491e?w=800&auto=format&fit=crop&q=60'">
            </div>
        </div>
    </section>

    <!-- Brand History Section -->
    <section class="brand-history-section">
        <div class="container">
            <div class="history-grid">
                <div>
                    <h2 style="font-size: 2rem; font-weight: 700; color: var(--color-primary); margin-bottom: 1.5rem;">
                        Nuestra Trayectoria con <span class="highlight"><?php echo htmlspecialchars($brand['name']); ?></span>
                    </h2>
                    <p style="font-size: 1.1rem; line-height: 1.8; color: var(--color-text-dark); margin-bottom: 1.5rem;">
                        <?php echo nl2br(htmlspecialchars($brand['history'] ? $brand['history'] : 'Operamos como distribuidor estratégico de esta marca para el canal tradicional, entregando productos de alta rotación directo a comercios locales.')); ?>
                    </p>
                </div>
                <div style="background-color: var(--color-bg-light); padding: 2rem; border-radius: var(--border-radius); border-left: 5px solid var(--color-secondary);">
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--color-primary); margin-bottom: 1rem;">Propuesta Comercial B2B</h3>
                    <ul style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 0.75rem;">
                        <li style="display: flex; gap: 0.5rem; align-items: center;">
                            <span style="color: var(--color-secondary); font-weight: bold;">✓</span> Abastecimiento continuo garantizado.
                        </li>
                        <li style="display: flex; gap: 0.5rem; align-items: center;">
                            <span style="color: var(--color-secondary); font-weight: bold;">✓</span> Formatos mayoristas optimizados para el canal retail.
                        </li>
                        <li style="display: flex; gap: 0.5rem; align-items: center;">
                            <span style="color: var(--color-secondary); font-weight: bold;">✓</span> Material promocional y apoyo comercial en terreno.
                        </li>
                        <li style="display: flex; gap: 0.5rem; align-items: center;">
                            <span style="color: var(--color-secondary); font-weight: bold;">✓</span> Facturación electrónica y logística adaptada.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Brand Products Section -->
    <section id="productos-marca" class="brand-products-section">
        <div class="container">
            <div class="section-header">
                <h2>Productos <span class="highlight">Destacados</span> de <?php echo htmlspecialchars($brand['name']); ?></h2>
                <p class="subtitle">Explora el portafolio disponible para distribución en tu zona.</p>
            </div>
            
            <?php if (count($products) > 0): ?>
                <div class="offers-grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
                    <?php foreach ($products as $p): ?>
                        <div class="offer-card">
                            <?php if ($p['featured'] == 1): ?>
                                <span class="offer-badge" style="background-color: var(--color-secondary);">Destacado</span>
                            <?php endif; ?>
                            <div class="offer-img-wrapper" style="height: 180px;">
                                <img src="<?php echo htmlspecialchars($p['image_url']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" onerror="this.src='https://placehold.co/300x200/0f2c59/ffffff?text=Producto'">
                            </div>
                            <div class="offer-details" style="padding: 1.25rem;">
                                <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--color-secondary); margin-bottom: 0.25rem; display: block;"><?php echo htmlspecialchars($p['category']); ?></span>
                                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--color-primary); margin: 0 0 0.5rem 0;"><?php echo htmlspecialchars($p['name']); ?></h3>
                                <p style="font-size: 0.85rem; color: var(--color-text-muted); line-height: 1.4; flex-grow: 1; margin: 0 0 1rem 0;"><?php echo htmlspecialchars($p['description']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center" style="color: var(--color-text-muted);">No hay productos registrados para esta marca en este momento.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Brand Contact Section -->
    <section id="contacto-marca" class="brand-contact-section">
        <div class="container">
            <div class="section-header">
                <h2>Alianza <span class="highlight">Comercial</span></h2>
                <p class="subtitle">Registra tu comercio para comenzar a distribuir <?php echo htmlspecialchars($brand['name']); ?>.</p>
            </div>
            
            <div style="max-width: 800px; margin: 0 auto;">
                <div class="brand-contact-card">
                    <h3 style="font-size: 1.5rem; font-weight: 700; color: var(--color-primary); margin-bottom: 1.5rem; text-align: center;">Solicitud de Distribución</h3>
                    
                    <form id="brandLeadForm">
                        <input type="hidden" name="comments_prefix" value="Interés en marca: <?php echo htmlspecialchars($brand['name']); ?>. ">
                        
                        <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                            <div class="form-group">
                                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.25rem; color: var(--color-text-dark);">Nombre Completo *</label>
                                <input type="text" name="name" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-border); border-radius: var(--border-radius); font-size: 0.9rem;">
                            </div>
                            <div class="form-group">
                                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.25rem; color: var(--color-text-dark);">Nombre de la Empresa *</label>
                                <input type="text" name="company" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-border); border-radius: var(--border-radius); font-size: 0.9rem;">
                            </div>
                        </div>

                        <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                            <div class="form-group">
                                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.25rem; color: var(--color-text-dark);">Cargo / Puesto *</label>
                                <input type="text" name="role" placeholder="Ej. Dueño, Comprador" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-border); border-radius: var(--border-radius); font-size: 0.9rem;">
                            </div>
                            <div class="form-group">
                                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.25rem; color: var(--color-text-dark);">Teléfono de Contacto *</label>
                                <input type="tel" name="phone" placeholder="Ej. +56912345678" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-border); border-radius: var(--border-radius); font-size: 0.9rem;">
                            </div>
                        </div>

                        <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                            <div class="form-group">
                                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.25rem; color: var(--color-text-dark);">Correo Electrónico *</label>
                                <input type="email" name="email" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-border); border-radius: var(--border-radius); font-size: 0.9rem;">
                            </div>
                            <div class="form-group">
                                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.25rem; color: var(--color-text-dark);">Comuna / Región *</label>
                                <select name="region" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-border); border-radius: var(--border-radius); font-size: 0.9rem; background: white;">
                                    <option value="">Seleccione una zona</option>
                                    <option value="Rancagua">Rancagua</option>
                                    <option value="Machalí">Machalí</option>
                                    <option value="Graneros">Graneros</option>
                                    <option value="Mostazal">Mostazal</option>
                                    <option value="San Fernando">San Fernando</option>
                                    <option value="Santa Cruz">Santa Cruz</option>
                                    <option value="Otra Comuna (Región de O'Higgins)">Otra Comuna (Región de O'Higgins)</option>
                                    <option value="Fuera de la Región">Fuera de la Región de O'Higgins</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 1.5rem;">
                            <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.25rem; color: var(--color-text-dark);">Comentarios / Requerimientos Específicos</label>
                            <textarea name="comments" rows="4" placeholder="Indíquenos qué productos le interesan o detalles de su local..." style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-border); border-radius: var(--border-radius); font-size: 0.9rem; font-family: inherit; resize: vertical;"></textarea>
                        </div>

                        <div style="text-align: center;">
                            <button type="submit" class="btn btn-secondary" style="padding: 0.75rem 2rem; width: 100%; max-width: 300px; font-weight: 700;">Enviar Formulario B2B</button>
                        </div>
                    </form>
                    <div id="formResponse" style="margin-top: 1.5rem; text-align: center; font-weight: 600; display: none;"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container footer-grid">
            <div class="footer-brand">
                <a href="index.html" class="logo logo-white" id="footerLogoTextNode">
                    <?php if ($logo_url !== 'assets/canasta-logo.png'): ?>
                        <img src="<?php echo htmlspecialchars($logo_url); ?>" alt="La Canasta Logo" style="height: 40px; max-width: 150px; object-fit: contain;">
                    <?php else: ?>
                        <span class="logo-bold">La Canasta</span><span class="logo-light">Distribuidora</span>
                    <?php endif; ?>
                </a>
                <p style="margin-top: 1rem; font-size: 0.9rem; opacity: 0.7; line-height: 1.6;">
                    Operador comercial y logístico B2B. Conectando marcas líderes con el canal tradicional y comercio local de la Región de O'Higgins.
                </p>
            </div>
            
            <div class="footer-links">
                <h3>Navegación</h3>
                <ul>
                    <li><a href="index.html">Inicio</a></li>
                    <li><a href="index.html#nosotros">¿Por qué nosotros?</a></li>
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
            <div class="container">
                <p>&copy; <?php echo date('Y'); ?> La Canasta Distribuidora. Todos los derechos reservados. | Desarrollado por <a href="https://bastiascid.github.io/portafolio" target="_blank" rel="noopener" style="color: var(--color-secondary-light); text-decoration: none; font-weight: bold;">Cristian Bastias Cid</a></p>
            </div>
        </div>
    </footer>

    <!-- Floating WhatsApp Button (Conditional) -->
    <?php if ($whatsapp_enabled === '1'): ?>
        <a href="https://wa.me/<?php echo preg_replace('/[^\d]/', '', $whatsapp_number); ?>?text=<?php echo urlencode("Hola, me interesa solicitar información comercial sobre productos de " . $brand['name']); ?>" class="whatsapp-float-container whatsapp-only-node" target="_blank" rel="noopener noreferrer" style="display: flex; position: fixed; bottom: 20px; right: 20px; z-index: 999; background-color: #25d366; width: 60px; height: 60px; border-radius: 50%; box-shadow: 0 4px 10px rgba(0,0,0,0.3); align-items: center; justify-content: center;">
            <svg viewBox="0 0 24 24" fill="white" width="34" height="34">
                <path d="M12.012 2c-5.506 0-9.989 4.478-9.99 9.984a9.96 9.96 0 001.37 5.028L2 22l5.13-1.346a9.92 9.92 0 004.882 1.28h.005c5.507 0 9.99-4.478 9.99-9.985C22.007 6.478 17.519 2 12.012 2zm6.786 14.182c-.279.78-1.619 1.431-2.235 1.503-.574.067-1.127.319-3.666-.688-3.245-1.287-5.328-4.577-5.49-4.793-.163-.215-1.302-1.724-1.302-3.29 0-1.565.814-2.336 1.103-2.642.279-.297.63-.372.842-.372.196 0 .393.003.565.011.182.008.423-.072.665.507.25.597.85 2.062.923 2.211.072.15.121.324.021.522-.099.198-.15.323-.298.497-.149.174-.312.389-.446.522-.15.15-.307.314-.132.613.176.3.782 1.284 1.678 2.079.914.811 1.684 1.062 1.984 1.187.3.125.474.104.65-.099.177-.202.756-.877.958-1.176.203-.3.407-.251.686-.149.279.102 1.768.834 2.073.987.305.153.509.227.583.352.073.125.073.725-.206 1.507z"/>
            </svg>
        </a>
    <?php endif; ?>

    <!-- AJAX Lead submission script for the brand landing page -->
    <script>
        document.getElementById('brandLeadForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const responseDiv = document.getElementById('formResponse');
            
            // Collect Form Data
            const prefix = form.querySelector('[name="comments_prefix"]').value;
            const rawComments = form.querySelector('[name="comments"]').value;
            
            const payload = {
                name: form.querySelector('[name="name"]').value,
                company: form.querySelector('[name="company"]').value,
                role: form.querySelector('[name="role"]').value,
                phone: form.querySelector('[name="phone"]').value,
                email: form.querySelector('[name="email"]').value,
                region: form.querySelector('[name="region"]').value,
                comments: prefix + rawComments
            };
            
            // Submit to API
            responseDiv.style.display = 'block';
            responseDiv.style.color = 'var(--color-primary)';
            responseDiv.textContent = 'Enviando tu solicitud comercial...';
            
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
                    responseDiv.style.color = '#2e7d32'; // green
                    responseDiv.textContent = res.message;
                    form.reset();
                } else {
                    responseDiv.style.color = 'var(--color-secondary)'; // red
                    responseDiv.textContent = 'Error: ' + res.message;
                }
            })
            .catch(err => {
                console.error(err);
                responseDiv.style.color = 'var(--color-secondary)';
                responseDiv.textContent = 'Ocurrió un error al enviar tus datos. Por favor, vuelve a intentarlo.';
            });
        });
    </script>
</body>
</html>
