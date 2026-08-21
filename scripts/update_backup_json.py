import json
import os

backup_path = "/Users/cristian/.gemini/antigravity/scratch/la-canasta/database_backup_v3.json"

if not os.path.exists(backup_path):
    print("Backup file not found!")
    exit(1)

with open(backup_path, "r", encoding="utf-8") as f:
    backup_data = json.load(f)

# Define brand IDs based on database_backup_v3.json (Iansa=2, Watts=3, Traverso=4, Mercado Nacional=5)
mn_id = 5
watts_id = 3

new_products = [
    {
        "id": 101,
        "name": "Turbo Plus Frambuesa 20g (Caja 10 un)",
        "brand_id": mn_id,
        "image_url": "assets/productos/turbo_plus_frambuesa.jpg",
        "description": "Bebida instantánea en polvo sabor frambuesa, cada sobre rinde 2 litros.",
        "category": "Bebidas y Postres",
        "featured": 1,
        "sort_order": 10
    },
    {
        "id": 102,
        "name": "Turbo Iced Tea Limón 25g (Caja 12 un)",
        "brand_id": mn_id,
        "image_url": "assets/productos/turbo_iced_tea_limon.jpg",
        "description": "Té helado instantáneo sabor limón con vitamina C, cada sobre rinde 1 litro.",
        "category": "Bebidas y Postres",
        "featured": 0,
        "sort_order": 11
    },
    {
        "id": 103,
        "name": "Turbo Zero Naranja 10g (Caja 10 un)",
        "brand_id": mn_id,
        "image_url": "assets/productos/turbo_zero_naranja.jpg",
        "description": "Bebida instantánea sabor naranja libre de azúcar, rinde 2 litros.",
        "category": "Bebidas y Postres",
        "featured": 1,
        "sort_order": 12
    },
    {
        "id": 104,
        "name": "Turbo Benny Chocolate Bolsa 200g",
        "brand_id": mn_id,
        "image_url": "assets/productos/turbo_benny_200g.jpg",
        "description": "Modificador de leche sabor chocolate enriquecido con vitaminas y hierro, rinde 20 porciones.",
        "category": "Bebidas y Postres",
        "featured": 0,
        "sort_order": 13
    },
    {
        "id": 105,
        "name": "Turbo Benny Zero Chocolate Bolsa 20g",
        "brand_id": mn_id,
        "image_url": "assets/productos/turbo_benny_zero_20g.jpg",
        "description": "Modificador de leche sabor chocolate libre de azúcar, libre de lactosa.",
        "category": "Bebidas y Postres",
        "featured": 1,
        "sort_order": 14
    },
    {
        "id": 106,
        "name": "Turbo Flan Vainilla Estuche 50g",
        "brand_id": mn_id,
        "image_url": "assets/productos/turbo_flan_vainilla.jpg",
        "description": "Mezcla en polvo para preparar flan sabor vainilla, rinde 5 porciones.",
        "category": "Bebidas y Postres",
        "featured": 0,
        "sort_order": 15
    },
    {
        "id": 107,
        "name": "Turbo Gelatina Frutilla Estuche 40g",
        "brand_id": mn_id,
        "image_url": "assets/productos/turbo_gelatina_frutilla.jpg",
        "description": "Gelatina en polvo sabor frutilla con stevia, rinde 5 porciones.",
        "category": "Bebidas y Postres",
        "featured": 1,
        "sort_order": 16
    },
    {
        "id": 108,
        "name": "Turbo Gelatina Sin Sabor Estuche 30g",
        "brand_id": mn_id,
        "image_url": "assets/productos/turbo_gelatina_sin_sabor.jpg",
        "description": "Gelatina en polvo sin sabor ideal para preparar postres y recetas.",
        "category": "Bebidas y Postres",
        "featured": 0,
        "sort_order": 17
    },
    {
        "id": 109,
        "name": "Turbo Flan Zero Vainilla Estuche 20g",
        "brand_id": mn_id,
        "image_url": "assets/productos/turbo_flan_zero.jpg",
        "description": "Flan en polvo sabor vainilla libre de azúcar, rinde 10 porciones.",
        "category": "Bebidas y Postres",
        "featured": 0,
        "sort_order": 18
    },
    {
        "id": 110,
        "name": "Turbo Gelatina Zero Frutilla Estuche 22g",
        "brand_id": mn_id,
        "image_url": "assets/productos/turbo_gelatina_zero.jpg",
        "description": "Gelatina en polvo sabor frutilla libre de azúcar, rinde 10 porciones.",
        "category": "Bebidas y Postres",
        "featured": 1,
        "sort_order": 19
    },
    {
        "id": 111,
        "name": "Turbo Energy Lata 473ml (Pack 6 un)",
        "brand_id": mn_id,
        "image_url": "assets/productos/turbo_energy_can.jpg",
        "description": "Bebida energética con taurina y vitaminas del complejo B, formato lata.",
        "category": "Bebidas y Postres",
        "featured": 1,
        "sort_order": 20
    },
    {
        "id": 112,
        "name": "Turbo Iced Tea Durazno Lata 473ml (Pack 6 un)",
        "brand_id": mn_id,
        "image_url": "assets/productos/turbo_iced_tea_can.jpg",
        "description": "Té helado líquido sabor durazno refrescante en formato lata.",
        "category": "Bebidas y Postres",
        "featured": 0,
        "sort_order": 21
    },
    {
        "id": 113,
        "name": "Jugo Watt's Selección Naranja 1.5L (Caja 6 un)",
        "brand_id": watts_id,
        "image_url": "assets/productos/jugo_watts_naranja.jpg",
        "description": "Jugo 100% de naranja exprimido, sin agua, sin azúcar añadida y sin preservantes.",
        "category": "Bebidas y Postres",
        "featured": 1,
        "sort_order": 10
    },
    {
        "id": 114,
        "name": "Jugo Watt's Selección Piña 1.5L (Caja 6 un)",
        "brand_id": watts_id,
        "image_url": "assets/productos/jugo_watts_pina.jpg",
        "description": "Jugo 100% de piña exprimido de fruta seleccionada, sin preservantes ni colorantes.",
        "category": "Bebidas y Postres",
        "featured": 0,
        "sort_order": 11
    },
    {
        "id": 115,
        "name": "Néctar Watt's Selección Frambuesa 1L (Caja 12 un)",
        "brand_id": watts_id,
        "image_url": "assets/productos/nectar_watts_frambuesa.jpg",
        "description": "Néctar de fruta natural sabor frambuesa en envase reciclable de 1 litro.",
        "category": "Bebidas y Postres",
        "featured": 0,
        "sort_order": 12
    },
    {
        "id": 116,
        "name": "Frugo Fresh Frutilla 1.75L (Caja 6 un)",
        "brand_id": watts_id,
        "image_url": "assets/productos/frugo_fresh_frutilla.jpg",
        "description": "Néctar refrescante de frutas sabor frutilla con más contenido de fruta.",
        "category": "Bebidas y Postres",
        "featured": 0,
        "sort_order": 13
    },
    {
        "id": 117,
        "name": "Frugo Fresh Piña 1.75L (Caja 6 un)",
        "brand_id": watts_id,
        "image_url": "assets/productos/frugo_fresh_pina.jpg",
        "description": "Néctar refrescante de frutas sabor piña, ideal para el abastecimiento comercial.",
        "category": "Bebidas y Postres",
        "featured": 1,
        "sort_order": 14
    },
    {
        "id": 118,
        "name": "Crema para Batir Loncoleche 500ml (Caja 12 un)",
        "brand_id": watts_id,
        "image_url": "assets/productos/loncoleche_crema.jpg",
        "description": "Crema de leche UHT para batir con 35% materia grasa, elabroada con leche natural.",
        "category": "Lácteos y Quesos",
        "featured": 1,
        "sort_order": 15
    },
    {
        "id": 119,
        "name": "Queso Cheddar Laminado Calo 1.92kg",
        "brand_id": watts_id,
        "image_url": "assets/productos/calo_cheddar_slices.jpg",
        "description": "Queso fundido procesado sabor cheddar laminado (160 láminas), formato industrial.",
        "category": "Lácteos y Quesos",
        "featured": 1,
        "sort_order": 16
    },
    {
        "id": 120,
        "name": "Piña en Rodajas Wasil 3kg (Lata)",
        "brand_id": watts_id,
        "image_url": "assets/productos/wasil_pina_slices.jpg",
        "description": "Fruta seleccionada en conserva de piñas en rodajas, sin preservantes ni colorantes.",
        "category": "Conservas",
        "featured": 0,
        "sort_order": 17
    },
    {
        "id": 121,
        "name": "Manjar Artesanal Loncoleche Balde 5kg",
        "brand_id": watts_id,
        "image_url": "assets/productos/loncoleche_manjar.jpg",
        "description": "Balde de manjar artesanal de receta tradicional, fabricado en Osorno.",
        "category": "Abarrotes",
        "featured": 1,
        "sort_order": 18
    }
]

existing_names = [p["name"] for p in backup_data["data"]["products"]]

appended = 0
for np in new_products:
    if np["name"] not in existing_names:
        backup_data["data"]["products"].append(np)
        appended += 1

if appended > 0:
    with open(backup_path, "w", encoding="utf-8") as f:
        json.dump(backup_data, f, indent=4, ensure_ascii=False)
    print(f"Successfully added {appended} products to {backup_path}")
else:
    print("No new products to add to backup JSON (they already exist).")
