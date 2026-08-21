import os
from PIL import Image

# Input directories
mn_dir = "/Users/cristian/.gemini/antigravity/brain/95817d57-0aea-430d-b063-4ebba427ae57/scratch/mercado_nacional"
watts_dir = "/Users/cristian/.gemini/antigravity/brain/95817d57-0aea-430d-b063-4ebba427ae57/scratch/watts_jpgs"

# Output directory
output_dir = "/Users/cristian/.gemini/antigravity/scratch/la-canasta/assets/productos"
os.makedirs(output_dir, exist_ok=True)

# 1. Crop from Mercado Nacional page JPEGs
# Image dimensions are 1240 x 1651
crops_mn = {
    # Page 1
    "turbo_plus_frambuesa.jpg": ("page_1.jpg", (300, 180, 750, 750)),
    "turbo_iced_tea_limon.jpg": ("page_1.jpg", (50, 900, 550, 1500)),
    
    # Page 2
    "turbo_zero_naranja.jpg": ("page_2.jpg", (80, 150, 520, 750)),
    "turbo_benny_zero_20g.jpg": ("page_2.jpg", (680, 850, 1150, 1500)),
    
    # Page 3
    "turbo_benny_200g.jpg": ("page_3.jpg", (50, 150, 520, 750)),
    "turbo_flan_vainilla.jpg": ("page_3.jpg", (480, 550, 1150, 1300)),
    
    # Page 4
    "turbo_gelatina_frutilla.jpg": ("page_4.jpg", (480, 150, 1150, 750)),
    "turbo_gelatina_sin_sabor.jpg": ("page_4.jpg", (50, 800, 600, 1500)),
    
    # Page 5
    "turbo_flan_zero.jpg": ("page_5.jpg", (550, 150, 1150, 750)),
    "turbo_gelatina_zero.jpg": ("page_5.jpg", (50, 800, 600, 1500)),
    
    # Page 6
    "turbo_energy_can.jpg": ("page_6.jpg", (80, 150, 600, 800)),
    "turbo_iced_tea_can.jpg": ("page_6.jpg", (500, 850, 1150, 1500))
}

for out_name, (src_name, bbox) in crops_mn.items():
    src_path = os.path.join(mn_dir, src_name)
    if os.path.exists(src_path):
        img = Image.open(src_path)
        cropped = img.crop(bbox)
        # Resize to standard size (e.g. 500x500 or scale)
        cropped.thumbnail((500, 500), Image.Resampling.LANCZOS)
        out_path = os.path.join(output_dir, out_name)
        cropped.save(out_path, "JPEG", quality=90)
        print(f"Cropped and saved: {out_name} (size: {cropped.size})")
    else:
        print(f"Source file not found: {src_path}")

# 2. Copy and rename standard JPEGs extracted from Watts catalog
watts_copies = {
    "page_77_img_3.jpg": "frugo_fresh_pina.jpg",
    "page_77_img_4.jpg": "frugo_fresh_frutilla.jpg",
    "page_78_img_2.jpg": "jugo_watts_naranja.jpg",
    "page_78_img_3.jpg": "jugo_watts_pina.jpg",
    "page_78_img_5.jpg": "nectar_watts_frambuesa.jpg"
}

for src_name, out_name in watts_copies.items():
    src_path = os.path.join(watts_dir, src_name)
    if os.path.exists(src_path):
        img = Image.open(src_path)
        out_path = os.path.join(output_dir, out_name)
        img.save(out_path, "JPEG", quality=90)
        print(f"Copied and saved: {out_name}")
    else:
        # Check in scratch/watts_pages directory as fallback
        fallback_path = os.path.join("/Users/cristian/.gemini/antigravity/brain/95817d57-0aea-430d-b063-4ebba427ae57/scratch/watts_pages", src_name)
        if os.path.exists(fallback_path):
            img = Image.open(fallback_path)
            out_path = os.path.join(output_dir, out_name)
            img.save(out_path, "JPEG", quality=90)
            print(f"Copied from fallback and saved: {out_name}")
        else:
            print(f"Watts source not found: {src_path}")

print("Cropping and copying completed!")
