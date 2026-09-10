import json
import os
import time
import requests
from ddgs import DDGS
from urllib.parse import urlparse

def get_image_url(query):
    for attempt in range(3):
        try:
            with DDGS() as ddgs:
                results = list(ddgs.images(query, max_results=1))
                if results:
                    return results[0]['image']
        except Exception as e:
            print(f"Error searching {query}: {e}")
            if "403" in str(e) or "Ratelimit" in str(e):
                print("Rate limit hit, sleeping 10 seconds...")
                time.sleep(10)
            else:
                time.sleep(2)
    return None

def download_image(url, filename):
    try:
        response = requests.get(url, stream=True, timeout=10)
        response.raise_for_status()
        with open(filename, 'wb') as f:
            for chunk in response.iter_content(8192):
                f.write(chunk)
        return True
    except Exception as e:
        print(f"Error downloading {url}: {e}")
        return False

def main():
    db_file = 'new_db.json'
    with open(db_file, 'r', encoding='utf-8') as f:
        products = json.load(f)
        
    assets_dir = 'assets/catalogos/images'
    os.makedirs(assets_dir, exist_ok=True)
    
    count = 0
    max_to_fetch = 500
    
    # We load previously updated ones to resume if interrupted
    for p in products:
        if 'default.jpg' in p.get('image_url', ''):
            query = f"{p['name']} producto supermercado"
            print(f"[{count}] Searching image for: {p['name']}...")
            img_url = get_image_url(query)
            
            if img_url:
                ext = '.jpg'
                parsed = urlparse(img_url)
                if '.' in os.path.basename(parsed.path):
                    ext = '.' + os.path.basename(parsed.path).split('.')[-1].lower()
                    if ext not in ['.jpg', '.jpeg', '.png', '.webp']:
                        ext = '.jpg'
                
                safe_name = f"prod_{p['id']}{ext}"
                local_path = os.path.join(assets_dir, safe_name)
                
                print(f"Downloading {img_url} to {local_path}...")
                if download_image(img_url, local_path):
                    p['image_url'] = local_path
                    count += 1
                else:
                    print(f"Failed to download image for {p['name']}")
            else:
                print(f"No image found for {p['name']}")
                
            time.sleep(1.5) # generous delay to avoid rate limiting
            
            # Save progress every 10 images
            if count % 10 == 0:
                with open(db_file, 'w', encoding='utf-8') as f:
                    json.dump(products, f, indent=4, ensure_ascii=False)
                    
            if count >= max_to_fetch:
                break
                
    with open(db_file, 'w', encoding='utf-8') as f:
        json.dump(products, f, indent=4, ensure_ascii=False)
        
    print(f"Successfully fetched {count} images.")

if __name__ == '__main__':
    main()
