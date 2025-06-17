import gradio as gr
from models import build_chat
import pymysql
import re

# Connexion MySQL
def get_connection():
    return pymysql.connect(
        host='localhost',
        user='root',
        password='root',
        database='vizia',
        port=3306,
        charset='utf8mb4',
        cursorclass=pymysql.cursors.DictCursor
    )


# === Schéma de la base de données (copié depuis ta version) ===
table_schema = """
CREATE TABLE `analyse_joueur_match` (
  `id_jm` int(11) NOT NULL,
  `poste` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  `minutes_joues` int(11) DEFAULT NULL,
  `id_joueur` int(11) DEFAULT NULL,
  `id_match` int(11) DEFAULT NULL
);

CREATE TABLE `equipe` (
  `id_equipe` int(11) NOT NULL,
  `nom_equipe` varchar(30) CHARACTER SET utf8 NOT NULL,
  `tranche_age` varchar(30) CHARACTER SET utf8 NOT NULL
);
CREATE TABLE `infos_match` (
  `id_match` int(11) NOT NULL,
  `nom_equipe_adverse` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `lieu_match` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `date_match` date DEFAULT NULL,
  `score_equipe_adverse` int(100) DEFAULT NULL,
  `score_equipe_asbh` int(100) DEFAULT NULL,
  `match_gagnant` tinyint(1) DEFAULT NULL
);


CREATE TABLE `joueur` (
  `id_joueur` int(11) NOT NULL,
  `id_equipe` int(11) DEFAULT NULL,
  `annee` date DEFAULT NULL,
  `nom` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `prenom` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `poste` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `mdp` varchar(300) CHARACTER SET utf8 DEFAULT NULL
);


CREATE TABLE `kine_form` (
  `id_form` int(11) NOT NULL,
  `id_joueur` int(11) DEFAULT NULL,
  `date_kine` date DEFAULT NULL,
  `type_seance` varchar(300) CHARACTER SET utf8 DEFAULT NULL,
  `observations` varchar(300) CHARACTER SET utf8 DEFAULT NULL
);

CREATE TABLE `medical_form` (
  `id_medical` int(11) NOT NULL,
  `id_joueur` int(11) DEFAULT NULL,
  `type_blessure` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `gravite` int(255) DEFAULT NULL,
  `date_blessure` date DEFAULT NULL,
  `recommandation` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `reprise` varchar(500) CHARACTER SET utf8 DEFAULT NULL
);
CREATE TABLE `mental_form` (
  `id_form` int(11) NOT NULL,
  `id_joueur` int(11) DEFAULT NULL,
  `date_form` date DEFAULT NULL,
  `etat_joueur` int(11) DEFAULT NULL,
  `observation` varchar(500) CHARACTER SET utf8 DEFAULT NULL
);

CREATE TABLE `mesure` (
  `id_mesure` int(11) NOT NULL,
  `id_joueur` int(11) NOT NULL,
  `valeur` decimal(5,2) NOT NULL,
  `type_mesure` varchar(55) CHARACTER SET utf8 DEFAULT NULL,
  `date_mesure` date DEFAULT NULL
);

CREATE TABLE `rpe_form` (
  `id_RPE` int(11) NOT NULL,
  `id_joueur` int(11) DEFAULT NULL,
  `date_form` date DEFAULT NULL,
  `type_entrainement` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `temps_entrainement` int(11) DEFAULT NULL,
  `difficulte` int(11) DEFAULT NULL,
  `observations` varchar(500) CHARACTER SET utf8 DEFAULT NULL
);

CREATE TABLE `staff` (
  `id_staff` int(11) NOT NULL,
  `nom` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `prenom` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `role` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `mdp` varchar(255) CHARACTER SET utf8 DEFAULT NULL
);

CREATE TABLE `tests_fonctionnnels` (
  `id_test` int(11) NOT NULL,
  `id_joueur` int(11) NOT NULL,
  `squat_arrache` varchar(11) CHARACTER SET utf8 DEFAULT NULL,
  `iso_leg_curl` varchar(11) CHARACTER SET utf8 DEFAULT NULL,
  `souplesse_chaine_post` varchar(11) CHARACTER SET utf8 DEFAULT NULL,
  `flamant_rose` varchar(11) CHARACTER SET utf8 DEFAULT NULL,
  `souplesse_membres_supérieurs` varchar(11) CHARACTER SET utf8 DEFAULT NULL,
  `date_test` date DEFAULT NULL
);

CREATE TABLE `tests_physiques` (
  `id_test` int(11) NOT NULL,
  `id_joueur` int(11) DEFAULT NULL,
  `type_test` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `date_test` date DEFAULT NULL,
  `mesure_test` float DEFAULT NULL
);
CREATE TABLE `wellness_form` (
  `id_wellness` int(11) NOT NULL,
  `id_joueur` int(11) DEFAULT NULL,
  `date_form` date DEFAULT NULL,
  `sommeil` int(11) DEFAULT NULL,
  `courbatures_haut` int(11) DEFAULT NULL,
  `courbatures_bas` int(11) DEFAULT NULL,
  `humeur` int(11) DEFAULT NULL,
  `observations` varchar(255) CHARACTER SET utf8 DEFAULT NULL
);
"""

