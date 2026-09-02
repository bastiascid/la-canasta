from ftplib import FTP
ftp = FTP("srv28.cpanelhost.cl", "cla117198", "UdMJhNmKCIKyQsFQWvQk")
ftp.cwd("public_html/api")
try:
    ftp.mkd("whatsapp")
except:
    pass
ftp.quit()
