import sys
import json
import re
from collections import Counter


def limpiar_palabra(palabra):
    palabra = palabra.lower()
    palabra = palabra.strip(".,;:!?¿¡()[]{}\"'")
    return palabra


def analizar_texto(texto):
    caracteres = len(texto)

    palabras = re.findall(r"\b[\wáéíóúÁÉÍÓÚñÑüÜ]+\b", texto)
    palabras_limpias = [limpiar_palabra(p) for p in palabras if limpiar_palabra(p)]

    frases = re.split(r"[.!?]+", texto)
    frases = [f.strip() for f in frases if f.strip()]

    stopwords = {
        "el", "la", "los", "las", "un", "una", "unos", "unas",
        "de", "del", "a", "en", "y", "o", "que", "es", "por",
        "para", "con", "se", "su", "sus", "al", "lo", "como",
        "este", "esta", "esto", "muy", "más", "mas"
    }

    palabras_filtradas = [
        p for p in palabras_limpias
        if p not in stopwords and len(p) > 2
    ]

    contador = Counter(palabras_filtradas)
    frecuentes = contador.most_common(5)

    if len(palabras_limpias) < 20:
        valoracion = "El texto es corto. Puede servir como ejemplo, pero tiene poca información para un análisis completo."
    elif len(palabras_limpias) < 60:
        valoracion = "El texto tiene una longitud media y permite realizar un análisis básico correcto."
    else:
        valoracion = "El texto es amplio y ofrece suficiente contenido para un análisis más completo."

    return {
        "caracteres": caracteres,
        "palabras": len(palabras_limpias),
        "frases": len(frases),
        "palabras_unicas": len(set(palabras_limpias)),
        "valoracion": valoracion,
        "palabras_frecuentes": [
            {
                "palabra": palabra,
                "veces": veces
            }
            for palabra, veces in frecuentes
        ]
    }


if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({
            "error": "No se ha recibido texto para analizar."
        }, ensure_ascii=False))
        sys.exit()

    texto = sys.argv[1]
    resultado = analizar_texto(texto)

    print(json.dumps(resultado, ensure_ascii=False))