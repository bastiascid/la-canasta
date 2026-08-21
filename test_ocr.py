import pymupdf
import subprocess
import os

def test_ocr():
    doc = pymupdf.open('/Users/cristian/.gemini/antigravity/brain/95817d57-0aea-430d-b063-4ebba427ae57/.user_uploaded/media_1786634603428.pdf')
    page = doc[11] # Page 12
    
    img_infos = page.get_image_info(xrefs=True)
    for idx, info in enumerate(img_infos):
        w, h = info["width"], info["height"]
        if w > 50 and h > 50:
            bbox = info["bbox"]
            print(f"Image {idx} bbox: {bbox}")
            
            # Text is usually below the image. Let's define a region below it.
            # width of region: same as image, or slightly wider
            # height of region: 100 pixels below the image
            rx0 = max(0, bbox[0] - 20)
            ry0 = bbox[3]
            rx1 = min(page.rect.width, bbox[2] + 20)
            ry1 = min(page.rect.height, bbox[3] + 100)
            
            rect = pymupdf.Rect(rx0, ry0, rx1, ry1)
            
            # Render that region at higher resolution
            mat = pymupdf.Matrix(2, 2)
            pix = page.get_pixmap(matrix=mat, clip=rect)
            
            img_path = f"test_crop_{idx}.png"
            pix.save(img_path)
            
            # Run Tesseract
            # --psm 6 assumes a single uniform block of text
            res = subprocess.run(["tesseract", img_path, "stdout", "-l", "spa", "--psm", "6"], capture_output=True, text=True)
            text = res.stdout.strip()
            print(f"Extracted text: {text}\n")

if __name__ == "__main__":
    test_ocr()