key_relationships = """
ALTER TABLE `analyse_joueur_match`
  ADD PRIMARY KEY (`id_jm`),
  ADD KEY `joueur - analyse` (`id_joueur`),
  ADD KEY `match - analyse` (`id_match`);

ALTER TABLE `equipe`
  ADD PRIMARY KEY (`id_equipe`);
ALTER TABLE `infos_match`
  ADD PRIMARY KEY (`id_match`);
ALTER TABLE `joueur`
  ADD PRIMARY KEY (`id_joueur`),
  ADD KEY `joueur - equipe` (`id_equipe`);

ALTER TABLE `kine_form`
  ADD PRIMARY KEY (`id_form`),
  ADD KEY `kinet - joueur` (`id_joueur`);

ALTER TABLE `medical_form`
  ADD PRIMARY KEY (`id_medical`),
  ADD KEY `medical - joueur` (`id_joueur`);

ALTER TABLE `mental_form`
  ADD PRIMARY KEY (`id_form`),
  ADD KEY `mental - joueur` (`id_joueur`);
ALTER TABLE `mesure`
  ADD PRIMARY KEY (`id_mesure`),
  ADD KEY `mesure - joueur` (`id_joueur`);


ALTER TABLE `rpe_form`
  ADD PRIMARY KEY (`id_RPE`),
  ADD KEY `rpe - joueur` (`id_joueur`);

ALTER TABLE `staff`
  ADD PRIMARY KEY (`id_staff`);

ALTER TABLE `tests_fonctionnnels`
  ADD PRIMARY KEY (`id_test`),
  ADD KEY `fonct - joueur` (`id_joueur`);

ALTER TABLE `tests_physiques`
  ADD PRIMARY KEY (`id_test`),
  ADD KEY `phys - joueur` (`id_joueur`);

ALTER TABLE `wellness_form`
  ADD PRIMARY KEY (`id_wellness`),
  ADD KEY `wellness - joueur` (`id_joueur`);
"""

