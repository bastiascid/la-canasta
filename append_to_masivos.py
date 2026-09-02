import json
import re
import os

md_path = "/Users/cristian/.gemini/antigravity/brain/f71c6910-3a71-497d-a56b-d1fa4a0249ad/catalogos.md"
json_path = "assets/catalogos/productos_masivos.json"

with open(json_path, "r", encoding="utf-8") as f:
    masivos = json.load(f)

# Find max ID
max_id = 1000

new_products = []
current_cat = None

with open(md_path, "r", encoding="utf-8") as f:
    lines = f.readlines()

for line in lines:
    line = line.strip()
    if line.startswith("## Catálogo"):
        if "Watt's" in line:
            current_cat = "Watt's"
        elif "Traverso" in line:
            current_cat = "Traverso"
        elif "Mercado Nacional" in line:
            current_cat = "Mercado Nacional"
    elif line.startswith("- ") and current_cat in ["Traverso", "Mercado Nacional"]:
        match = re.match(r"- (.*?) \(Código: (.*?)\)", line)
        if match:
            name = match.group(1).strip()
            code = match.group(2).strip()
            desc = f"Código: {code}"
        else:
            name = line[2:].strip()
            desc = "Abarrotes"
            
        # Avoid exact duplicates
        if not any(p["name"] == name for p in masivos):
            max_id += 1
            new_products.append({
                "id": str(max_id),
                "name": name,
                "image": "assets/productos/default.jpg",
                "description": desc,
                "category": current_cat,
                "price": 0,
                "stock": 100
            })

if new_products:
    masivos.extend(new_products)
    with open(json_path, "w", encoding="utf-8") as f:
        json.dump(masivos, f, indent=4, ensure_ascii=False)
    print(f"Added {len(new_products)} products to {json_path}")
else:
    print("No new products added.")
