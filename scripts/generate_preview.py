import os

base_out = "/Users/cristian/.gemini/antigravity/brain/95817d57-0aea-430d-b063-4ebba427ae57/scratch/bulk_extract"
md_path = "/Users/cristian/.gemini/antigravity/brain/95817d57-0aea-430d-b063-4ebba427ae57/preview_catalogo.md"

with open(md_path, "w") as f:
    f.write("# Vista Previa: Catálogo Masivo\n\n")
    f.write("Aquí hay una muestra de las imágenes extraídas automáticamente (son cientos en total, esta es una selección representativa):\n\n")
    
    # Traverso sample
    f.write("## Catálogo Traverso (Muestra)\n")
    traverso_imgs = sorted(os.listdir(os.path.join(base_out, "traverso")))[::5][:20]
    f.write("````carousel\n")
    for i, img in enumerate(traverso_imgs):
        f.write(f"![Traverso](/Users/cristian/.gemini/antigravity/brain/95817d57-0aea-430d-b063-4ebba427ae57/scratch/bulk_extract/traverso/{img})\n")
        if i < len(traverso_imgs) - 1:
            f.write("<!-- slide -->\n")
    f.write("````\n\n")
    
    # Watts sample
    f.write("## Catálogo Watt's (Muestra)\n")
    watts_imgs = sorted(os.listdir(os.path.join(base_out, "watts")))[::15][:30]
    f.write("````carousel\n")
    for i, img in enumerate(watts_imgs):
        f.write(f"![Watts](/Users/cristian/.gemini/antigravity/brain/95817d57-0aea-430d-b063-4ebba427ae57/scratch/bulk_extract/watts/{img})\n")
        if i < len(watts_imgs) - 1:
            f.write("<!-- slide -->\n")
    f.write("````\n\n")
    
    f.write("> [!NOTE]\n> Se extrajeron **127 imágenes** del catálogo Traverso y **421 imágenes** del catálogo Watt's. Como solicitaste, el recorte es automático y se subirán así. Para agregarlas, confirmame si esta muestra está bien o si procedemos directamente a cargarlas a la base de datos y al sitio.\n")
