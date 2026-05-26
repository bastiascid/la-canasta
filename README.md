# La Canasta - Distribución de Alimentos

Landing page moderna y optimizada para **La Canasta**, distribuidora de alimentos en la Sexta Región, Chile. Venta exclusiva para almacenes y comercios con atención directa y rápida.

## Características
- **Diseño Premium**: Interfaz moderna, limpia y responsive con una paleta de colores armónica basada en el logo original (Verdes y Naranjos).
- **Acceso Directo a Pedidos**: Enlace directo a pedidos por WhatsApp con mensajes pre-formateados desde el Hero y un botón flotante persistente.
- **Formulario de Contacto**: Formulario interactivo que valida los campos, muestra un mensaje de éxito estético al enviar y ofrece la opción de reenviar los datos directamente por WhatsApp si es necesario.
- **Ubicación Integrada**: Mapa interactivo de la zona de cobertura en Rancagua y comunas aledañas, con información de contacto clara y horarios de atención.

---

## Cómo Publicar en GitHub Pages

Sigue estos sencillos pasos para publicar esta landing page en internet de forma gratuita usando **GitHub Pages**:

### Paso 1: Confirmar y guardar tus cambios locales en Git
Abre tu terminal dentro del directorio del proyecto (`la-canasta`) y ejecuta:
```bash
# 1. Agregar todos los cambios realizados
git add .

# 2. Hacer un commit (guardar los cambios)
git commit -m "Convertir a landing page con formulario de contacto y mapa"
```

### Paso 2: Crear el repositorio en tu cuenta de GitHub
1. Ingresa a tu cuenta de GitHub y crea un nuevo repositorio público ingresando a: [https://github.com/new](https://github.com/new).
2. Nómbralo como **`la-canasta`** (u otro nombre que prefieras).
3. **No** selecciones "Add a README file", "Add .gitignore" o "Choose a license" (déjalo vacío).
4. Haz clic en **Create repository**.

### Paso 3: Vincular tu repositorio local con GitHub y subir el código
Copia y ejecuta los siguientes comandos en tu terminal (asegúrate de que tu usuario sea `bastiascid`):

**Si usas HTTPS:**
```bash
git remote add origin https://github.com/bastiascid/la-canasta.git
git branch -M main
git push -u origin main
```

**Si usas SSH:**
```bash
git remote add origin git@github.com:bastiascid/la-canasta.git
git branch -M main
git push -u origin main
```

### Paso 4: Activar GitHub Pages
Una vez que el código esté en tu repositorio de GitHub:
1. En GitHub, entra a la pestaña **Settings** (Configuración) de tu repositorio `la-canasta`.
2. En la barra lateral izquierda, busca la sección **Code and automation** y haz clic en **Pages**.
3. En la sección **Build and deployment**:
   - En **Source**, selecciona **Deploy from a branch**.
   - En **Branch**, selecciona **`main`** y la carpeta **`/ (root)`**.
   - Haz clic en **Save** (Guardar).
4. Espera 1 o 2 minutos. GitHub Pages generará tu enlace. Podrás acceder a tu landing page desde:
   **`https://bastiascid.github.io/la-canasta/`**

---
Desarrollado por [Cristian Bastias Cid](https://bastiascid.github.io/portafolio).

---
*Última actualización: 26 de Mayo, 2026*
