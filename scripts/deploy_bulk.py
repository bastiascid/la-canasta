#!/usr/bin/env python3
import os
import sys
from ftplib import FTP, error_perm
import urllib.request
import ssl

FTP_HOST = "srv28.cpanelhost.cl"
FTP_USER = "cla117198"
FTP_PASS = "UdMJhNmKCIKyQsFQWvQk"
FTP_PORT = 21
WEB_DIR = "public_html"

LOCAL_BASE = "/Users/cristian/.gemini/antigravity/scratch/la-canasta"

files_to_upload = [
    (os.path.join(LOCAL_BASE, "api/import_bulk_catalog.php"), "api/import_bulk_catalog.php"),
]

def ftp_mkdirs(ftp, remote_dir):
    parts = remote_dir.strip("/").split("/")
    current = ""
    for part in parts:
        current = f"{current}/{part}" if current else part
        try:
            ftp.mkd(current)
        except error_perm:
            pass

def upload_file(ftp, local_path, remote_path):
    parent = os.path.dirname(remote_path)
    if parent:
        ftp_mkdirs(ftp, parent)
    with open(local_path, "rb") as f:
        ftp.storbinary(f"STOR {remote_path}", f)
    print(f"Uploaded: {remote_path}")

print("Conectando a FTP...")
ftp = FTP()
ftp.connect(FTP_HOST, FTP_PORT, timeout=60)
ftp.login(FTP_USER, FTP_PASS)
ftp.cwd(WEB_DIR)
for local_path, remote_path in files_to_upload:
    upload_file(ftp, local_path, remote_path)
ftp.quit()
print("FTP done.")

# Trigger import
ctx = ssl.create_default_context()
ctx.check_hostname = False
ctx.verify_mode = ssl.CERT_NONE

import_url = "https://lacanasta.cl/api/import_bulk_catalog.php?passcode=admin123"
print("Llamando a: ", import_url)
try:
    with urllib.request.urlopen(import_url, context=ctx, timeout=30) as response:
        print(response.read().decode("utf-8"))
except Exception as e:
    print(f"Error calling URL: {e}")
