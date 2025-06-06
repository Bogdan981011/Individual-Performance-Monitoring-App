import ollama
from rag import get_context_for_question  # Import RAG context function

def build_chat(model_name, message, history, prompt=""):
    if history is None:
        history = []

    # Récupération du contexte RAG et des sources
    context, sources = get_context_for_question(message)
    if context:
        prompt = f"{prompt}\nVoici des informations récentes sur le rugby :\n{context}\n"

    # Préparation des messages au format Ollama
    messages = [{"role": "system", "content": prompt}]
    for user_msg, bot_msg in history:
        messages.append({"role": "user", "content": user_msg})
        messages.append({"role": "assistant", "content": bot_msg})

    messages.append({"role": "user", "content": message})

    try:
        response = ollama.chat(model=model_name, messages=messages)
        reply = response["message"]["content"]
        # Ajouter les sources à la réponse affichée
        
        print('context : \n', context)
        if sources:
            print('source')
            reply += f"\n\nSources :\n{sources}"
    except Exception as e:
        reply = f"❌ Error: {e}"

    history.append((message, reply))
    # print(history)
    return history, history


""" MODELS """

def chat_with_codellama(message, history, prompt=""):
    return build_chat("codellama", message, history, prompt=prompt)

def chat_with_tinyllama(message, history, prompt=""):
    return build_chat("tinyllama", message, history, prompt=prompt)

def chat_with_gemma(message, history, prompt=""):
    return build_chat("gemma", message, history, prompt=prompt)

def chat_with_phi(message, history, prompt=""):
    return build_chat("phi", message, history, prompt=prompt)

def chat_with_llama(message, history, prompt=""):
    return build_chat("llama3", message, history, prompt=prompt)

def chat_with_mistral(message, history, prompt=""):
    return build_chat("mistral", message, history, prompt=prompt)