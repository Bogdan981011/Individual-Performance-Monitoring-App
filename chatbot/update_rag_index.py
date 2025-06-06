from sentence_transformers import SentenceTransformer
from langchain.text_splitter import RecursiveCharacterTextSplitter
import faiss
import numpy as np
import requests
from bs4 import BeautifulSoup
import pickle
import os
import feedparser
from datetime import datetime

# === Config ===
model = SentenceTransformer("paraphrase-MiniLM-L6-v2")
index_file = "rag_index.faiss"
chunks_sources_file = "rag_chunks_with_sources.pkl"
log_file = "rag_log.txt"

# Liste des URLs à scraper (HTML)
URLS = [
    "https://www.allrugby.com/stats/",
    "https://www.rugbyrama.fr/",
    "https://www.lerugbynistere.fr/",
    "https://www.ffr.fr/"
]

# Flux RSS L'Équipe (rugby)
LEQUIPE_RSS_URL = "https://www.lequipe.fr/rss/rugby.xml"

def scrape_site(url):
    """Extrait le texte brut d’un site donné (scraping HTML), avec date du scraping."""
    headers = {"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)"}
    try:
        response = requests.get(url, timeout=10, headers=headers)
        soup = BeautifulSoup(response.text, 'html.parser')

        title = soup.find("title").get_text().strip() if soup.find("title") else "Sans titre"
        paragraphs = soup.find_all("p")
        content = "\n".join(p.get_text() for p in paragraphs)

        if not content.strip():
            return None
        # Date : date actuelle au format ISO
        date = datetime.utcnow().isoformat() + "Z"
        return (title, content, date)
    except Exception as e:
        print(f"Erreur de scraping sur {url} : {e}")
        return None

def scrape_rss(url_rss):
    feed = feedparser.parse(url_rss)
    articles = []
    for entry in feed.entries:
        title = entry.title
        content = entry.get('summary', '') or entry.get('content', [{}])[0].get('value', '')
        date = entry.get('published', '')  # ex: 'Tue, 28 May 2025 10:00:00 GMT'
        url = entry.get('link', '')  # <- récupère l'URL
        if content.strip():
            articles.append((title, content, date, url))  # <- ajoute url ici
    return articles


def scrape_all_articles():
    """Récupère tous les articles depuis RSS + scraping HTML"""
    all_articles = []

    # 1. Articles L'Équipe via RSS
    all_articles.extend(scrape_rss(LEQUIPE_RSS_URL))

    # 2. Articles scraping HTML classiques
    for url in URLS:
        article = scrape_site(url)
        if article:
            all_articles.append(article)

    # 3. Supprime les doublons simples par titre
    seen_titles = set()
    unique_articles = []
    for title, content, date in all_articles:
        if title not in seen_titles:
            seen_titles.add(title)
            unique_articles.append((title, content, date))

    return unique_articles

def is_chunk_relevant(chunk_text):
    """Filtre basique des chunks avec indications de futur ou événements non passés."""
    forbidden_phrases = [
        "n'a pas encore eu lieu",
        "sera",
        "sera prévu",
        "prévu pour",
        "va se dérouler",
        "doit avoir lieu",
        "devra",
        "est prévu",
        "sera organisé",
        "aura lieu",
        "devrait",
        "en attente",
        "à venir",
        "prochainement",
    ]
    chunk_lower = chunk_text.lower()
    return not any(phrase in chunk_lower for phrase in forbidden_phrases)

def embed_and_index(new_articles, existing_chunks_with_sources):
    splitter = RecursiveCharacterTextSplitter(chunk_size=500, chunk_overlap=50)
    all_new_chunks_with_sources = []

    for article in new_articles:
        if len(article) == 4:
            title, content, date, url = article
        else:
            title, content, date = article
            url = "URL inconnue"  # fallback si pas d'URL

        chunks = splitter.split_text(content)
        for chunk in chunks:
            # if is_chunk_relevant(chunk):
                all_new_chunks_with_sources.append({
                    "chunk": chunk,
                    "source": {
                        "title": title,
                        "date": date,
                        "url": url
                    }
                })
            # else:
            #     print(f"Chunk filtré car non pertinent (événement futur) : {chunk[:60]}...")


    # Liste des chunks déjà existants (texte uniquement)
    existing_chunks_texts = [item["chunk"] for item in existing_chunks_with_sources]

    # Filtrer les nouveaux chunks qui ne sont pas déjà dans l'index
    unique_chunks_with_sources = [
        item for item in all_new_chunks_with_sources if item["chunk"] not in existing_chunks_texts
    ]

    if not unique_chunks_with_sources:
        print("Aucun nouveau chunk à ajouter après filtrage.")
        return existing_chunks_with_sources

    embeddings = model.encode([item["chunk"] for item in unique_chunks_with_sources])

    if os.path.exists(index_file):
        index = faiss.read_index(index_file)
    else:
        index = faiss.IndexFlatL2(embeddings.shape[1])

    index.add(np.array(embeddings))
    faiss.write_index(index, index_file)

    all_chunks_with_sources = existing_chunks_with_sources + unique_chunks_with_sources
    with open(chunks_sources_file, "wb") as f:
        pickle.dump(all_chunks_with_sources, f)

    print(f"    {len(unique_chunks_with_sources)} nouveaux chunks ajoutés à l'index après filtrage.")

    # Enregistrement dans le fichier log avec date et titre
    with open(log_file, "a", encoding="utf-8") as logf:
        for title, _, date in new_articles:
            logf.write(f"{date}\t{title}\n")

    return all_chunks_with_sources

def scrape_rugby_articles():
    return scrape_all_articles()

if __name__ == "__main__":
    if os.path.exists(chunks_sources_file):
        with open(chunks_sources_file, "rb") as f:
            existing_chunks_with_sources = pickle.load(f)
    else:
        existing_chunks_with_sources = []

    new_articles = scrape_all_articles()

    if new_articles:
        embed_and_index(new_articles, existing_chunks_with_sources)
    else:
        print("Aucun nouvel article récupéré.")
