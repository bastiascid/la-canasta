import os
from ftplib import FTP

ftp = FTP('srv28.cpanelhost.cl', 'cla117198', 'UdMJhNmKCIKyQsFQWvQk')
ftp.cwd('public_html/assets/catalogos')
with open('db_backup_full.json', 'wb') as f:
    ftp.retrbinary('RETR db_backup_full.json', f.write)
ftp.quit()
print("Downloaded via FTP")
