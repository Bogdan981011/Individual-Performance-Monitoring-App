import os
from flask import Flask, request, render_template, jsonify
import requests
from langchain_community.document_loaders import TextLoader
from langchain.text_splitter import CharacterTextSplitter
from langchain_huggingface import HuggingFaceEmbeddings
from langchain_community.vectorstores import FAISS

app = Flask(__name__)

# 1. Chargement des documents rugby
rugby_folder = 'rugby_docs'
docs = []
for filename in os.listdir(rugby_folder):
    if filename.endswith('.txt'):
        loader = TextLoader(os.path.join(rugby_folder, filename))
        docs.extend(loader.load())

# 2. Splitter texte
text_splitter = CharacterTextSplitter(chunk_size=400, chunk_overlap=40)
texts = text_splitter.split_documents(docs)

# 3. Embeddings
embeddings = HuggingFaceEmbeddings(model_name='all-MiniLM-L6-v2')

# 4. Création index FAISS
vectorstore = FAISS.from_documents(texts, embeddings)

def query_llama3(prompt):
    url = "http://localhost:11434/api/chat"
    headers = {"Content-Type": "application/json"}
    payload = {
        "model": "llama3",
        "messages": [{"role": "user", "content": prompt}],
        "max_tokens": 512
    }
    
    response = requests.post(url, json=payload, headers=headers, stream=True)
    response.raise_for_status()
    
    full_response = ""
    for line in response.iter_lines():
        if line:
            data = line.decode('utf-8')
            import json
            try:
                json_data = json.loads(data)
                content = json_data.get('message', {}).get('content', '')
                full_response += content
            except json.JSONDecodeError:
                pass
    
    return full_response


# Fonction pour répondre à une question
def answer_question(question):
    related_docs = vectorstore.similarity_search(question, k=3)
    context = "\n".join([doc.page_content for doc in related_docs])

    prompt = f"""
    Tu es un expert en rugby. Voici des informations utiles extraites :
    {context}

    En te basant sur ces informations, réponds clairement et simplement à la question :
    {question}
    """
    return query_llama3(prompt)

@app.route("/", methods=["GET", "POST"])
def index():
    answer = None
    if request.method == "POST":
        question = request.form.get("question")
        if question:
            answer = answer_question(question)
    return render_template("index.html", answer=answer)

if __name__ == "__main__":
    app.run(debug=True)
