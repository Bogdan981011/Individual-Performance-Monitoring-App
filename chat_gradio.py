import gradio as gr
from models import *

# General prompt building
table_schema = """
CREATE TABLE `analyse_joueur_match` (
  `id_jm` int(11) NOT NULL,
  `poste` varchar(30) NOT NULL,
  `minutes_joues` int(11) NOT NULL,
  `id_joueur` int(11) NOT NULL,
  `id_match` int(11) NOT NULL,
  `id_staff` int(11) NOT NULL
)

CREATE TABLE `equipe` (
  `id_equipe` int(11) NOT NULL,
  `nom_equipe` varchar(30) NOT NULL,
  `tranche_age` varchar(30) NOT NULL
)

CREATE TABLE `infos_match` (
  `id_match` int(11) NOT NULL,
  `nom_equipe_adverse` varchar(100) NOT NULL,
  `lieu_match` varchar(100) NOT NULL,
  `date_match` datetime(6) NOT NULL,
  `score_equipe_adverse` int(100) NOT NULL,
  `score_equipe_asbh` int(100) NOT NULL,
  `match_gagnant` varchar(100) NOT NULL
)

CREATE TABLE `joueur` (
  `id_joueur` int(11) NOT NULL,
  `id_equipe` int(11) NOT NULL,
  `annee` int(11) NOT NULL,
  `nom` varchar(30) NOT NULL,
  `prenom` varchar(30) NOT NULL,
  `poste` varchar(30) NOT NULL,
  `email` varchar(30) NOT NULL,
  `mdp` varchar(300) NOT NULL
)

CREATE TABLE `kinet_form` (
  `id_form` int(11) NOT NULL,
  `id_joueur` int(11) NOT NULL,
  `id_staff` int(11) NOT NULL,
  `date_kinet` datetime(6) NOT NULL,
  `type_seance` varchar(300) NOT NULL,
  `observations` varchar(300) NOT NULL
)

CREATE TABLE `medical_form` (
  `id_medical` int(11) NOT NULL,
  `id_joueur` int(11) NOT NULL,
  `id_staff` int(11) NOT NULL,
  `type_blessure` varchar(255) NOT NULL,
  `gravite_blessure` int(255) NOT NULL,
  `date_blessure` datetime NOT NULL,
  `observations` varchar(255) NOT NULL
)

CREATE TABLE `mental_form` (
  `id_form` int(11) NOT NULL,
  `id_staff` int(11) NOT NULL,
  `id_joueur` int(11) NOT NULL,
  `date_form` int(6) NOT NULL,
  `etat_joueur` int(11) NOT NULL,
  `obser` int(11) NOT NULL
)

CREATE TABLE `mesure` (
  `id_mesure` int(11) NOT NULL,
  `id_joueur` int(11) NOT NULL,
  `valeur` float NOT NULL,
  `update_date` datetime(6) NOT NULL,
  `type_mesure` varchar(55) DEFAULT NULL
)

CREATE TABLE `pdf` (
  `id_pdf` int(11) NOT NULL,
  `id_joueur` int(11) NOT NULL,
  `id_staff` int(11) NOT NULL,
  `type_pdf` varchar(50) NOT NULL,
  `lien_pdf` varchar(255) NOT NULL
)

CREATE TABLE `rpe_form` (
  `id_rpe` int(11) NOT NULL,
  `id_joueur` int(11) NOT NULL,
  `id_staff` int(11) NOT NULL,
  `date_form` datetime(6) NOT NULL,
  `type_entrainement` varchar(100) NOT NULL,
  `temps_entrainement` int(11) NOT NULL,
  `difficulte` int(11) NOT NULL,
  `observations` varchar(255) NOT NULL
)

CREATE TABLE `staff` (
  `id_staff` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `mobile` varchar(100) NOT NULL,
  `role` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mdp` varchar(255) NOT NULL,
  `admin_depart` varchar(100) NOT NULL
)

CREATE TABLE `tests_fonctionnnels` (
  `id_test` int(11) NOT NULL,
  `id_joueur` int(11) NOT NULL,
  `id_staff` int(11) NOT NULL,
  `squat_arrache` varchar(11) NOT NULL,
  `iso_leg_curl` varchar(11) NOT NULL,
  `souplesse_chaine_post` varchar(11) NOT NULL,
  `flamant_rose` varchar(11) NOT NULL,
  `souplesse_membres_supérieurs` varchar(11) NOT NULL,
  `date_test` datetime DEFAULT NULL
)

CREATE TABLE `tests_physiques` (
  `id_test` int(11) NOT NULL,
  `id_joueur` int(11) NOT NULL,
  `id_staff` int(11) NOT NULL,
  `type_test` varchar(255) NOT NULL,
  `date_test` datetime NOT NULL,
  `mesure_test` int(11) NOT NULL
)

CREATE TABLE `wellness_form` (
  `id_wellness` int(11) NOT NULL,
  `id_joueur` int(11) NOT NULL,
  `date_form` datetime NOT NULL,
  `sommeil` int(11) NOT NULL,
  `courbatures_haut` int(11) NOT NULL,
  `courbatures_bas` int(11) NOT NULL,
  `hummeur_score` int(11) NOT NULL,
  `observations` varchar(255) NOT NULL
)
"""

