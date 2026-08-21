import os
import pypdf

def extract_images_from_pdf(pdf_path, output_dir, prefix):
    os.makedirs(output_dir, exist_ok=True)
    reader = pypdf.PdfReader(pdf_path)
    count = 0
    
    for p_num, page in enumerate(reader.pages):
        images = page.images
        for j, img_info in enumerate(images):
            name = img_info.name
            data = img_info.data
            ext = name.split(".")[-1].lower() if "." in name else "jpg"
            # Filter out tiny images (like icons or logos)
            if len(data) < 10000: # skip images smaller than 10KB
                continue
                
            img_out_path = os.path.join(output_dir, f"{prefix}_p{p_num+1}_{j}.{ext}")
            with open(img_out_path, "wb") as f:
                f.write(data)
            count += 1
            
    print(f"Extracted {count} images from {os.path.basename(pdf_path)} into {output_dir}")

base_out = "/Users/cristian/.gemini/antigravity/brain/95817d57-0aea-430d-b063-4ebba427ae57/scratch/bulk_extract"
watts_pdf = "/Users/cristian/Desktop/catalogos la canasta/Catalogo Watts 2026 Comprimido (1).pdf"
traverso_pdf = "/Users/cristian/Desktop/catalogos la canasta/Catalogo Traverso.pdf"

print("Starting bulk extraction...")
extract_images_from_pdf(traverso_pdf, os.path.join(base_out, "traverso"), "traverso")
extract_images_from_pdf(watts_pdf, os.path.join(base_out, "watts"), "watts")
print("Done!")
