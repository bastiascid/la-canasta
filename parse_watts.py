import pymupdf
import json
import re
import sys

def parse_pdf(filepath):
    print(f"Opening {filepath}")
    doc = pymupdf.open(filepath)
    print(f"Pages: {len(doc)}")
    
    products = []
    
    for page_num in range(len(doc)):
        page = doc[page_num]
        text = page.get_text("text")
        
        # We need to find products. 
        # They usually have:
        # NAME
        # FORMAT
        # SAP : <number>
        # EAN : <number>
        
        lines = [line.strip() for line in text.split('\n') if line.strip()]
        
        for i, line in enumerate(lines):
            if "SAP" in line.upper() and ":" in line:
                sap_match = re.search(r'SAP\s*:\s*(\d+)', line, re.IGNORECASE)
                if not sap_match:
                    continue
                sap = sap_match.group(1)
                
                # Try to find EAN
                ean = ""
                ean_match = re.search(r'EAN\s*:\s*(\d+)', line, re.IGNORECASE)
                if ean_match:
                    ean = ean_match.group(1)
                elif i + 1 < len(lines):
                    ean_match = re.search(r'EAN\s*:\s*(\d+)', lines[i+1], re.IGNORECASE)
                    if ean_match:
                        ean = ean_match.group(1)
                        
                # Look for name and format going backwards
                format_str = ""
                name_str = ""
                
                if i >= 1:
                    # check if previous line has UX or ML or G or KG (format)
                    prev_line = lines[i-1]
                    if re.search(r'\d+\s*[UXx]\s*[\d,]+\s*[MmLlgGkK]+', prev_line):
                        format_str = prev_line
                        if i >= 2:
                            name_str = lines[i-2]
                    else:
                        name_str = prev_line
                        
                products.append({
                    "name": name_str,
                    "format": format_str,
                    "sap": sap,
                    "ean": ean,
                    "page": page_num + 1
                })

    return products

if __name__ == "__main__":
    filepath = sys.argv[1]
    products = parse_pdf(filepath)
    print(f"Total productos encontrados: {len(products)}")
    with open("extracted_products.json", "w", encoding="utf-8") as f:
        json.dump(products, f, ensure_ascii=False, indent=4)
