import ollama

"""
=======================================
        -- General Function --
=======================================
"""
def build_chat(model_name, message, history, prompt=""):
    if history is None:
        history = []

    # Convert history to OpenAI-style message list
    messages = [{"role": "system", "content": prompt}]
    for user_msg, bot_msg in history:
        messages.append({"role": "user", "content": user_msg})
        messages.append({"role": "assistant", "content": bot_msg})

    messages.append({"role": "user", "content": message})

    # Get response from Ollama
    try:
        response = ollama.chat(model=model_name, messages=messages)
        reply = response["message"]["content"]
    except Exception as e:
        reply = f"❌ Error: {e}"

    # Append to Gradio history as (user, assistant)
    history.append((message, reply))

    return history, history  # Gradio expects (chatbot_output, state)


"""
=======================================
            -- MODELS --
=======================================
"""

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