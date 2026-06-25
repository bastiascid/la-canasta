#!/usr/bin/env python3
import os
from PIL import Image

def convert_to_webp():
    workspace = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
    assets_dir = os.path.join(workspace, "assets")
    
    print(f"Searching for images in {assets_dir}...")
    
    supported_extensions = (".png", ".jpg", ".jpeg")
    
    for filename in os.listdir(assets_dir):
        ext = os.path.splitext(filename)[1].lower()
        if ext in supported_extensions:
            input_path = os.path.join(assets_dir, filename)
            output_path = os.path.splitext(input_path)[0] + ".webp"
            
            # Skip if it is already converted or if it is a directory
            if os.path.isdir(input_path):
                continue
                
            old_size = os.path.getsize(input_path)
            
            print(f"Converting {filename} ({old_size / 1024:.1f} KB)...")
            try:
                with Image.open(input_path) as img:
                    # Save as WebP
                    img.save(output_path, "WEBP", quality=85)
                
                new_size = os.path.getsize(output_path)
                saved_bytes = old_size - new_size
                savings_pct = (saved_bytes / old_size) * 100
                print(f"  -> Saved as {os.path.basename(output_path)} ({new_size / 1024:.1f} KB). Savings: {savings_pct:.1f}% ({saved_bytes / 1024:.1f} KB saved)")
            except Exception as e:
                print(f"  ERROR converting {filename}: {e}")

if __name__ == "__main__":
    convert_to_webp()
