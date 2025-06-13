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
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
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
        @media (max-width: 768px) {
            h2 {
                margin-top: 80px; /* Réduire la marge pour les petits écrans */
            }

            .form-container {
                margin-top: 10px; /* Réduire la marge supérieure pour le container */
                padding: 10px; /* Ajout d'un padding pour plus de confort */
            }

            .ajouter {
                font-size: 0.9em; /* Réduire la taille du bouton sur les petits écrans */
                padding: 0.8vh; /* Ajuster la taille du bouton */
            }

            .supprimer {
                font-size: 0.9em; /* Réduire la taille du bouton supprimer */
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="../accueil_staff.php" class="btn-retour">Retour à l'accueil</a>
    </div>
    <h2>Création de Compte - Staff</h2>

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
                        <option value="video">Analyste vidéo</option>
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
