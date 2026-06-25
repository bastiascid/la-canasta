import json
import ssl
import sys
import urllib.request

# Configuración
USERNAME = "bastiascid"
REPOS_TO_MAKE_PUBLIC = [
    "modulo-mantenciones", "Clubdeleones2", "smarturna", "colegio-diego-portales",
    "sistema-asistencia-ena", "la-blueseria", "medical-appointments", "java-crud-app",
    "appyudame", "carnes-ohiggins-redesign", "cordillera-landing", "neumaticos-system",
    "club-leones-machali-redesign", "Desafio-Covid19", "Validador_de_Titulos_Profesionales",
    "Desafio-cachipun", "Pagina_Web-Grupo_Legal_Chile", "jwt-example-desafioLatam", "Prueba-Viajes-Chile"
]

def main():
    if len(sys.argv) < 2:
        print("Uso: python3 make_public.py <GITHUB_TOKEN>")
        sys.exit(1)

    token = sys.argv[1]
    
    # Ignorar errores de SSL
    ctx = ssl.create_default_context()
    ctx.check_hostname = False
    ctx.verify_mode = ssl.CERT_NONE
    
    for repo in REPOS_TO_MAKE_PUBLIC:
        url = f"https://api.github.com/repos/{USERNAME}/{repo}"
        data = json.dumps({"private": False}).encode('utf-8')
        
        print(f"Cambiando {repo} a PÚBLICO...", end=" ")
        
        req = urllib.request.Request(url, data=data, method='PATCH')
        req.add_header("Authorization", f"token {token}")
        req.add_header("Accept", "application/vnd.github.v3+json")
        req.add_header("Content-Type", "application/json")
        
        try:
            with urllib.request.urlopen(req, context=ctx) as response:
                if response.status == 200:
                    print("✅ Hecho")
                else:
                    print(f"❌ Error: {response.status}")
        except urllib.error.HTTPError as e:
            print(f"❌ Error: {e.code}")
        except Exception as e:
            print(f"❌ Excepción: {str(e)}")

if __name__ == "__main__":
    main()
