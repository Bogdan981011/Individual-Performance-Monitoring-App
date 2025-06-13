import time
import threading
from update_rag_index import embed_and_index, scrape_rugby_articles, chunks_sources_file
import os
import pickle

def daily_update():
    while True:
        now = time.localtime()
        print("Heure actuelle : {:02d}:{:02d}:{:02d}".format(now.tm_hour, now.tm_min, now.tm_sec))
        seconds_until_midnight = (24 * 3600) - (now.tm_hour * 3600 + now.tm_min * 60 + now.tm_sec)
        if seconds_until_midnight < 0:
            seconds_until_midnight += 86400

        print(f"Attente jusqu'à minuit : {seconds_until_midnight} secondes")
        time.sleep(seconds_until_midnight)

        print("Lancement de la mise à jour quotidienne RAG...")
        articles = scrape_rugby_articles()
        if articles:
            if os.path.exists(chunks_sources_file):
                with open(chunks_sources_file, "rb") as f:
                    existing_chunks = pickle.load(f)
            else:
                existing_chunks = []

            embed_and_index(articles, existing_chunks)
            print("Mise à jour terminée.")
        else:
            print("Pas d'articles trouvés pour la mise à jour.")

        time.sleep(60)

def start_horloge():
    thread = threading.Thread(target=daily_update, daemon=True)
    thread.start()
