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
    <script src="staff.js"></script>
    <style>
        /* Tu peux ajouter ceci directement dans ton CSS existant */
        .supprimer {
            background-color: #eee;
            border: none;
            color: #cc0a0a;
            font-weight: bold;
            float: right;
            cursor: pointer;
            font-size: 1em;
            margin-bottom: 1em;
        }

        .ajouter {
            background-color: #1D1442;
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
            color: #1D1442;
            text-align: center;
        }

        .error-message{
            color: #cc0a0a;
            font-size: 1rem;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="../accueil_staff.html" class="btn-retour">Retour à l'accueil</a>
    </div>
    <h1>Création de Compte - Staff</h1>

    <form>
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <div id="personnes">
            <!-- Une première personne affichée par défaut -->
            <div class="form-container personne">
                <h2>Personne 1</h2>
                <p>
                    <label>Poste :</label>
                    <select name="poste">
                        <option value="" disabled selected>Sélectionner son rôle</option>
                        <option value="coach">Coach</option>
                        <option value="manager">Manager</option>
                        <option value="directeur">Directeur</option>
                        <option value="pp">Prépa physique</option>
                        <option value="kine">Kiné</option>
                        <option value="Analyste vidéo">Analyste vidéo</option>
                        <option value="admin">Corps admin</option>
                        <option value="pm">Prépa mental</option>
                    </select>
                    <span class="error-message"></span>
                </p>
                <p>
                    <label>Nom :</label>
                    <input type="text" name="nom" ><span class="error-message"></span>
                </p>
                <p>
                    <label>Prénom :</label>
                    <input type="text" name="prénom"><span class="error-message"></span>
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
