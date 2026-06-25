#!/usr/bin/env python3
import os
import shutil

def main():
    workspace = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
    backup_name = "backup-lacanasta-v1"
    backup_path = os.path.join(workspace, backup_name)
    
    print(f"Workspace: {workspace}")
    print(f"Backup target: {backup_path}")
    
    if os.path.exists(backup_path):
        print(f"Removing existing backup directory: {backup_path}...")
        shutil.rmtree(backup_path)
        
    os.makedirs(backup_path)
    
    ignore_items = [
        backup_name,
        ".git",
        ".DS_Store",
        "node_modules",
        "scripts/create_backup.py"
    ]
    
    for item in os.listdir(workspace):
        if item in ignore_items:
            continue
            
        src = os.path.join(workspace, item)
        dst = os.path.join(backup_path, item)
        
        print(f"Copying {item}...")
        if os.path.isdir(src):
            shutil.copytree(src, dst, ignore=shutil.ignore_patterns(".git", ".DS_Store"))
        else:
            shutil.copy2(src, dst)
            
    print("\nBackup completed successfully!")

if __name__ == "__main__":
    main()