key_relationships = """
ALTER TABLE `analyse_joueur_match`
  ADD PRIMARY KEY (`id_jm`),
  ADD KEY `joueur - analyse` (`id_joueur`),
  ADD KEY `staff - analyse` (`id_staff`),
  ADD KEY `match - analyse` (`id_match`);

ALTER TABLE `equipe`
  ADD PRIMARY KEY (`id_equipe`);

ALTER TABLE `infos_match`
  ADD PRIMARY KEY (`id_match`);

ALTER TABLE `joueur`
  ADD PRIMARY KEY (`id_joueur`),
  ADD KEY `joueur - equipe` (`id_equipe`);

ALTER TABLE `kinet_form`
  ADD PRIMARY KEY (`id_form`),
  ADD KEY `kinet - joueur` (`id_joueur`),
  ADD KEY `kinet - staff` (`id_staff`);

ALTER TABLE `medical_form`
  ADD PRIMARY KEY (`id_medical`),
  ADD KEY `medical - joueur` (`id_joueur`),
  ADD KEY `medical - staff` (`id_staff`);

ALTER TABLE `mental_form`
  ADD KEY `mental - joueur` (`id_joueur`),
  ADD KEY `mental - staff` (`id_staff`);

ALTER TABLE `mesure`
  ADD PRIMARY KEY (`id_mesure`),
  ADD KEY `mesure - joueur` (`id_joueur`);

ALTER TABLE `pdf`
  ADD PRIMARY KEY (`id_pdf`);

ALTER TABLE `rpe_form`
  ADD PRIMARY KEY (`id_rpe`),
  ADD KEY `rpe - joueur` (`id_joueur`),
  ADD KEY `rpe - staff` (`id_staff`);

ALTER TABLE `staff`
  ADD PRIMARY KEY (`id_staff`);

ALTER TABLE `tests_fonctionnnels`
  ADD PRIMARY KEY (`id_test`),
  ADD KEY `fonct - joueur` (`id_joueur`),
  ADD KEY `fonct - staff` (`id_staff`);

ALTER TABLE `tests_physiques`
  ADD PRIMARY KEY (`id_test`),
  ADD KEY `phys - joueur` (`id_joueur`),
  ADD KEY `phys - staff` (`id_staff`);

ALTER TABLE `wellness_form`
  ADD PRIMARY KEY (`id_wellness`),
  ADD KEY `wellness - joueur` (`id_joueur`);
"""

prompt = (
    "You are a helpful AI assistant that ONLY responds with SQL queries. "
    "The user will describe their request in French. "
    "You must return ONLY ONE SINGLE, VALID, and STRICT SQL QUERY. "
    "Do NOT explain your answer. "
    "Do NOT add any conditions, filters, JOINs, or clauses that are not explicitly requested by the user.\n\n"
    "The database schema is:\n\n"
    f"{table_schema}\n\n"
    "The database structure (primary and foreign key relationships) is as follows:\n\n"
    f"{key_relationships}\n\n"
)


"""
============================
    -- DB CONNEXION --
============================
"""

# code 

"""
============================
    -- Gradio Chat -- 
============================
"""

# Router function to select the right model
def chat_with_selected_model(message, history, model_choice):
    return build_chat(model_name=model_choice, message=message, history=history, prompt=prompt)


# Gradio Interface with model selection
with gr.Blocks() as demo:
    gr.Markdown("### 💬 SQL Assistant powered by Ollama")

    model_selector = gr.Dropdown(
        ["codellama", "tinyllama", "gemma", "phi"],
        value="codellama",
        label="🧠 Select LLM Model",
        interactive=True
    )

    chatbot = gr.Chatbot(label="SQL Generator")
    state = gr.State([])

    textbox = gr.Textbox(label="Posez votre question en français", placeholder="Ex: Quels joueurs ont été blessés en janvier ?")

    # On submit: call the router with selected model
    textbox.submit(chat_with_selected_model, [textbox, state, model_selector], [chatbot, state])

demo.launch()
