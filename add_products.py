import json
import urllib.request
import urllib.parse
import ssl
ssl._create_default_https_context = ssl._create_unverified_context

HEADERS = {
    'Content-Type': 'application/json',
    'X-Admin-Passcode': 'admin123'
}
API_BASE = 'https://lacanastacomercializadora.cl/api'

products_data = [
    {"name": "Vinagre de Vino", "brand_name": "Traverso", "category": "Vinagres", "description": "Vinagre de vino blanco", "featured": 1, "sort_order": 1, "image_url": "assets/vinagre-de-vino-blanco.jpg"},
    {"name": "Vinagre de Manzana", "brand_name": "Traverso", "category": "Vinagres", "description": "Vinagre de manzana", "featured": 1, "sort_order": 2, "image_url": "assets/vinagre-de-manzana-1-l.jpg"},
    {"name": "Vinagre Rosado", "brand_name": "Traverso", "category": "Vinagres", "description": "Vinagre rosado", "featured": 0, "sort_order": 3, "image_url": "assets/vinagre-rosado-1-l.jpg"},
    {"name": "Aceto Balsámico", "brand_name": "Traverso", "category": "Gourmet", "description": "Aceto balsámico", "featured": 1, "sort_order": 4, "image_url": "assets/aceto-balsámico-500-ml.jpg"},
    {"name": "Limón 100%", "brand_name": "Traverso", "category": "Sucedáneos", "description": "Jugo de limón 100%", "featured": 1, "sort_order": 5, "image_url": "assets/limón-100-1-l.jpg"},
    {"name": "Limón de Pica", "brand_name": "Traverso", "category": "Sucedáneos", "description": "Jugo de limón de pica", "featured": 0, "sort_order": 6, "image_url": "assets/limón-de-pica-1-l.jpg"},
    {"name": "Ketchup 1 kg", "brand_name": "Traverso", "category": "Salsas", "description": "Ketchup de tomate", "featured": 1, "sort_order": 7, "image_url": "assets/ketchup-1-kg.jpg"},
    {"name": "Mostaza 1 kg", "brand_name": "Traverso", "category": "Salsas", "description": "Mostaza ideal", "featured": 0, "sort_order": 8, "image_url": "assets/mostaza-1-kg.jpg"},
    {"name": "Ají Crema 1 kg", "brand_name": "Traverso", "category": "Salsas", "description": "Ají crema", "featured": 0, "sort_order": 9, "image_url": "assets/ají-crema-1-kg.jpg"},
    {"name": "Ají Pebre 1 kg", "brand_name": "Traverso", "category": "Salsas", "description": "Ají pebre listo", "featured": 0, "sort_order": 10, "image_url": "assets/ají-pebre-1-kg.jpg"},
    {"name": "Mayonesa 900g", "brand_name": "Traverso", "category": "Salsas", "description": "Mayonesa casera", "featured": 1, "sort_order": 11, "image_url": "assets/mayonesa-900-g.jpg"},
    {"name": "Salsa de Soya", "brand_name": "Kikkoman", "category": "Cocina Oriental", "description": "Salsa de soya", "featured": 1, "sort_order": 12, "image_url": "assets/salsa-de-soya-591-ml.jpg"},
    {"name": "Salsa Teriyaki", "brand_name": "Kikkoman", "category": "Cocina Oriental", "description": "Salsa teriyaki", "featured": 0, "sort_order": 13, "image_url": "assets/salsa-teriyaki-296-ml.jpg"},
    {"name": "Café Tradicional", "brand_name": "Traverso", "category": "Café", "description": "Café tostado tradicional", "featured": 1, "sort_order": 14, "image_url": "assets/café-tradicional-500-g.jpg"},
    {"name": "Café Extrafuerte", "brand_name": "Traverso", "category": "Café", "description": "Café tostado extrafuerte", "featured": 0, "sort_order": 15, "image_url": "assets/café-extrafuerte-500-g.jpg"},
    {"name": "Azúcar Granulada 1kg", "brand_name": "Iansa", "category": "Azúcar", "description": "Azúcar granulada", "featured": 1, "sort_order": 16, "image_url": "assets/azúcar-granulada-1-kg.jpg"},
    {"name": "Azúcar Granulada 5kg", "brand_name": "Iansa", "category": "Azúcar", "description": "Formato económico", "featured": 1, "sort_order": 17, "image_url": "assets/azúcar-granulada-5-kg.jpg"},
    {"name": "Azúcar Granulada 25kg", "brand_name": "Iansa", "category": "Azúcar", "description": "Saco de azúcar", "featured": 1, "sort_order": 18, "image_url": "assets/azúcar-granulada-25-kg.jpg"},
    {"name": "Azúcar Flor", "brand_name": "Iansa", "category": "Repostería", "description": "Azúcar flor", "featured": 0, "sort_order": 19, "image_url": "assets/azúcar-flor.jpg"},
    {"name": "Azúcar Rubia", "brand_name": "Iansa", "category": "Azúcar", "description": "Azúcar rubia", "featured": 0, "sort_order": 20, "image_url": "assets/azúcar-rubia.jpg"},
    {"name": "Endulzante IANSA", "brand_name": "Iansa", "category": "Endulzante", "description": "Endulzante líquido", "featured": 0, "sort_order": 21, "image_url": "assets/endulzante-iansa.jpg"},
    {"name": "Stevia Iansa", "brand_name": "Iansa", "category": "Endulzante", "description": "Endulzante natural stevia", "featured": 0, "sort_order": 22, "image_url": "assets/stevia-iansa.jpg"},
    {"name": "Charly Carne Asada 25kg", "brand_name": "Iansa", "category": "Mascotas", "description": "Alimento para perros carne asada", "featured": 1, "sort_order": 23, "image_url": "assets/charly-carne-asada-25-kg.jpg"},
    {"name": "Charly Carne Asada 18kg", "brand_name": "Iansa", "category": "Mascotas", "description": "Alimento para perros", "featured": 0, "sort_order": 24, "image_url": "assets/charly-carne-asada-18-kg.jpg"},
    {"name": "Charly Carne Asada 9kg", "brand_name": "Iansa", "category": "Mascotas", "description": "Formato pequeño carne asada", "featured": 0, "sort_order": 25, "image_url": "assets/charly-carne-asada-9-kg.jpg"},
    {"name": "Charly Adulto Hueso 25kg", "brand_name": "Iansa", "category": "Mascotas", "description": "Alimento para perros adultos", "featured": 0, "sort_order": 26, "image_url": "assets/charly-adulto-hueso-25-kg.jpg"},
    {"name": "Felinnes Salmón 20kg", "brand_name": "Iansa", "category": "Mascotas", "description": "Alimento para gatos salmón", "featured": 1, "sort_order": 27, "image_url": "assets/felinnes-salmón-20-kg.jpg"},
    {"name": "Felinnes Pollo 20kg", "brand_name": "Iansa", "category": "Mascotas", "description": "Alimento para gatos pollo", "featured": 0, "sort_order": 28, "image_url": "assets/felinnes-pollo-20-kg.jpg"},
    {"name": "Pouch Felinnes Carne 85g", "brand_name": "Iansa", "category": "Mascotas", "description": "Alimento húmedo gatos carne", "featured": 0, "sort_order": 29, "image_url": "assets/pouch-felinnes-carne-85-g.jpg"},
    {"name": "Pouch Felinnes Salmón", "brand_name": "Iansa", "category": "Mascotas", "description": "Alimento húmedo gatos salmón", "featured": 0, "sort_order": 30, "image_url": "assets/pouch-felinnes-salmón-85-g.jpg"},
    {"name": "Arroz Grado 1", "brand_name": "Mercado Nacional", "category": "Abarrotes", "description": "Arroz de excelente calidad", "featured": 1, "sort_order": 31, "image_url": "assets/arroz-grado-1.jpg"},
    {"name": "Aceite Vegetal", "brand_name": "Mercado Nacional", "category": "Aceites", "description": "Aceite vegetal", "featured": 1, "sort_order": 32, "image_url": "assets/aceite-vegetal.jpg"},
    {"name": "Harina", "brand_name": "Mercado Nacional", "category": "Abarrotes", "description": "Harina para todo uso", "featured": 1, "sort_order": 33, "image_url": "assets/harina.jpg"},
    {"name": "Lentejas", "brand_name": "Mercado Nacional", "category": "Legumbres", "description": "Lentejas seleccionadas", "featured": 0, "sort_order": 34, "image_url": "assets/lentejas.jpg"},
    {"name": "Porotos", "brand_name": "Mercado Nacional", "category": "Legumbres", "description": "Porotos ideales para guisos", "featured": 0, "sort_order": 35, "image_url": "assets/porotos.jpg"},
    {"name": "Garbanzos", "brand_name": "Mercado Nacional", "category": "Legumbres", "description": "Garbanzos secos", "featured": 0, "sort_order": 36, "image_url": "assets/garbanzos.jpg"},
    {"name": "Tallarines", "brand_name": "Mercado Nacional", "category": "Pastas", "description": "Pasta seca ideal para preparar", "featured": 0, "sort_order": 37, "image_url": "assets/tallarines.jpg"},
    {"name": "Salsa de Tomate", "brand_name": "Mercado Nacional", "category": "Conservas", "description": "Salsa de tomate", "featured": 1, "sort_order": 38, "image_url": "assets/salsa-de-tomate.jpg"},
    {"name": "Atún", "brand_name": "Mercado Nacional", "category": "Conservas", "description": "Atún en conserva", "featured": 1, "sort_order": 39, "image_url": "assets/atún.jpg"},
    {"name": "Café Instantáneo", "brand_name": "Mercado Nacional", "category": "Café", "description": "Café instantáneo", "featured": 0, "sort_order": 40, "image_url": "assets/café-instantáneo.jpg"},
    {"name": "Té", "brand_name": "Mercado Nacional", "category": "Bebidas", "description": "Té para consumo diario", "featured": 0, "sort_order": 41, "image_url": "assets/té.jpg"},
    {"name": "Galletas", "brand_name": "Mercado Nacional", "category": "Snacks", "description": "Galletas ideales para colaciones", "featured": 0, "sort_order": 42, "image_url": "assets/galletas.jpg"},
    {"name": "Avena", "brand_name": "Mercado Nacional", "category": "Cereales", "description": "Avena para desayunos saludables", "featured": 0, "sort_order": 43, "image_url": "assets/avena.jpg"},
    {"name": "Sal de Mesa", "brand_name": "Mercado Nacional", "category": "Condimentos", "description": "Sal de mesa refinada", "featured": 0, "sort_order": 44, "image_url": "assets/sal-de-mesa.jpg"},
    {"name": "Arvejas", "brand_name": "Mercado Nacional", "category": "Conservas", "description": "Arvejas listas para servir", "featured": 0, "sort_order": 45, "image_url": "assets/arvejas.jpg"},
]

