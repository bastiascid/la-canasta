import json
import re
import os

md_path = "/Users/cristian/.gemini/antigravity/brain/f71c6910-3a71-497d-a56b-d1fa4a0249ad/catalogos.md"
backup_path = "database_backup_v3.json"

with open(backup_path, "r", encoding="utf-8") as f:
    backup_data = json.load(f)

products_list = backup_data["data"]["products"]

# Get the max ID
max_id = max([p.get("id", 0) for p in products_list]) if products_list else 100

brands_map = {
    "Watt's": 3,
    "Traverso": 4,
    "Mercado Nacional": 5
}

current_brand = None
new_products = []

with open(md_path, "r", encoding="utf-8") as f:
    lines = f.readlines()

for line in lines:
    line = line.strip()
    if line.startswith("## Catálogo"):
        if "Watt's" in line:
            current_brand = brands_map["Watt's"]
        elif "Traverso" in line:
            current_brand = brands_map["Traverso"]
        elif "Mercado Nacional" in line:
            current_brand = brands_map["Mercado Nacional"]
    elif line.startswith("- ") and current_brand is not None:
        # Match product name and code
        # Format: "- NAME (Código: CODE)" or "- NAME"
        match = re.match(r"- (.*?) \(Código: (.*?)\)", line)
        if match:
            name = match.group(1).strip()
            code = match.group(2).strip()
            desc = f"Código: {code}"
        else:
            name = line[2:].strip()
            desc = ""
        
        # Check if already exists by name
        exists = any(p["name"] == name for p in products_list)
        if not exists:
            max_id += 1
            new_products.append({
                "id": max_id,
                "name": name,
                "brand_id": current_brand,
                "image_url": "assets/productos/default.jpg",
                "description": desc,
                "category": "Abarrotes", # Default category
                "featured": 0,
                "sort_order": 100
            })

if new_products:
    backup_data["data"]["products"].extend(new_products)
    with open(backup_path, "w", encoding="utf-8") as f:
        json.dump(backup_data, f, indent=4, ensure_ascii=False)
    print(f"Added {len(new_products)} new products to {backup_path}")
else:
    print("No new products to add.")