# === PROMPT FINAL ===
prompt = ("""
🧠 Tu es un assistant IA qui répond EXCLUSIVEMENT par une requête SQL complète, valide, sans aucun texte explicatif.

⚠️⚠️ RÈGLES MÉTIER CRITIQUES (À NE JAMAIS VIOLER) ⚠️⚠️
1. ❌ N’UTILISE JAMAIS la table `mesure` pour des tests physiques (ex : "10 m", "yo-yo", "CMJ", etc.)
2. ❌ N’UTILISE JAMAIS la table `tests_physiques` pour des mesures morphologiques (ex : taille, poids, IMG).
3. ❌ NE FAIS JAMAIS de jointure entre `tests_physiques` et `mesure`. C’est strictement INTERDIT.
4. ✅ Tous les tests physiques sont UNIQUEMENT dans `tests_physiques`. 
5. ✅ Toutes les mesures morphologiques sont UNIQUEMENT dans `mesure`.
          4. ✅ Tous les tests physiques sont UNIQUEMENT dans `tests_physiques`, avec :  
   - `type_test` VARCHAR(255), ex: '10 m', 'YOYO', 'CMJ', etc.  
   - `mesure_test` FLOAT, valeur numérique du test.  

5. ✅ Toutes les mesures morphologiques sont UNIQUEMENT dans `mesure`, avec :  
   - `type_mesure` VARCHAR(55), ex: 'taille', 'poids', 'img'  
   - `valeur` DECIMAL(5,2)

6. ⚠️ Pour les tests physiques de type temps (ex: '10 m', '20 m', 'YOYO'), le meilleur score est la plus petite valeur (ORDER BY `mesure_test` ASC).  
Pour les autres tests physiques (ex: 'CMJ', 'SQUAT'), le meilleur score est la plus grande valeur (ORDER BY `mesure_test` DESC).

📘 EXEMPLE D’UTILISATION :  
- Trouver le joueur le plus rapide au test '10 m' :  
```sql
SELECT j.nom, j.prenom
FROM joueur AS j
JOIN tests_physiques AS tp ON j.id_joueur = tp.id_joueur
WHERE tp.type_test = '10 m'
ORDER BY tp.mesure_test ASC
LIMIT 1;


Si tu enfreins ces règles, ta requête est considérée comme FAUSSE.

🎯 LISTE DES TYPES DE DONNÉES
- `mesure` contient : 'taille', 'poids', 'img'
- `tests_physiques` contient : 'CMJ', 'DC', 'SQUAT', 'TP', 'BROADJUMP', '10 m', '20 m', 'BRONCO', 'RFU', 'YOYO', 'PESEE'

📘 INSTRUCTIONS GÉNÉRALES :
- Utilise toujours les bons noms de table et colonnes.
- Toujours utiliser des alias pour les tables (`j`, `tp`, `m`, etc.).
- Ne joins `equipe` que via `joueur`, jamais directement.
- Ne sélectionne jamais de colonnes sensibles comme `mdp`.
- Ne jamais générer plus d’une requête.
- Ne jamais ajouter d’explications ni commentaires.
- Si la question est floue, déduis la requête la plus probable.

📎 FILTRER PAR ÉQUIPE (ex : "joueur de l’équipe crabos") :
- Commence par `joueur`, puis joins vers `equipe` (`joueur.id_equipe = equipe.id_equipe`)
- Pour `mesure` : joins aussi `mesure.id_joueur = joueur.id_joueur`
- Pour `tests_physiques` : joins `tests_physiques.id_joueur = joueur.id_joueur`

✅ EXEMPLES :
- “Quel est le joueur le plus grand ?” → utilise `mesure`, filtre `type_mesure = 'taille'`
- “Quels sont les meilleurs résultats au CMJ ?” → utilise `tests_physiques`, filtre `type_test = 'CMJ'`
- “Quel est le joueur le plus rapide de l’équipe crabos ?” → `tests_physiques` + `joueur` + `equipe`, jamais `mesure`.

📎 SCHÉMA DE LA BASE :
{table_schema}

🔑 RELATIONS CLÉS :
{key_relationships}

📝 Répond UNIQUEMENT par UNE REQUÊTE SQL. Aucune explication.
"""
)



def extract_sql_only(text):
    # Extrait uniquement la requête SQL d’un texte qui pourrait contenir des explications
    match = re.search(r"(SELECT)[\s\S]+?;", text, re.IGNORECASE)
    return match.group(0) if match else text

# Fonction pour générer uniquement la requête SQL

def generate_sql_query(message, history, model_name):
    history, _ = build_chat(model_name=model_name, message=message, history=history, prompt=prompt)
    raw_response = history[-1][1]
    sql_query = extract_sql_only(raw_response)

    # Sécurité : bloquer dès la génération
    forbidden_keywords = ["drop", "delete", "update", "insert", "alter", "truncate"]
    if any(keyword in sql_query.lower() for keyword in forbidden_keywords):
        sql_query = "-- Requête interdite générée par le LLM"
    
    return sql_query, history


# Exécution de la requête SQL
def execute_sql(sql_query):
    # Sécurité : bloquer les requêtes destructives
    forbidden_keywords = ["drop", "delete", "update", "insert", "alter", "truncate"]
    if any(keyword in sql_query.lower() for keyword in forbidden_keywords):
        return None, "❌ Requête SQL interdite : les opérations de modification ou suppression sont bloquées."

    try:
        conn = get_connection()
        cursor = conn.cursor()
        cursor.execute(sql_query)
        result = cursor.fetchall()
        columns = [desc[0] for desc in cursor.description]
        cursor.close()
        conn.close()
        return columns, result
    except Exception as e:
        return None, f"❌ Erreur SQL : {e}"




# Pipeline complet : question → SQL → exécution → affichage
def full_pipeline(message, history, model_choice):
    sql_query, updated_history = generate_sql_query(message, history, model_choice)
    columns, result = execute_sql(sql_query)

    if isinstance(result, str):  # erreur SQL
        return updated_history, sql_query, "", result

    if not result:
        texte_resultat = "Aucun résultat trouvé."
    else:
        rows = [[row[col] for col in columns] for row in result]
        if len(rows) == 1 and 'nom' in columns and 'prenom' in columns:
            nom = rows[0][columns.index('nom')]
            prenom = rows[0][columns.index('prenom')]
            texte_resultat = f"{prenom} {nom} est le joueur correspondant au critère."
        else:
            texte_resultat = "Résultats trouvés : " + ", ".join(str(rows[0]))

    new_history = []
    for q, r, *rest in updated_history:
        if (q, r) == updated_history[-1]:
            new_history.append((q, texte_resultat))
        else:
            new_history.append((q, r))

    return new_history, sql_query, texte_resultat, ""







