<?php 
session_start(); 
if (!isset($_SESSION['user_id'])) {
    // L'utilisateur n'est pas connecté, on le redirige
    header("Location: /vizia/accueil.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../Styles/nouveau.css" type="text/css" media="screen" />
    <title>Nouveau</title>
    <script src="joueur.js"></script>
    <script>
        // Fonction pour ajouter une section "personne"
        function ajouterPersonne() {
            const container = document.getElementById('personnes');
            const index = container.children.length + 1;
            const personne = document.createElement('div');
            personne.className = 'form-container personne';

            personne.innerHTML = `
                <button type="button" class="supprimer" onclick="this.parentElement.remove()">− Supprimer</button>
                <h2>Personne ${index}</h2>
                <p>
                    <label>Equipe :</label>
                    <select name="n[]">
                        <option value="crabos">CRABOS</option>
                        <option value="cadetsa">CADETS A</option>
                        <option value="cadetsb">CADETS B</option>
                        <option value="espoirs">ESPOIRS</option>
                    </select>
                </p>
                <p>
                    <label>Nom :</label>
                    <input type="text" name="p[]">
                </p>
                
                <p>
                    <label>Prénom :</label>
                    <input type="text" name="p[]">
                </p>
                <p>
                    <label>Adresse e-mail :</label>
                    <input type="email" name="mail[]">
                </p>
        
            `;
            container.appendChild(personne);
        }
    </script>
    <style>
        /* Tu peux ajouter ceci directement dans ton CSS existant */
        .supprimer {
            background-color: #eee;
            border: none;
            color: #190C63;
            font-weight: bold;
            float: right;
            cursor: pointer;
            font-size: 1em;
            margin-bottom: 1em;
        }

        .ajouter {
            background-color: #190C63;
            color: white;
            padding: 1vh;
            border-radius: 5px;
            font-size: 1em;
            margin: 1vh auto;
            display: block;
            cursor: pointer;
            border: none;
        }

        h2 {
            font-size: 1.2em;
            color: #190C63;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="../accueil_staff.html" class="btn-retour">Retour à l'accueil</a>
    </div>
    <h1>Création de Compte - Staff</h1> 
    <form method="get" action="enregistrement.php" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <div id="personnes">
            <!-- Une première personne affichée par défaut -->
            <div class="form-container personne">
                <h2>Personne 1</h2>
                <p>
                    <label>Equipe :</label>
                    <select name="equipe">
                        <option value="" disabled selected>Sélectionner son équipe</option>
                        <option value="crabos">CRABOS</option>
                        <option value="cadets a">CADETS A</option>
                        <option value="cadets b">CADETS B</option>
                        <option value="espoirs">ESPOIRS</option>
                    </select>
                    <span class="error-message"></span>
                </p>
                <p>
                    <label>Nom :</label>
                    <input type="text" name="nom"><span class="error-message"></span>
                </p>
                <p>
                    <label>Prénom :</label>
                    <input type="text" name="prenom"><span class="error-message"></span>
                </p>
                <p>
                    <label>Adresse e-mail :</label>
                    <input type="email" name="mail"><span class="error-message"></span>
                </p>
                <p>                    
                    <label>Mot de passe provisoire :</label>
                    <input type="text" name="mdp"><span class="error-message"></span>
                </p>
            </div>
        </div>

        <!-- Bouton pour ajouter une personne -->
        <button type="button" class="ajouter">+ Ajouter une personne</button>

        <!-- Bouton de soumission -->
        <div class="form-container">
            <input type="submit" value="Envoyer">
        </div>
    </form>
</body>
</html>
