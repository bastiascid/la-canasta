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
    
    $logo_url = isset($settings['logo_url']) ? $settings['logo_url'] : 'assets/canasta-logo.webp';
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
    <title>Distribución B2B <?php echo htmlspecialchars($brand['name']); ?> - La Canasta Comercializadora y Distribuidora</title>
    <meta name="description" content="Distribuidor mayorista oficial de <?php echo htmlspecialchars($brand['name']); ?>. Abastecemos almacenes, minimarkets y comercio detallista en la Región de O'Higgins.">
    <link rel="shortcut icon" href="favicon.png" type="image/png">
    <link rel="canonical" href="https://www.lacanastacomercializadora.cl/marcas/<?php echo htmlspecialchars($brand['slug'], ENT_QUOTES, 'UTF-8'); ?>" />
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://www.lacanastacomercializadora.cl/marcas/<?php echo htmlspecialchars($brand['slug']); ?>">
    <meta property="og:title" content="Distribución B2B <?php echo htmlspecialchars($brand['name']); ?> - La Canasta Comercializadora y Distribuidora">
    <meta property="og:description" content="Distribuidor mayorista oficial de <?php echo htmlspecialchars($brand['name']); ?> para almacenes y minimarkets en la Sexta Región.">
    <meta property="og:image" content="https://www.lacanastacomercializadora.cl/<?php echo htmlspecialchars($brand['logo_url']); ?>">
    
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css?v=18">
    
    <!-- Google Analytics 4 -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-6TE0RMDKPX"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-6TE0RMDKPX');
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
      fbq('init', '4411421472402929');
      fbq('track', 'PageView');

      <?php if (isset($products) && count($products) > 0): ?>
      <?php 
        $product_ids = array_map(function($p) { return (int)$p['id']; }, $products);
        $ga4_items = array_map(function($p) use ($brand) {
            return [
                'item_id' => (string)$p['id'],
                'item_name' => $p['name'],
                'item_brand' => $brand['name']
            ];
        }, $products);
      ?>
      fbq('track', 'ViewContent', {
        content_ids: <?php echo json_encode($product_ids); ?>,
        content_name: <?php echo json_encode($brand['name']); ?>,
        content_type: 'product'
      });
      gtag('event', 'view_item', {
        items: <?php echo json_encode($ga4_items); ?>
      });
      <?php endif; ?>

      // Listener global para clics en enlaces estáticos de WhatsApp sin PII
      document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('a[href*="wa.me"], a[href*="whatsapp.com"]').forEach(function(el) {
          el.addEventListener('click', function() {
            if (typeof fbq === 'function') {
              fbq('trackCustom', 'WhatsAppClick', { origin: 'static_link' });
            }
            if (typeof gtag === 'function') {
              gtag('event', 'click_whatsapp', { origin: 'static_link' });
            }
          });
        });
      });
    </script>
    <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=4411421472402929&ev=PageView&noscript=1" /></noscript>
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
            <a href="index.html" class="logo" style="display: flex; flex-direction: column; align-items: center; text-decoration: none; text-align: center; gap: 0.15rem; margin-top: -5px; margin-bottom: -5px;">
                <?php if (!empty($logo_url)): ?>
                    <img src="<?php echo htmlspecialchars($logo_url); ?>?v=2" alt="La Canasta Logo" style="height: 50px; max-width: 150px; object-fit: contain;">
                <?php endif; ?>
                <span style="font-size: 0.6rem; color: var(--color-text-muted); font-weight: 700; line-height: 1.1; margin-top: 3px;">Comercializadora y<br>Distribuidora</span>
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
                <div style="background-color: var(--color-bg-light); padding: 2rem; border-radius: var(--border-radius); border: 1px solid var(--color-border);">
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--color-primary); margin-bottom: 1rem;">Propuesta Comercial B2B</h3>
                    <ul style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 0.75rem;">
                        <li style="display: flex; gap: 0.5rem; align-items: center;">
                            <span style="color: var(--color-secondary); font-weight: bold;">✓</span> Abastecimiento continuo garantizado.
                        </li>
                        <li style="display: flex; gap: 0.5rem; align-items: center;">
                            <span style="color: var(--color-secondary); font-weight: bold;">✓</span> Formatos mayoristas optimizados para el canal retail.
                        </li>
                        <li style="display: flex; gap: 0.5rem; align-items: center;">
                            <span style="color: var(--color-secondary); font-weight: bold;">✓</span> Activa <strong>fuerza de ventas en terreno</strong> para el desarrollo de los negocios.
                        </li>
                        <li style="display: flex; gap: 0.5rem; align-items: center;">
                            <span style="color: var(--color-secondary); font-weight: bold;">✓</span> <strong>Contacto permanente</strong> y asesoría comercial continua.
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
                    <div class="form-slogan">¡Haz tu Pedido!</div>
                    <h3 class="form-title">Solicitud de <span class="highlight">Distribución</span></h3>
                    
                    <form id="brandLeadForm">
                        <input type="hidden" name="comments_prefix" value="Interés en marca: <?php echo htmlspecialchars($brand['name']); ?>. ">
                        
                        <div class="form-grid" style="margin-bottom: 1rem;">
                            <div class="form-group">
                                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.25rem; color: var(--color-text-dark);">Nombre Completo *</label>
                                <input type="text" name="name" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-border); border-radius: var(--border-radius); font-size: 0.9rem;">
                            </div>
                            <div class="form-group">
                                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.25rem; color: var(--color-text-dark);">Nombre de la Empresa *</label>
                                <input type="text" name="company" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-border); border-radius: var(--border-radius); font-size: 0.9rem;">
                            </div>
                        </div>

                        <div class="form-grid" style="margin-bottom: 1rem;">
                            <div class="form-group">
                                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.25rem; color: var(--color-text-dark);">Cargo / Puesto *</label>
                                <input type="text" name="role" placeholder="Ej. Dueño, Comprador" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-border); border-radius: var(--border-radius); font-size: 0.9rem;">
                            </div>
                            <div class="form-group">
                                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.25rem; color: var(--color-text-dark);">Teléfono de Contacto *</label>
                                <input type="tel" name="phone" placeholder="Ej. +56912345678" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-border); border-radius: var(--border-radius); font-size: 0.9rem;">
                            </div>
                        </div>

                        <div class="form-grid" style="margin-bottom: 1rem;">
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
                                </select>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 1.5rem;">
                            <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.25rem; color: var(--color-text-dark);">Comentarios / Requerimientos Específicos</label>
                            <textarea name="comments" rows="4" placeholder="Indíquenos qué productos le interesan o detalles de su local..." style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-border); border-radius: var(--border-radius); font-size: 0.9rem; font-family: inherit; resize: vertical;"></textarea>
                        </div>

                        <div style="display: flex; flex-direction: column; align-items: center; gap: 0.75rem;">
                            <div style="font-size: 0.85rem; font-weight: 700; color: var(--color-primary); background-color: rgba(184, 59, 29, 0.08); border: 1px solid rgba(184, 59, 29, 0.15); padding: 0.6rem; border-radius: 4px; text-align: center; display: flex; align-items: center; justify-content: center; gap: 0.35rem; line-height: 1.2; width: 100%; max-width: 500px;">
                                <span style="font-size: 1.1rem;">📦</span>
                                <span>El Ticket mínimo de Compra desde $25.000</span>
                            </div>
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
            
            <div class="footer-links">
                <h3>Navegación</h3>
                <ul>
                    <li><a href="index.html">Inicio</a></li>
                    <li><a href="index.html#nosotros">¿Por qué nosotros?</a></li>
                    <li><a href="index.html#marcas">Nuestras Marcas</a></li>
                    <li><a href="index.html#catalogo">Catálogo</a></li>
                    <li><a href="reclamos.php">Formulario de Reclamos</a></li>
                </ul>
            </div>
            
            <div class="footer-contact" style="display: flex; flex-direction: column; gap: 0.85rem;">
                <h3 style="margin-bottom: 0.25rem;">Contacto Corporativo</h3>
                <div style="display: flex; flex-direction: column; gap: 0.15rem;">
                    <span style="font-size: 0.75rem; text-transform: uppercase; color: #9ca3af; font-weight: 700; display: block;">Correo de Contacto Único</span>
                    <a href="mailto:contacto@lacanastacomercializadora.cl" style="color: white; text-decoration: none; font-size: 0.9rem; opacity: 0.85;">contacto@lacanastacomercializadora.cl</a>
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
            <div class="container" style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 1rem; width: 100%;">
                <p>&copy; <?php echo date('Y'); ?> La Canasta Comercializadora y Distribuidora. Todos los derechos reservados. | Desarrollado por <a href="https://bastiascid.github.io/portafolio" target="_blank" rel="noopener" style="color: var(--color-secondary-light); text-decoration: none; font-weight: bold;">Cristian Bastias Cid</a></p>
                <div style="display: flex; gap: 1.5rem;">
                    <a href="privacidad.html" style="color: #9ca3af; font-size: 0.8rem; text-decoration: none;">Política de Privacidad</a>
                    <a href="terminos.html" style="color: #9ca3af; font-size: 0.8rem; text-decoration: none;">Términos y Condiciones</a>
                </div>
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