def append_history(history, question, answer):
    # Si history contient des tuples, les convertir en str
    if history and isinstance(history[0], tuple):
        history = [f"Q: {q}\nR: {r}" for q, r in history]
    history.append(f"Q: {question}\nR: {answer}\n")
    return history, "\n".join(history)



def handle_submission_simple(question, history, model_choice):
    history_with_text, sql_query, result_text, error_message = full_pipeline(question, history, model_choice)

    if error_message:
        answer = f"<span style='color:red;'>Erreur : {error_message}</span>"
    else:
        answer = result_text  # <-- ici tu affiches le résultat, pas la requête SQL

    chat_html = ""
    for q, r, *rest in history_with_text:
        chat_html += f"""
        <div style="display:flex; justify-content:flex-end; margin:5px 0 15px 0;">
            <div style="max-width:70%; padding:10px 15px; border-radius:20px; background:#CC0A0A; color:white;">
                {q}
            </div>
        </div>
        <div style="display:flex; margin:10px 0;">
            <div style="max-width:70%; padding:10px 15px; border-radius:20px; background:#e5e5ea; color:black;">
                {r}
            </div>
        </div>
        """

    # Si la dernière question n'est pas dans l'historique, on l'ajoute avec la réponse
    if not history_with_text or (history_with_text and history_with_text[-1][0] != question):
        chat_html += f"""
        <div style="display:flex; justify-content:flex-end; margin:5px 0 15px 0;">
            <div style="max-width:70%; padding:10px 15px; border-radius:20px; background:#CC0A0A; color:white;">
                {question}
            </div>
        </div>
        <div style="display:flex; margin:10px 0;">
            <div style="max-width:70%; padding:10px 15px; border-radius:20px; background:#e5e5ea; color:black;">
                {answer}  <!-- ici on affiche la réponse du résultat SQL -->
            </div>
        </div>
        """

    return history_with_text, chat_html



with gr.Blocks(css="""
  @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@700&display=swap');

  body {
      background-color: #0a1e3a;
      color: white;
      font-family: 'Segoe UI', sans-serif;
  }

  .gradio-container {
      background-color: #0a1e3a;
  }

  h1, h2, h3, h4, h5, h6 {
      color: white; /* Met tous les titres en blanc */
      font-family: 'Orbitron', sans-serif; /* Police innovante pour les titres */
  }

  input, textarea, select, button {
      border-radius: 8px;
      background-color: white;
      color: #0a1e3a;
  }

  button {
      background-color: #e30613;
      color: white;
  }

  button:hover {
      background-color: #ba0410;
  }
  /* Conteneur de la ligne d'input */
  #input-row {
      display: flex;
      align-items: center; /* Aligne verticalement */
      gap: 8px;
  }

  /* Style du bouton (div gradio) */
  #send_btn {
      width: 42px;
      min-width: 42px;
      max-width: 42px;
      padding: 0;
      margin: 0;
  }

  /* Style du vrai bouton HTML */
  #send_btn button {
      width: 100% !important;
      height: 38px !important;
      padding: 0 !important;
      font-size: 18px;
      border-radius: 8px;
      background-color: #e30613;
      color: white;
      font-weight: bold;
      line-height: 1;
  }

  /* Responsive ajusté */
  @media screen and (max-width: 600px) {
      #input-row {
          flex-wrap: nowrap;
      }
  }



""") as demo:
    gr.Markdown("### 🏉 Assistant VIZIABOT")
    gr.Markdown(
        "<p style='font-size: 0.9em;color: white; /* Texte blanc */margin-top: 10px;margin-bottom: 20px;;'>*Cet assistant est encore en phase d'amélioration et peut se tromper dans ses réponses.*</p>",
    )



    model_selector = gr.Dropdown(["codellama", "tinyllama", "gemma", "phi"], value="codellama", label="Modèle LLM", visible=False,)

    state = gr.State([])

    

    history_display = gr.HTML(label="Historique de la conversation")
    
    textbox = gr.Textbox(
        label="Posez votre question",
        placeholder="Ex : Quel est le joueur le plus rapide ?",
        scale=9,
        submit_btn="➤"
    )

    textbox.submit(
        fn=handle_submission_simple,
        inputs=[textbox, state, model_selector],
        outputs=[state, history_display]
    )

    # Pour vider la textbox après submit
    def reset_input():
        return gr.update(value="")
    textbox.submit(reset_input, [], [textbox])
demo.launch(server_name="127.0.0.1", server_port=7860)