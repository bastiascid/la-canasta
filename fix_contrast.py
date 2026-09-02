with open('index.html', 'r') as f:
    content = f.read()

# H1
old_h1 = """<h1 class="hero-title-adjust" style="font-size: 2.2rem; line-height: 1.2; margin-top: 0.5rem; margin-bottom: 1.25rem; font-weight: 800; font-family: var(--font-heading); color: var(--color-secondary);">"""
new_h1 = """<h1 class="hero-title-adjust" style="font-size: 2.2rem; line-height: 1.2; margin-top: 0.5rem; margin-bottom: 1.25rem; font-weight: 800; font-family: var(--font-heading); color: var(--color-secondary); text-shadow: 1px 1px 0px rgba(255,255,255,0.9), -1px -1px 0px rgba(255,255,255,0.9), 1px -1px 0px rgba(255,255,255,0.9), -1px 1px 0px rgba(255,255,255,0.9), 0 0 15px rgba(255,255,255,1), 0 0 30px rgba(255,255,255,1);">"""

# H2
old_h2 = """<h2 style="font-size: 1.5rem; color: var(--color-primary); font-weight: 500; margin-top: 0.5rem; margin-bottom: 1rem; font-family: var(--font-heading);">Operador Comercial y Logístico para el Canal Tradicional</h2>"""
new_h2 = """<h2 style="font-size: 1.5rem; color: var(--color-primary); font-weight: 700; margin-top: 0.5rem; margin-bottom: 1rem; font-family: var(--font-heading); text-shadow: 1px 1px 0px rgba(255,255,255,0.9), -1px -1px 0px rgba(255,255,255,0.9), 1px -1px 0px rgba(255,255,255,0.9), -1px 1px 0px rgba(255,255,255,0.9), 0 0 15px rgba(255,255,255,1), 0 0 30px rgba(255,255,255,1);">Operador Comercial y Logístico para el Canal Tradicional</h2>"""

# P
old_p = """<p style="margin-top: 1rem; margin-bottom: 1.5rem;">Impulsamos marcas y conectamos productos con almacenes, minimarkets y comercios de barrio.</p>"""
new_p = """<p style="margin-top: 1rem; margin-bottom: 1.5rem; font-weight: 600; color: #111; text-shadow: 1px 1px 0px rgba(255,255,255,0.8), -1px -1px 0px rgba(255,255,255,0.8), 1px -1px 0px rgba(255,255,255,0.8), -1px 1px 0px rgba(255,255,255,0.8), 0 0 10px rgba(255,255,255,1), 0 0 20px rgba(255,255,255,1);">Impulsamos marcas y conectamos productos con almacenes, minimarkets y comercios de barrio.</p>"""

content = content.replace(old_h1, new_h1)
content = content.replace(old_h2, new_h2)
content = content.replace(old_p, new_p)

with open('index.html', 'w') as f:
    f.write(content)
print("Updated text contrast.")
