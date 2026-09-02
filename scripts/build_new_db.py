import json
import re

with open('db_backup_full.json', 'r') as f:
    old_db = json.load(f)

kept_products = [p for p in old_db if p['id'] <= 4]

with open('/Users/cristian/.gemini/antigravity/brain/f71c6910-3a71-497d-a56b-d1fa4a0249ad/catalogos.md', 'r') as f:
    lines = f.readlines()

new_products = []
current_brand_id = 3
brand_map = {"Watt's": 3, "Traverso": 4, "Mercado Nacional": 5}

def get_category(name):
    name_up = name.upper()
    
    # Priority checks
    if "VINAGRE" in name_up: return "Salsas y Aderezos"
    
    # Alcohol check
    if any(k in name_up for k in ["VINO", "RON", "PISCO", "VODKA", "CERVEZA", "LICOR", "ESPUMANTE"]): return "Licores y Vinos"
    
    if "ACEITE" in name_up: return "Aceites"
    if any(k in name_up for k in ["LECHE", "LONCOLECHE", "CREMA", "QUESO", "MANTEQUILLA", "YOGU YOGU", "SHAKE"]): return "Lácteos"
    if any(k in name_up for k in ["JUGO", "NÉCTAR", "NECTAR", "FRUGO", "ICE TEA", "ENERGY"]): return "Bebidas y Jugos"
    if any(k in name_up for k in ["GELATINA", "FLAN", "TURBO"]): return "Bebidas y Postres"
    if any(k in name_up for k in ["KETCHUP", "MOSTAZA", "MAYONESA", "AJI", "AJÍ", "SALSA", "ACETO", "SALSITA"]): return "Salsas y Aderezos"
    if any(k in name_up for k in ["DULCE", "MERMELADA", "MANJAR"]): return "Dulces y Untables"
    if any(k in name_up for k in ["CONSERVA", "DURAZNO", "PIÑA", "CHAMPIÑON", "WASIL", "FRUTILLA", "PALMITO", "CEREZA", "LOMITO"]): return "Conservas"
    return "Abarrotes"

max_id = 4
for line in lines:
    line = line.strip()
    if line.startswith("## Catálogo"):
        if "Watt's" in line: current_brand_id = 3
        elif "Traverso" in line: current_brand_id = 4
        elif "Mercado Nacional" in line: current_brand_id = 5
    elif line.startswith("- "):
        match = re.match(r"- (.*?) \(Código: (.*?)\)", line)
        if match:
            raw_name = match.group(1).strip()
            code = match.group(2).strip()
            final_name = f"{raw_name} (Cód: {code})"
            desc = f"Código interno: {code}"
        else:
            raw_name = line[2:].strip()
            final_name = raw_name
            desc = "Abarrotes"
            
        img = "assets/productos/default.jpg"
        for old in old_db:
            if raw_name in old['name'] and 'watts_' in old['image_url']:
                img = old['image_url']
                break
                
        cat = get_category(raw_name)
        
        max_id += 1
        new_products.append({
            "id": max_id,
            "name": final_name,
            "brand_id": current_brand_id,
            "image_url": img,
            "description": desc,
            "category": cat,
            "featured": 0,
            "sort_order": 100
        })

final_db = kept_products + new_products

with open('new_db.json', 'w', encoding='utf-8') as f:
    json.dump(final_db, f, indent=4, ensure_ascii=False)

print(f"Generated new_db.json with {len(final_db)} products.")
