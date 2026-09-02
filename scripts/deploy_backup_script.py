import os
from ftplib import FTP

ftp = FTP('srv28.cpanelhost.cl', 'cla117198', 'UdMJhNmKCIKyQsFQWvQk')
ftp.cwd('public_html/api')
with open('api/backup_db.php', 'rb') as f:
    ftp.storbinary('STOR backup_db.php', f)
ftp.quit()
print("Uploaded backup_db.php")
