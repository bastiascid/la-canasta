import ftplib
import os

FTP_HOST = "lacanastacomercializadora.cl"
FTP_USER = "cla117198"
FTP_PASS = "UdMJhNmKCIKyQsFQWvQk"

files_to_upload = [
    "index.html",
    "hazte-cliente.php",
    "sobre-nosotros.php"
]

try:
    print(f"Connecting to {FTP_HOST}...")
    ftp = ftplib.FTP(FTP_HOST, FTP_USER, FTP_PASS)
    print("Connected successfully.")
    
    # Change to the public_html directory if it exists, otherwise list directories
    try:
        ftp.cwd("public_html")
        print("Changed directory to public_html")
    except Exception as e:
        print(f"Could not cwd to public_html: {e}")
        print("Current directory:", ftp.pwd())
        print("Directory listing:", ftp.nlst())
    
    for filename in files_to_upload:
        if os.path.exists(filename):
            print(f"Uploading {filename}...")
            with open(filename, 'rb') as f:
                ftp.storbinary(f'STOR {filename}', f)
            print(f"Successfully uploaded {filename}")
        else:
            print(f"File {filename} not found locally.")
            
    ftp.quit()
    print("FTP upload complete.")
except Exception as e:
    print(f"FTP error: {e}")
