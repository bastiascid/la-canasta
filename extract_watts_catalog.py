import pymupdf
import os
import json
import uuid

def extract_products(pdf_path, output_dir, json_path, page_names_map):
    os.makedirs(output_dir, exist_ok=True)
    doc = pymupdf.open(pdf_path)
    
    all_products = []
    
    # We load existing products to append to them
    if os.path.exists(json_path):
        try:
            with open(json_path, 'r', encoding='utf-8') as f:
                all_products = json.load(f)
        except Exception:
            all_products = []
            
    # Remove old Watts products to avoid duplicates if re-run
    all_products = [p for p in all_products if not p.get('name', '').startswith("Watts -")]
    
    for page_num in range(len(doc)):
        # Page index is 1-based in our map
        real_page_num = page_num + 1
        page = doc[page_num]
        
        # Get all images
        image_list = page.get_images(full=True)
        if not image_list:
            continue
            
        # We need the coordinates of the images to sort them left-to-right, top-to-bottom
        # But get_images() doesn't return coordinates directly.
        # We can use page.get_image_info()
        img_infos = page.get_image_info(xrefs=True)
        
        # Filter out tiny images (like logos or icons)
        valid_images = []
        for info in img_infos:
            w, h = info["width"], info["height"]
            if w > 50 and h > 50:
                valid_images.append(info)
                
        # Sort images by Y, then X
        # Bbox is (x0, y0, x1, y1)
        valid_images.sort(key=lambda img: (round(img["bbox"][1] / 50) * 50, img["bbox"][0]))
        
        names_for_page = page_names_map.get(str(real_page_num), [])
        
        for idx, img_info in enumerate(valid_images):
            xref = img_info["xref"]
            if xref == 0:
                continue
            
            try:
                base_image = doc.extract_image(xref)
                image_bytes = base_image["image"]
                image_ext = base_image["ext"]
                
                # Generate a unique name
                prod_id = str(uuid.uuid4())[:8]
                img_filename = f"watts_{real_page_num}_{idx}_{prod_id}.{image_ext}"
                img_filepath = os.path.join(output_dir, img_filename)
                
                with open(img_filepath, "wb") as img_file:
                    img_file.write(image_bytes)
                    
                # Determine name
                if idx < len(names_for_page):
                    prod_name = names_for_page[idx]
                else:
                    prod_name = f"Watts - Producto P{real_page_num}-{idx+1}"
                    
                all_products.append({
                    "id": f"watts_{real_page_num}_{idx}",
                    "name": prod_name,
                    "image": f"assets/catalogos/images/{img_filename}",
                    "description": "Producto del catálogo Watt's 2026",
                    "price": 0,
                    "category": "Watts",
                    "stock": 100
                })
            except Exception as e:
                print(f"Error extracting image {xref} on page {real_page_num}: {e}")
                
    with open(json_path, 'w', encoding='utf-8') as f:
        json.dump(all_products, f, ensure_ascii=False, indent=4)
        
    print(f"Extraction complete! Total products in catalog: {len(all_products)}")

if __name__ == "__main__":
    pdf_path = "/Users/cristian/.gemini/antigravity/brain/95817d57-0aea-430d-b063-4ebba427ae57/.user_uploaded/media_1786634603428.pdf"
    output_dir = "/Users/cristian/.gemini/antigravity/scratch/la-canasta/assets/catalogos/images"
    json_path = "/Users/cristian/.gemini/antigravity/scratch/la-canasta/assets/catalogos/productos_masivos.json"
    
    # Map of page numbers to lists of product names (left-to-right, top-to-bottom)
    page_names_map = {
        "7": [
            "ACEITE CHEF OLIVA EXTRA VIRGEN 250 ml",
            "ACEITE CHEF OLIVA EXTRA VIRGEN 500 ml",
            "ACEITE CHEF OLIVA EXTRA VIRGEN 1 L",
            "ACEITE CHEF OLIVA EX VIRGEN SPRAY 180 ml",
            "ACEITE CHEF OLIVA 1 L",
            "ACEITE CHEF OLIVA MARAVILLA 1 L"
        ],
        "8": [
            "ACEITE CHEF MARAVILLA 500 ml",
            "ACEITE CHEF MARAVILLA 1 L",
            "ACEITE CHEF SPRAY 180 ml",
            "ACEITE CHEF MAÍZ 1 L",
            "ACEITE CHEF MARAVILLA 5 L",
            "ACEITE CHEF PEPITA DE UVA 500 ml",
            "ACEITE CHEF PEPITA DE UVA 1 L"
        ],
        "9": [
            "ACEITE BELMONT VEGETAL 1 L",
            "ACEITE BELMONT VEGETAL 250 ml",
            "ACEITE BELMONT CANOLA 900 ml",
            "ACEITE BELMONT FREÍR 1 L",
            "ACEITE BELMONT VEGETAL CON CANOLA 5 L"
        ],
        "10": [
            "ACEITE MAZOLA CANOLA 1 L",
            "ACEITE CRISTAL VEGETAL 900 ml"
        ],
        "12": [
            "LONCOLECHE POLVO ENTERA 760 g",
            "LONCOLECHE POLVO SEMI DESCREMADA 760 g",
            "LONCOLECHE POLVO DESCREMADA 760 g",
            "LONCOLECHE POLVO EXTRA CALCIO 800 g",
            "LONCOLECHE POLVO S/L 800 g",
            "LONCOLECHE EXTRA PROTEIN POLVO 550 g"
        ],
        "13": [
            "LECHE POLVO CALO CRECER 4+ 800 g",
            "LECHE POLVO CALO CRECER 1+ 800 g",
            "LECHE POLVO CALO CRECER 1+ 1.5 kg",
            "LECHE POLVO CALO 26% ENTERA 850 g",
            "LECHE POLVO CALO 26% ENTERA 1440 g"
        ],
        "15": [
            "LONCOLECHE BLANCA ENTERA 1 L",
            "LONCOLECHE BLANCA SEMI DESCREMADA 1 L",
            "LONCOLECHE BLANCA DESCREMADA 1 L",
            "LONCOLECHE S/L ENTERA 1 L",
            "LONCOLECHE S/L SEMIDESCREMADA 1 L",
            "LONCOLECHE S/L DESCREMADA 1 L",
            "LONCOLECHE S/L EXTRA CALCIO 1 L"
        ],
        "16": [
            "LONCOLECHE PLÁTANO 1 L",
            "LONCOLECHE FRUTILLA 1 L",
            "LONCOLECHE VAINILLA 1 L",
            "LONCOLECHE CHOCOLATE 1 L",
            "LONCOLECHE DESC LIGHT CHOCOLATE 1 L",
            "LONCOLECHE S/L CHOCOLATE 1 L",
            "LONCOLECHE S/L VAINILLA 1 L",
            "LONCOLECHE S/L FRUTILLA 1 L"
        ]
    }
    
    extract_products(pdf_path, output_dir, json_path, page_names_map)
