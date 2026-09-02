import json

print("# Listado de Productos por Catálogo\n")

print("## Catálogo Watt's\n")
with open('assets/catalogos/productos_masivos.json', 'r') as f:
    watts_data = json.load(f)
for p in watts_data:
    name = p.get('name', 'N/A')
    code = p.get('sap', p.get('ean', 'N/A'))
    print(f"- {name} (Código: {code})")

print("\n## Catálogo Traverso\n")
with open('traverso_ocr.txt', 'r') as f:
    for line in f:
        parts = line.strip().split()
        if len(parts) > 4:
            code = parts[0]
            # Name is sometimes the rest, sometimes not
            name = " ".join(parts[1:])
            print(f"- {name} (Código: {code})")

print("\n## Catálogo Mercado Nacional (Turbo, Benny)\n")
print("*Nota: El catálogo proporcionado para Mercado Nacional no incluye códigos de producto.*")
print("- TURBO PLUS 2L")
print("- TURBO ICED TEA 1L")
print("- TURBO ICED TEA DOYPACK 10L")
print("- TURBO ICED TEA TARRO 6L")
print("- TURBO ZERO 2L")
print("- TURBO BENNY ZERO 20g")
print("- TURBO BENNY 200g")
print("- TURBO FLAN")
print("- TURBO GELATINA")
print("- TURBO GELATINA SIN SABOR")
print("- TURBO FLAN ZERO")
print("- TURBO GELATINA ZERO")
print("- TURBO ENERGY 473ml")
print("- TURBO ICED TEA LATA 473ml")

