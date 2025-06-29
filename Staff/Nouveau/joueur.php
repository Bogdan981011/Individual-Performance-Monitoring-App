<?php 
session_start(); 
if (!isset($_SESSION['user_id'])) {
    // L'utilisateur n'est pas connecté, on le redirige
    header("Location: /vizia/accueil.html");
    exit;
}
?>
<?php include('../../chatbot/chatbot.php'); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="stylesheet" href="../../Styles/nouveau.css" type="text/css" media="screen" />
    <title>Nouveau</title>
    <script src="joueur.js"></script>
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
    <h2>Création de Compte - Joueur</h2> 
    <form>
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
                    <label for="annee">Date de naissance :</label>
                    <input type="date" name="annee" id="annee"><span class="error-message"></span>
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
                    <label for="poste">Poste :</label>
                    <input type="text" name="poste" id="poste"><span class="error-message"></span>
                </p>
                <p>
                    <label>Adresse e-mail :</label>
                    <input type="email" name="mail"><span class="error-message"></span>
                </p>
                <p>                    
                    <label>Mot de passe provisoire :</label>
                    <input type="text" name="mdp"><span class="error-message"></span>
                </p>
                <p>
                    <label>Photo :</label>
                    <input type="file" name="photo" accept="image/png,image/jpeg,image/webp">
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
