#!/bin/bash
echo "Starting image fetch..."
python3 -u fetch_missing_images.py

echo "Optimizing to webp..."
python3 scripts/convert_webp.py

echo "Uploading images..."
python3 upload_iansa_images.py # actually this script only uploads webp from assets/catalogos/images

echo "Uploading DB..."
python3 scripts/deploy_db_v3.py

echo "Importing to MySQL..."
curl -s "https://www.lacanastacomercializadora.cl/api/import_db_v2.php?passcode=admin123"
echo "Done!"
