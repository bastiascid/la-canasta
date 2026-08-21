import pymupdf
import subprocess
import os
import json
import uuid
import re

def extract_products(pdf_path, output_dir, json_path):
    os.makedirs(output_dir, exist_ok=True)
    doc = pymupdf.open(pdf_path)
    
    all_products = []
    if os.path.exists(json_path):
        try:
            with open(json_path, 'r', encoding='utf-8') as f:
                all_products = json.load(f)
        except Exception:
            all_products = []
            
    # Keep Traverso products but remove old Watts to avoid duplicates
    all_products = [p for p in all_products if p.get('category') != "Watts"]
    
    print(f"Total pages: {len(doc)}")
    
    for page_num in range(6, len(doc)): # Start from page 7 (index 6) to skip intro
        real_page_num = page_num + 1
        page = doc[page_num]
        
        img_infos = page.get_image_info(xrefs=True)
        valid_images = []
        for info in img_infos:
            w, h = info["width"], info["height"]
            if w > 50 and h > 50:
                valid_images.append(info)
                
        # Sort images by Y, then X
        valid_images.sort(key=lambda img: (round(img["bbox"][1] / 50) * 50, img["bbox"][0]))
        
        if not valid_images:
            continue
            
        print(f"Processing page {real_page_num}...")
        
        for idx, img_info in enumerate(valid_images):
            xref = img_info["xref"]
            if xref == 0:
                continue
            
            bbox = img_info["bbox"]
            
            # Text is usually below the image. 
            rx0 = max(0, bbox[0] - 20)
            ry0 = max(0, bbox[3] - 10)
            rx1 = min(page.rect.width, bbox[2] + 20)
            ry1 = min(page.rect.height, bbox[3] + 120)
            
            if rx0 >= rx1 or ry0 >= ry1:
                continue # Invalid crop
            
            rect = pymupdf.Rect(rx0, ry0, rx1, ry1)
            mat = pymupdf.Matrix(2, 2)
            try:
                pix = page.get_pixmap(matrix=mat, clip=rect)
                img_path = f"crop_ocr.png"
                pix.save(img_path)
                
                # Run Tesseract
                res = subprocess.run(["tesseract", img_path, "stdout", "-l", "spa", "--psm", "6"], capture_output=True, text=True, errors='replace')
                ocr_text = res.stdout.strip()
                
                # Parse OCR text
                lines = [line.strip() for line in ocr_text.split('\n') if line.strip()]
                name = ""
                format_str = ""
                sap = ""
                ean = ""
                
                for i, line in enumerate(lines):
                    # SAP
                    m = re.search(r'SAP\s*[:;]\s*(\d+)', line, re.IGNORECASE)
                    if m: sap = m.group(1)
                    
                    # EAN
                    m = re.search(r'EAN\s*[:;]\s*(\d+)', line, re.IGNORECASE)
                    if m: ean = m.group(1)
                    
                    # Format
                    if re.search(r'\d+\s*[UXx]\s*[\d,]+', line):
                        format_str = line
                        
                # If we couldn't find SAP, it might not be a product
                if not sap:
                    continue
                    
                # Name is usually the first 1 or 2 lines before the format
                name_parts = []
                for line in lines:
                    if line == format_str or "SAP" in line.upper() or "EAN" in line.upper():
                        break
                    # Clean up some common OCR artifacts
                    clean_line = re.sub(r'^[A-Z]\.\s*', '', line) # remove "A. "
                    if clean_line:
                        name_parts.append(clean_line)
                        
                name = " ".join(name_parts)
                if not name:
                    name = f"Producto Watt's {sap}"
                    
                print(f"  -> Extracted: {name} | SAP: {sap} | EAN: {ean} | Format: {format_str}")
                    
                # Extract actual image
                base_image = doc.extract_image(xref)
                image_bytes = base_image["image"]
                image_ext = base_image["ext"]
                
                prod_id = str(uuid.uuid4())[:8]
                img_filename = f"watts_{real_page_num}_{idx}_{prod_id}.{image_ext}"
                img_filepath = os.path.join(output_dir, img_filename)
                
                with open(img_filepath, "wb") as img_file:
                    img_file.write(image_bytes)
                    
                all_products.append({
                    "id": f"watts_{real_page_num}_{idx}",
                    "name": name,
                    "format": format_str,
                    "sap": sap,
                    "ean": ean,
                    "image": f"assets/catalogos/images/{img_filename}",
                    "description": "Producto del catálogo Watt's 2026",
                    "price": 0,
                    "category": "Watts",
                    "stock": 100
                })
                
                with open(json_path, 'w', encoding='utf-8') as f:
                    json.dump(all_products, f, ensure_ascii=False, indent=4)
                    
            except Exception as e:
                import traceback
                print(f"Error on page {real_page_num} image {idx}: {e}")
                traceback.print_exc()
                
    print(f"Extraction complete! Total products loaded: {len(all_products)}")

if __name__ == "__main__":
    pdf_path = "/Users/cristian/.gemini/antigravity/brain/95817d57-0aea-430d-b063-4ebba427ae57/.user_uploaded/media_1786634603428.pdf"
    output_dir = "/Users/cristian/.gemini/antigravity/scratch/la-canasta/assets/catalogos/images"
    json_path = "/Users/cristian/.gemini/antigravity/scratch/la-canasta/assets/catalogos/productos_masivos.json"
    extract_products(pdf_path, output_dir, json_path)
