import os
import json

base_dir = "/Users/cristian/.gemini/antigravity/brain/95817d57-0aea-430d-b063-4ebba427ae57/scratch/bulk_extract"
out_json = "/Users/cristian/.gemini/antigravity/scratch/la-canasta/assets/catalogos/productos_masivos.json"

products = []
categories = {
    "traverso": "Abarrotes Traverso",
    "watts": "Catálogo Watt's"
}

for brand in ["traverso", "watts"]:
    brand_dir = os.path.join(base_dir, brand)
    if os.path.exists(brand_dir):
        files = sorted(os.listdir(brand_dir))
        for i, f in enumerate(files):
            # Create a simple name
            name = f"{brand.capitalize()} Producto {i+1}"
            image_path = f"assets/productos/{f}"
            products.append({
                "name": name,
                "description": f"Producto del catálogo {brand.capitalize()}.",
                "price": 0,
                "image": image_path,
                "category": categories[brand]
            })

with open(out_json, "w", encoding="utf-8") as f:
    json.dump(products, f, indent=4, ensure_ascii=False)

print(f"Generated JSON with {len(products)} products at {out_json}")
