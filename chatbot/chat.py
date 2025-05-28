import gradio as gr
from models import *

prompt = (
    "You are an support assistant in rugby team, answer in french with a friendly and supportive attitude. "
    "Verify your syntax before send the answer. Follow the vibe of the user. And put some emoji."
)


# Router function to select the right model
def chat_with_selected_model(message, history, model_choice):
    return build_chat(model_name=model_choice, message=message, history=history, prompt=prompt)


# Gradio Interface with model selection
with gr.Blocks() as demo:
    gr.Markdown("### 💬 Assistant powered by Ollama")

    model_selector = gr.Dropdown(
        ["codellama", "tinyllama", "gemma", "phi", "llama3", "mistral"],
        value="mistral",
        label="🧠 Select LLM Model",
        interactive=True
    )

    chatbot = gr.Chatbot(label="Generator", type="messages")
    state = gr.State([])
    
    with gr.Row():
        textbox = gr.Textbox(placeholder="Posez votre question en français", scale=4)

    # Fonction pour vider le champ texte
    def reset_input():
        return gr.update(value="")

    # Envoi via Entrée et bouton
    textbox.submit(chat_with_selected_model, [textbox, state, model_selector], [chatbot, state])
    textbox.submit(reset_input, [], [textbox])

demo.launch()
