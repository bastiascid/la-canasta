import os
from ftplib import FTP
import urllib.request

FTP_HOST = "srv28.cpanelhost.cl"
FTP_USER = "cla117198"
FTP_PASS = "UdMJhNmKCIKyQsFQWvQk"

print("Uploading to FTP...")
try:
    ftp = FTP()
    ftp.connect(FTP_HOST, 21, timeout=60)
    ftp.login(FTP_USER, FTP_PASS)
    ftp.cwd("/public_html/assets/catalogos")
    local_path = "assets/catalogos/productos_masivos.json"
    remote_path = "productos_masivos.json"
    with open(local_path, "rb") as f:
        ftp.storbinary(f"STOR {remote_path}", f)
    ftp.quit()
    print("Upload successful!")
except Exception as e:
    print(f"FTP Error: {e}")

print("Triggering import...")
url = "https://lacanasta.cl/api/import_bulk_catalog.php?passcode=admin123"
try:
    with urllib.request.urlopen(url, timeout=30) as req:
        resp = req.read().decode("utf-8")
        print(f"Server response: {resp}")
except Exception as e:
    print(f"Import Error: {e}")
