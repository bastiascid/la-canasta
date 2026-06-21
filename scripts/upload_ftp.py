#!/usr/bin/env python3
import os
import sys
from ftplib import FTP, error_perm

# Hosting credentials provided by user
FTP_HOST = "srv28.cpanelhost.cl"
FTP_USER = "cla117198"
FTP_PASS = "UdMJhNmKCIKyQsFQWvQk"
FTP_PORT = 21
WEB_DIR = "public_html"

# Files to upload relative to workspace root
FILES_TO_UPLOAD = [
    "index.html",
    "styles.css",
    "script.js",
    "admin.html",
    "admin.js",
    ".htaccess",
    "marca.php",
    "hazte-cliente.php",
    "sobre-nosotros.php",
    "privacidad.html",
    "terminos.html",
    "robots.txt",
    "sitemap.xml",
    "favicon.png"
]

# Assets folder files to upload
ASSETS_TO_UPLOAD = [
    "assets/canasta-logo.png",
    "assets/quesos-promo.png",
    "assets/van-reparto.png"
]

# API folder files to upload
API_TO_UPLOAD = [
    "api/db.php",
    "api/auth.php",
    "api/setup.php",
    "api/leads.php",
    "api/export.php",
    "api/brands.php",
    "api/products.php",
    "api/settings.php",
    "api/offers.php",
    "api/coverage.php",
    "api/partners.php",
    "api/sub_brands.php",
    "api/upload.php"
]

def main():
    workspace = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
    print(f"Workspace directory: {workspace}")
    
    # 1. Connect and Login
    ftp = FTP()
    print(f"Connecting to FTP host: {FTP_HOST}:{FTP_PORT}...")
    try:
        ftp.connect(FTP_HOST, FTP_PORT, timeout=30)
        print("Connection established. Logging in...")
        ftp.login(FTP_USER, FTP_PASS)
        print("Successfully logged in!")
    except Exception as e:
        print(f"ERROR: Failed to connect or log in: {e}", file=sys.stderr)
        sys.exit(1)
        
    try:
        # 2. Change to web root folder
        print(f"Changing directory to {WEB_DIR}...")
        ftp.cwd(WEB_DIR)
        
        # 3. Check for conflicting default files
        print("Checking directory listing...")
        remote_files = ftp.nlst()
        print(f"Remote files in {WEB_DIR}: {remote_files}")
        
        # Conflicting files that cPanel might default to (e.g. index.php)
        conflict_defaults = ["index.php", "default.html", "default.php", "index.shtml", "default.shtml"]
        for conflict_file in conflict_defaults:
            if conflict_file in remote_files:
                print(f"WARNING: Found default/placeholder file '{conflict_file}' on the server.")
                renamed_file = f"old_{conflict_file}"
                print(f"Renaming remote '{conflict_file}' to '{renamed_file}' to prevent conflict...")
                try:
                    ftp.rename(conflict_file, renamed_file)
                    print(f"Successfully renamed '{conflict_file}' to '{renamed_file}'")
                except Exception as ex:
                    print(f"Could not rename '{conflict_file}': {ex}. Please check permissions or delete manually.")
        
        # 4. Upload root files
        for filename in FILES_TO_UPLOAD:
            local_path = os.path.join(workspace, filename)
            if not os.path.exists(local_path):
                print(f"SKIPPING: Local file not found: {local_path}")
                continue
                
            print(f"Uploading file: {filename}...")
            with open(local_path, "rb") as f:
                ftp.storbinary(f"STOR {filename}", f)
            print(f"Successfully uploaded: {filename}")
            
        # 5. Handle assets directory
        assets_dir = "assets"
        if assets_dir not in remote_files:
            print(f"Creating remote folder: {assets_dir}...")
            ftp.mkd(assets_dir)
            print(f"Remote folder '{assets_dir}' created successfully.")
            
        # 6. Upload assets files
        for asset_path in ASSETS_TO_UPLOAD:
            local_path = os.path.join(workspace, asset_path)
            if not os.path.exists(local_path):
                print(f"SKIPPING: Local asset not found: {local_path}")
                continue
                
            print(f"Uploading asset: {asset_path}...")
            with open(local_path, "rb") as f:
                ftp.storbinary(f"STOR {asset_path}", f)
            print(f"Successfully uploaded: {asset_path}")
            
        # 7. Handle api directory
        api_dir = "api"
        if api_dir not in remote_files:
            print(f"Creating remote folder: {api_dir}...")
            ftp.mkd(api_dir)
            print(f"Remote folder '{api_dir}' created successfully.")
            
        # 8. Upload api files
        for api_path in API_TO_UPLOAD:
            local_path = os.path.join(workspace, api_path)
            if not os.path.exists(local_path):
                print(f"SKIPPING: Local api file not found: {local_path}")
                continue
                
            print(f"Uploading api file: {api_path}...")
            with open(local_path, "rb") as f:
                ftp.storbinary(f"STOR {api_path}", f)
            print(f"Successfully uploaded: {api_path}")
            
        print("\nAll files uploaded successfully!")
        
    except Exception as e:
        print(f"ERROR during FTP operations: {e}", file=sys.stderr)
        ftp.quit()
        sys.exit(1)
        
    ftp.quit()
    print("FTP Connection closed.")

if __name__ == "__main__":
    main()
