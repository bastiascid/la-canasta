import os
import sys
from ftplib import FTP

FTP_HOST = "srv28.cpanelhost.cl"
FTP_USER = "cla117198"
FTP_PASS = "UdMJhNmKCIKyQsFQWvQk"
FTP_PORT = 21

def main():
    ftp = FTP()
    ftp.connect(FTP_HOST, FTP_PORT, timeout=30)
    ftp.login(FTP_USER, FTP_PASS)
    ftp.cwd('public_html/assets/catalogos/images')
    
    local_dir = 'assets/catalogos/images'
    count = 0
    
    for filename in os.listdir(local_dir):
        # We only upload the newly converted ones that might contain "iansa" or are newly created
        # actually let's just upload all of them if they are small, or check modified time
        local_path = os.path.join(local_dir, filename)
        if os.path.isfile(local_path):
            if filename.lower().endswith('.webp'):
                print(f"Uploading {filename}...")
                with open(local_path, "rb") as f:
                    ftp.storbinary(f"STOR {filename}", f)
                count += 1
                
    print(f"Uploaded {count} images.")
    ftp.quit()

if __name__ == "__main__":
    main()
