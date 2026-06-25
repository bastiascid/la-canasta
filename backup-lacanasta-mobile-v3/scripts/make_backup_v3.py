#!/usr/bin/env python3
# scripts/make_backup_v3.py
import os
import sys
import shutil
import urllib.request
import json
import ssl
from ftplib import FTP

FTP_HOST = "srv28.cpanelhost.cl"
FTP_USER = "cla117198"
FTP_PASS = "UdMJhNmKCIKyQsFQWvQk"
FTP_PORT = 21
WEB_DIR = "public_html"

def main():
    workspace = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
    backup_name = "backup-lacanasta-mobile-v3"
    backup_path = os.path.join(workspace, backup_name)
    
    print(f"Workspace: {workspace}")
    print(f"Backup target: {backup_path}")
    
    # 0. Write local temp_backup.php dynamically
    local_temp_script = os.path.join(workspace, "api", "temp_backup.php")
    print(f"Generating temporary local backup script at {local_temp_script}...")
    temp_script_content = """<?php
// api/temp_backup.php
// Temporary script to dump database tables for backup. Will be deleted after backup.

require_once 'db.php';

header('Content-Type: application/json');

$passcode = isset($_GET['passcode']) ? $_GET['passcode'] : '';
if ($passcode !== 'admin123') {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Acceso denegado.'
    ]);
    exit;
}

try {
    $tables = ['settings', 'brands', 'products', 'leads', 'coverage', 'offers', 'partners', 'sub_brands'];
    $backup = [];
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT * FROM `$table`");
            $backup[$table] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $pe) {
            $backup[$table] = [];
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $backup
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
"""
    with open(local_temp_script, "w", encoding="utf-8") as f:
        f.write(temp_script_content)
        
    # 1. Upload temp_backup.php to FTP
    ftp = FTP()
    print(f"Connecting to FTP {FTP_HOST}:{FTP_PORT}...")
    try:
        ftp.connect(FTP_HOST, FTP_PORT, timeout=30)
        ftp.login(FTP_USER, FTP_PASS)
        ftp.cwd(WEB_DIR)
        
        print("Uploading temp_backup.php to remote server...")
        with open(local_temp_script, "rb") as f:
            ftp.storbinary("STOR api/temp_backup.php", f)
        print("Upload complete.")
    except Exception as e:
        print(f"FTP Upload Error: {e}")
        try:
            os.remove(local_temp_script)
        except:
            pass
        ftp.quit()
        sys.exit(1)
        
    # 2. Fetch DB backup via HTTP
    db_backup_data = None
    backup_url = "https://lacanastacomercializadora.cl/api/temp_backup.php?passcode=admin123"
    print(f"Fetching database backup from {backup_url}...")
    try:
        req = urllib.request.Request(
            backup_url, 
            headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'}
        )
        with urllib.request.urlopen(req, context=ssl._create_unverified_context()) as response:
            res_content = response.read().decode('utf-8')
            db_backup_data = json.loads(res_content)
            print("Database backup fetched successfully.")
    except Exception as e:
        print(f"Error fetching database backup: {e}")
        if hasattr(e, 'read'):
            try:
                print("Error response body:", e.read().decode('utf-8'))
            except:
                pass
        # Clean up remote file even if fetch failed
        try:
            ftp.delete("api/temp_backup.php")
            print("Cleaned up remote temp_backup.php")
        except:
            pass
        try:
            os.remove(local_temp_script)
        except:
            pass
        ftp.quit()
        sys.exit(1)
        
    # 3. Clean up remote temp_backup.php
    print("Cleaning up remote temp_backup.php...")
    try:
        ftp.delete("api/temp_backup.php")
        print("Cleaned up remote temp_backup.php successfully.")
    except Exception as e:
        print(f"Warning: Could not delete remote temp_backup.php: {e}")
    ftp.quit()
    
    # 4. Save database backup locally
    db_backup_path = os.path.join(workspace, "database_backup_v3.json")
    print(f"Saving database backup to {db_backup_path}...")
    with open(db_backup_path, "w", encoding="utf-8") as f:
        json.dump(db_backup_data, f, indent=4, ensure_ascii=False)
        
    # 5. Create backup directory
    if os.path.exists(backup_path):
        print(f"Removing existing backup directory: {backup_path}...")
        shutil.rmtree(backup_path)
        
    os.makedirs(backup_path)
    
    # 6. Copy files to backup folder
    ignore_items = [
        backup_name,
        "backup-lacanasta-v1",
        "backup-lacanasta-ajustes-v2",
        ".git",
        ".DS_Store",
        "node_modules",
        "api/temp_backup.php",
        "scripts/make_backup_v3.py"
    ]
    
    for item in os.listdir(workspace):
        if item in ignore_items:
            continue
            
        src = os.path.join(workspace, item)
        dst = os.path.join(backup_path, item)
        
        print(f"Copying {item} to backup...")
        if os.path.isdir(src):
            shutil.copytree(src, dst, ignore=shutil.ignore_patterns(".git", ".DS_Store"))
        else:
            shutil.copy2(src, dst)
            
    # Remove local temp script
    try:
        os.remove(local_temp_script)
        print("Cleaned up local temp_backup.php")
    except:
        pass
        
    print(f"\nBackup {backup_name} completed successfully!")

if __name__ == "__main__":
    main()
