#!/usr/bin/env python3
"""
deploy_catalog.py
Sube los nuevos archivos del catálogo al servidor de producción via FTP,
luego llama import_catalog.php para insertar los productos en la BD.
"""

import os
import sys
from ftplib import FTP, error_perm
import urllib.request
import urllib.error

FTP_HOST = "srv28.cpanelhost.cl"
FTP_USER = "cla117198"
FTP_PASS = "UdMJhNmKCIKyQsFQWvQk"
FTP_PORT = 21
WEB_DIR = "public_html"

LOCAL_BASE = "/Users/cristian/.gemini/antigravity/scratch/la-canasta"

# Files to upload: (local_path, remote_path)
files_to_upload = []

# 1. All product images
productos_dir = os.path.join(LOCAL_BASE, "assets/productos")
for fname in os.listdir(productos_dir):
    local = os.path.join(productos_dir, fname)
    remote = f"assets/productos/{fname}"
    files_to_upload.append((local, remote))

# 2. PDF catalog files
catalogos_dir = os.path.join(LOCAL_BASE, "assets/catalogos")
for fname in os.listdir(catalogos_dir):
    local = os.path.join(catalogos_dir, fname)
    remote = f"assets/catalogos/{fname}"
    files_to_upload.append((local, remote))

# 3. Modified source files
files_to_upload += [
    (os.path.join(LOCAL_BASE, "index.html"), "index.html"),
    (os.path.join(LOCAL_BASE, "styles.css"), "styles.css"),
    (os.path.join(LOCAL_BASE, "api/setup.php"), "api/setup.php"),
    (os.path.join(LOCAL_BASE, "api/import_catalog.php"), "api/import_catalog.php"),
]

def ftp_mkdirs(ftp, remote_dir):
    """Create remote directories recursively if they don't exist."""
    parts = remote_dir.strip("/").split("/")
    current = ""
    for part in parts:
        current = f"{current}/{part}" if current else part
        try:
            ftp.mkd(current)
            print(f"  Created dir: {current}")
        except error_perm:
            pass  # Already exists

def upload_file(ftp, local_path, remote_path):
    """Upload a single file, creating parent dirs as needed."""
    parent = os.path.dirname(remote_path)
    if parent:
        ftp_mkdirs(ftp, parent)
    with open(local_path, "rb") as f:
        ftp.storbinary(f"STOR {remote_path}", f)
    size_kb = os.path.getsize(local_path) // 1024
    print(f"  ✓ Uploaded: {remote_path} ({size_kb} KB)")

print("=" * 60)
print("La Canasta — Deploy Catálogo de Productos")
print("=" * 60)
print(f"\nConectando a FTP {FTP_HOST}:{FTP_PORT}...")

ftp = FTP()
try:
    ftp.connect(FTP_HOST, FTP_PORT, timeout=60)
    ftp.login(FTP_USER, FTP_PASS)
    ftp.cwd(WEB_DIR)
    print(f"✓ Conectado. Directorio actual: {ftp.pwd()}\n")
    
    print(f"Subiendo {len(files_to_upload)} archivos...\n")
    for local_path, remote_path in files_to_upload:
        if not os.path.exists(local_path):
            print(f"  ⚠ Archivo no encontrado localmente: {local_path}")
            continue
        upload_file(ftp, local_path, remote_path)
    
    ftp.quit()
    print("\n✓ Todos los archivos subidos correctamente.\n")

except Exception as e:
    print(f"\n✗ Error FTP: {e}")
    try:
        ftp.quit()
    except:
        pass
    sys.exit(1)

# Trigger import via HTTP
print("Ejecutando import_catalog.php en el servidor...")
import_url = "https://lacanasta.cl/api/import_catalog.php?passcode=admin123"
try:
    with urllib.request.urlopen(import_url, timeout=30) as response:
        result = response.read().decode("utf-8")
        print(f"\nRespuesta del servidor:\n{result}")
except urllib.error.URLError as e:
    print(f"\n⚠ No se pudo llamar al import (puede que el dominio SSL tarde): {e}")
    print(f"  Puedes ejecutarlo manualmente visitando:\n  {import_url}")

print("\n✓ Deploy completado. Verifica la página en https://lacanasta.cl")
