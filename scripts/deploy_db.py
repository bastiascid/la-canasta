import os
from ftplib import FTP

ftp = FTP('srv28.cpanelhost.cl', 'cla117198', 'UdMJhNmKCIKyQsFQWvQk')
ftp.cwd('public_html/api')
with open('api/import_db.php', 'rb') as f:
    ftp.storbinary('STOR import_db.php', f)
with open('new_db.json', 'rb') as f:
    ftp.storbinary('STOR new_db.json', f)
ftp.quit()
print("Uploaded script and json")
