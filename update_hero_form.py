import re

with open('index.html', 'r') as f:
    content = f.read()

# Replace the hero form structure
old_form_part = """                        <div class="form-grid" style="gap: 0.5rem;">
                            <div class="form-group" style="gap: 0.15rem;">
                                <label for="hero-name" style="font-size: 0.75rem; font-weight: 700;">Nombre</label>
                                <input type="text" id="hero-name" required placeholder="Tu nombre" style="padding: 0.45rem 0.65rem; font-size: 0.85rem;">
                            </div>
                            <div class="form-group" style="gap: 0.15rem;">
                                <label for="hero-email" style="font-size: 0.75rem; font-weight: 700;">Correo</label>
                                <input type="email" id="hero-email" required placeholder="Tu correo" style="padding: 0.45rem 0.65rem; font-size: 0.85rem;">
                            </div>
                        </div>
                        <div class="form-grid" style="gap: 0.5rem;">
                            <div class="form-group" style="gap: 0.15rem;">
                                <label for="hero-company" style="font-size: 0.75rem; font-weight: 700;">Empresa</label>
                                <input type="text" id="hero-company" required placeholder="Nombre local" style="padding: 0.45rem 0.65rem; font-size: 0.85rem;">
                            </div>
                            <div class="form-group" style="gap: 0.15rem;">
                                <label for="hero-role" style="font-size: 0.75rem; font-weight: 700;">Cargo</label>
                                <input type="text" id="hero-role" required placeholder="Ej: Dueño" style="padding: 0.45rem 0.65rem; font-size: 0.85rem;">
                            </div>
                        </div>
                        <div class="form-grid" style="gap: 0.5rem;">
                            <div class="form-group" style="gap: 0.15rem;">
                                <label for="hero-phone" style="font-size: 0.75rem; font-weight: 700;">Teléfono</label>
                                <input type="tel" id="hero-phone" required placeholder="+56 9..." style="padding: 0.45rem 0.65rem; font-size: 0.85rem;">
                            </div>
                            <div class="form-group" style="gap: 0.15rem;">
                                <label for="hero-region" style="font-size: 0.75rem; font-weight: 700;">Región</label>
                                <select id="hero-region" required style="padding: 0.45rem 0.65rem; font-size: 0.85rem;">
                                    <option value="" disabled selected>Selecciona</option>
                                    <option value="Región de O'Higgins">Región de O'Higgins</option>
                                </select>
                            </div>
                        </div>"""

new_form_part = """                        <div class="form-grid" style="gap: 0.5rem;">
                            <div class="form-group" style="gap: 0.15rem;">
                                <label for="hero-name" style="font-size: 0.75rem; font-weight: 700;">Nombre</label>
                                <input type="text" id="hero-name" required placeholder="Tu nombre" style="padding: 0.45rem 0.65rem; font-size: 0.85rem;">
                            </div>
                            <div class="form-group" style="gap: 0.15rem;">
                                <label for="hero-rut" style="font-size: 0.75rem; font-weight: 700;">RUT</label>
                                <input type="text" id="hero-rut" required placeholder="12.345.678-9" style="padding: 0.45rem 0.65rem; font-size: 0.85rem;">
                            </div>
                        </div>
                        <div class="form-grid" style="gap: 0.5rem;">
                            <div class="form-group" style="gap: 0.15rem;">
                                <label for="hero-email" style="font-size: 0.75rem; font-weight: 700;">Correo</label>
                                <input type="email" id="hero-email" required placeholder="Tu correo" style="padding: 0.45rem 0.65rem; font-size: 0.85rem;">
                            </div>
                            <div class="form-group" style="gap: 0.15rem;">
                                <label for="hero-phone" style="font-size: 0.75rem; font-weight: 700;">Teléfono</label>
                                <input type="tel" id="hero-phone" required placeholder="+56 9..." style="padding: 0.45rem 0.65rem; font-size: 0.85rem;">
                            </div>
                        </div>
                        <div class="form-grid" style="gap: 0.5rem;">
                            <div class="form-group" style="gap: 0.15rem;">
                                <label for="hero-company" style="font-size: 0.75rem; font-weight: 700;">Empresa</label>
                                <input type="text" id="hero-company" required placeholder="Nombre local" style="padding: 0.45rem 0.65rem; font-size: 0.85rem;">
                            </div>
                            <div class="form-group" style="gap: 0.15rem;">
                                <label for="hero-role" style="font-size: 0.75rem; font-weight: 700;">Cargo</label>
                                <input type="text" id="hero-role" required placeholder="Ej: Dueño" style="padding: 0.45rem 0.65rem; font-size: 0.85rem;">
                            </div>
                        </div>
                        <div class="form-grid" style="gap: 0.5rem;">
                            <div class="form-group" style="gap: 0.15rem;">
                                <label for="hero-region" style="font-size: 0.75rem; font-weight: 700;">Región</label>
                                <select id="hero-region" required style="padding: 0.45rem 0.65rem; font-size: 0.85rem;">
                                    <option value="" disabled selected>Selecciona tu región</option>
                                    <option value="Región de O'Higgins">Región de O'Higgins</option>
                                </select>
                            </div>
                            <div class="form-group" style="gap: 0.15rem;">
                                <label for="hero-comuna" style="font-size: 0.75rem; font-weight: 700;">Comuna</label>
                                <select id="hero-comuna" required style="padding: 0.45rem 0.65rem; font-size: 0.85rem; background: white;"><option value="" disabled selected>Cargando...</option></select>
                            </div>
                        </div>"""

new_content = content.replace(old_form_part, new_form_part)

with open('index.html', 'w') as f:
    f.write(new_content)
print("Form updated.")
