import os
from ftplib import FTP

ftp = FTP('srv28.cpanelhost.cl', 'cla117198', 'UdMJhNmKCIKyQsFQWvQk')
ftp.cwd('public_html/api')
with open('api/import_db_v2.php', 'rb') as f:
    ftp.storbinary('STOR import_db_v2.php', f)
ftp.quit()
print("Uploaded script v2")
