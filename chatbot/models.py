import ollama

def build_chat(model_name: str, message: str, history: list, prompt: str):
    # Construction du contexte complet pour l'IA
    chat_history = "\n".join([f"Utilisateur: {item[0]}\nAssistant: {item[1]}" for item in history])
    full_prompt = f"{prompt}\n{chat_history}\nUtilisateur: {message}\nAssistant:"

    # Appel au modèle via Ollama
    response = ollama.chat(
        model=model_name,
        messages=[
            {"role": "system", "content": prompt},
            {"role": "user", "content": full_prompt}
        ]
    )

    # Récupération de la réponse du modèle
    answer = response["message"]["content"]

    # Mise à jour de l'historique
    history.append((message, answer))

    return history, history