def make_request(url, method, data=None):
    if data:
        data = json.dumps(data).encode('utf-8')
    req = urllib.request.Request(url, data=data, headers=HEADERS, method=method)
    with urllib.request.urlopen(req) as response:
        return json.loads(response.read().decode())

def get_brands():
    resp = make_request(API_BASE + '/brands.php', 'GET')
    return {b['name'].lower(): b['id'] for b in resp['data']}

brands_map = get_brands()

if 'kikkoman' not in brands_map:
    print("Creating Kikkoman brand...")
    make_request(API_BASE + '/brands.php', 'POST', {
        'name': 'Kikkoman',
        'logo_url': 'assets/kikkoman-logo.jpg',
        'image_url': 'assets/kikkoman-logo.jpg',
        'description': 'Marca de salsa de soya',
        'status': 'Activa'
    })
    brands_map = get_brands()

for p in products_data:
    b_id = brands_map.get(p['brand_name'].lower())
    if not b_id:
        print(f"ERROR: Brand not found for {p['brand_name']}")
        continue
    
    p['brand_id'] = b_id
    del p['brand_name']
    print(f"Adding product: {p['name']}...")
    try:
        make_request(API_BASE + '/products.php', 'POST', p)
    except Exception as e:
        print(f"Failed to add {p['name']}: {e}")

print("Done!")
