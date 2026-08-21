import os
from ftplib import FTP

ftp = FTP()
ftp.connect('srv28.cpanelhost.cl', 21, timeout=60)
ftp.login('cla117198', 'UdMJhNmKCIKyQsFQWvQk')

def upload_file(local_path, remote_path):
    with open(local_path, 'rb') as f:
        ftp.storbinary(f'STOR {remote_path}', f)
    print(f"Uploaded {local_path} to {remote_path}")

def upload_directory(local_dir, remote_dir):
    try:
        ftp.mkd(remote_dir)
    except:
        pass
    
    files = os.listdir(local_dir)
    total = len(files)
    for i, file in enumerate(files):
        local_path = os.path.join(local_dir, file)
        if os.path.isfile(local_path):
            remote_path = f"{remote_dir}/{file}"
            with open(local_path, 'rb') as f:
                ftp.storbinary(f'STOR {remote_path}', f)
            if i % 50 == 0:
                print(f"Uploaded {i}/{total} from {local_dir}")

# 1. Upload API script
ftp.cwd('/public_html/api')
upload_file('/Users/cristian/.gemini/antigravity/scratch/la-canasta/api/import_bulk_catalog.php', 'import_bulk_catalog.php')

# 2. Upload JSON
ftp.cwd('/public_html/assets/catalogos')
upload_file('/Users/cristian/.gemini/antigravity/scratch/la-canasta/assets/catalogos/productos_masivos.json', 'productos_masivos.json')

# 3. Upload images
base_dir = "/Users/cristian/.gemini/antigravity/brain/95817d57-0aea-430d-b063-4ebba427ae57/scratch/bulk_extract"
ftp.cwd('/public_html/assets/productos')

print("Uploading Traverso images...")
upload_directory(os.path.join(base_dir, "traverso"), '/public_html/assets/productos')

print("Uploading Watts images...")
upload_directory(os.path.join(base_dir, "watts"), '/public_html/assets/productos')

ftp.quit()
print("Bulk upload finished successfully!")
