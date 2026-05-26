import sys
import json
import chromadb

db_path = "ciclosformativos"
collection_name = "ciclosformativos_parrafos"

RANGO = 2
MIN_PALABRAS = 10


def ampliar_consulta(texto):
    texto_original = texto.strip()
    texto_minusculas = texto_original.lower()

    extras = []

    if "daw" in texto_minusculas:
        extras.append("Desarrollo de Aplicaciones Web")
        extras.append("Ciclo Formativo de Grado Superior en Desarrollo de Aplicaciones Web")
        extras.append("Desarrollo web en entorno cliente")
        extras.append("Desarrollo web en entorno servidor")
        extras.append("Despliegue de aplicaciones web")
        extras.append("Diseño de interfaces web")

    if "dam" in texto_minusculas:
        extras.append("Desarrollo de Aplicaciones Multiplataforma")

    if "asir" in texto_minusculas:
        extras.append("Administración de Sistemas Informáticos en Red")

    if "smr" in texto_minusculas:
        extras.append("Sistemas Microinformáticos y Redes")

    if extras:
        return texto_original + " " + " ".join(extras)

    return texto_original


def buscar_con_contexto(texto):
    consulta_ampliada = ampliar_consulta(texto)

    client = chromadb.PersistentClient(path=db_path)
    collection = client.get_collection(name=collection_name)

    resultados = collection.query(
        query_texts=[consulta_ampliada],
        n_results=8
    )

    documentos = resultados["documents"][0]
    metadatas = resultados["metadatas"][0]

    candidatos = []

    for doc, meta in zip(documentos, metadatas):
        if len(doc.split()) >= MIN_PALABRAS:
            candidatos.append({
                "documento": doc,
                "metadata": meta
            })

    if not candidatos:
        return {
            "ok": False,
            "error": "No hay resultados válidos.",
            "contexto": []
        }

    mejor = candidatos[0]
    indice = mejor["metadata"]["indice"]

    contexto = []

    for i in range(indice - RANGO, indice + RANGO + 1):
        if i < 0:
            continue

        try:
            res = collection.get(ids=[f"parrafo_{i}"])
            doc = res["documents"][0]

            if i < indice:
                etiqueta = "Anterior"
            elif i > indice:
                etiqueta = "Siguiente"
            else:
                etiqueta = "Central"

            contexto.append({
                "indice": i,
                "etiqueta": etiqueta,
                "contenido": doc
            })

        except Exception:
            continue

    return {
        "ok": True,
        "consulta_original": texto,
        "consulta_ampliada": consulta_ampliada,
        "indice_central": indice,
        "contexto": contexto
    }


if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({
            "ok": False,
            "error": "No se ha recibido ninguna consulta.",
            "contexto": []
        }, ensure_ascii=False))
        sys.exit()

    consulta = sys.argv[1]
    resultado = buscar_con_contexto(consulta)

    print(json.dumps(resultado, ensure_ascii=False))
