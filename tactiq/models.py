import ollama
from ordering_dsl import *

"""
=======================================
        -- General Function --
=======================================
"""
def build_chat(model_name, message, history, prompt="", temperature=0.0, top_p=0.9, max_tokens=512):
    if history is None:
        history = []

    messages = [{"role": "system", "content": prompt}]
    for user_msg, bot_msg in history:
        messages.append({"role": "user", "content": user_msg})
        messages.append({"role": "assistant", "content": bot_msg})
    messages.append({"role": "user", "content": message})

    try:
        response = ollama.chat(
            model=model_name,
            messages=messages,
            options={
                "temperature": temperature,
                "top_p": top_p,
                "num_predict": max_tokens,
                "stop" : ["###"]
            }
        )
        reply = response["message"]["content"]

        # Verifier text
        ordered_reply = process_dsl_pipeline(reply)
    except Exception as e:
        reply = f"❌ Error: {e}"

    history.append((message, ordered_reply))
    return history, history



"""
=======================================
            -- MODELS --
=======================================
"""

def chat_with_codellama(message, history, prompt=""):
    return build_chat("codellama:7b-instruct", message, history, prompt=prompt)

def chat_with_tinyllama(message, history, prompt=""):
    return build_chat("tinyllama", message, history, prompt=prompt)

def chat_with_gemma(message, history, prompt=""):
    return build_chat("gemma", message, history, prompt=prompt)

def chat_with_phi(message, history, prompt=""):
    return build_chat("phi", message, history, prompt=prompt)

def chat_with_mistral(message, history, prompt=""):
    return build_chat("mistral:7b-instruct", message, history, prompt=prompt)

def chat_with_llama3(message, history, prompt=""):
    return build_chat("llama3", message, history, prompt=prompt)

