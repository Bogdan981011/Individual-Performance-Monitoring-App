from sentence_transformers import SentenceTransformer
import faiss
import numpy as np
import pickle

model = SentenceTransformer("paraphrase-MiniLM-L6-v2")
index_file = "rag_index.faiss"
texts_file = "rag_texts.pkl"

index = None
texts = []

def load_index():
    global index, texts
    try:
        index = faiss.read_index(index_file)
        with open(texts_file, "rb") as f:
            texts = pickle.load(f)
        print("Index chargé depuis le disque.")
    except Exception as e:
        print(f"Erreur chargement index : {e}")

def search_similar_chunks(query, top_k=3):
    if index is None:
        load_index()
        if index is None:
            return ""

    query_vec = model.encode([query])
    distances, indices = index.search(np.array(query_vec), top_k)
    results = [texts[i] for i in indices[0] if i < len(texts)]
    return "\n".join(results)

def get_context_for_question(question, k=5):
    import faiss
    import pickle
    from sentence_transformers import SentenceTransformer
    import numpy as np

    model = SentenceTransformer("paraphrase-MiniLM-L6-v2")
    index = faiss.read_index("rag_index.faiss")
    distance_threshold = 30.0

    with open("rag_chunks_with_sources.pkl", "rb") as f:
        chunks_with_sources = pickle.load(f)

    q_emb = model.encode([question])
    D, I = index.search(np.array(q_emb), k)

    # D est un tableau de distances, ex : [[0.8, 1.2, 1.5, ...]]
    print (D[0][0])
    # Vérifie si la distance la plus petite est trop grande (pas de bon match)
    if D[0][0] > distance_threshold:
        # Pas de contexte pertinent, renvoyer vide
        return "", ""

    context_parts = []
    used_sources = set()
    sources_list = []

    for idx in I[0]:
        if idx < len(chunks_with_sources):
            chunk_info = chunks_with_sources[idx]
            chunk = chunk_info["chunk"]
            source = chunk_info["source"]
            context_parts.append(chunk)
            
            source_id = source.get("url", "URL inconnue")
            if source_id not in used_sources:
                used_sources.add(source_id)
                sources_list.append(f"{source.get('title', 'Titre inconnu')} - {source_id}")

    context_text = "\n\n".join(context_parts)
    sources_text = "\n".join(sources_list)
    print(context_text)
    return context_text, sources_text