import json
import os
import re
from PIL import Image

db_path = 'database_backup_v3.json'
with open(db_path, 'r', encoding='utf-8') as f:
    db = json.load(f)

brands = db['data']['brands']
products = db['data']['products']

# Create Iansa brand if not exists
iansa_brand_id = None
for b in brands:
    if b['name'].lower() == 'iansa':
        iansa_brand_id = b['id']
        break

if not iansa_brand_id:
    iansa_brand_id = max([b['id'] for b in brands]) + 1
    brands.append({
        "id": iansa_brand_id,
        "name": "Iansa",
        "logo_url": "assets/marcas/iansa-logo.png",
        "description": "Iansa y Iansa Agro"
    })

max_prod_id = max([p['id'] for p in products]) if products else 0

iansa_dir = '/Users/cristian/Downloads/fotos catalogo iansa'
assets_dir = 'assets/catalogos/images'

if not os.path.exists(assets_dir):
    os.makedirs(assets_dir)

for filename in os.listdir(iansa_dir):
    if filename.lower().endswith('.jpg'):
        input_path = os.path.join(iansa_dir, filename)
        
        # Clean filename to generate product name
        # e.g., Hero-Image-Azucar-Flor-Iansa-1kg.jpg -> Azucar Flor Iansa 1kg
        # Iansa-Agro-Arroz-Grado1-1kg.jpg -> Arroz Grado1 1kg (Iansa Agro)
        name_raw = filename[:-4].replace('Hero-Image-', '').replace('Iansa-Agro-', '').replace('-', ' ').strip()
        name_raw = re.sub(r'\s*\(\d+\)', '', name_raw) # remove (1) if exists
        name_raw = name_raw.replace('Iansa', '').strip()
        final_name = f"{name_raw} Iansa"
        if "Agro" in filename:
            final_name = f"{name_raw} Iansa Agro"
            
        final_name = final_name.replace("  ", " ")
        
        # Determine category
        cat = "Abarrotes"
        name_upper = final_name.upper()
        if "ARROZ" in name_upper or "LENTEJAS" in name_upper or "GARBANZOS" in name_upper or "POROTOS" in name_upper:
            cat = "Legumbres y Arroz"
        elif "SUCRALOSA" in name_upper or "STEVIA" in name_upper or "ALULOSA" in name_upper or "AZUCAR" in name_upper or "CHANCACA" in name_upper:
            cat = "Endulzantes y Azúcar"

        # Convert image to webp
        safe_filename = filename.replace(' ', '_').replace('(', '').replace(')', '').replace('Hero-Image-', '').lower()
        safe_name_webp = os.path.splitext(safe_filename)[0] + '.webp'
        output_path = os.path.join(assets_dir, safe_name_webp)
        
        with Image.open(input_path) as img:
            img.save(output_path, "WEBP", quality=85)
            
        max_prod_id += 1
        products.append({
            "id": max_prod_id,
            "name": final_name.title(),
            "brand_id": iansa_brand_id,
            "image_url": f"assets/catalogos/images/{safe_name_webp}",
            "description": "Catálogo Iansa",
            "category": cat,
            "featured": 0,
            "sort_order": 100
        })

with open(db_path, 'w', encoding='utf-8') as f:
    json.dump(db, f, indent=4, ensure_ascii=False)

print(f"Added new products. Total products now: {len(products)}")
