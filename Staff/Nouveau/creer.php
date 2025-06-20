<?php include('../../chatbot/chatbot.php'); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un compte</title>
    <link rel="stylesheet" href="../../Styles/nouveau.css" type="text/css" media="screen" />
    <style>
        h1 {
            margin-top: 60px;
            font-family: 'Bebas Neue', sans-serif; /* Police qui évoque l'esprit sportif */
            font-size: 3em; /* Taille plus grande pour mettre en avant le titre */
            text-align: center;
            color: #fff; /* Texte en blanc pour contraster avec un fond sombre */
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.7); /* Ombre portée pour donner de la profondeur au texte */
            text-transform: uppercase; /* Mettre en majuscules */
            letter-spacing: 0.1em; /* Espacement des lettres pour un effet plus sport */
        }

        p {
            text-align: center;
            color: #fff; /* Texte en blanc pour le rendre plus visible */
            font-size: 1.5em; /* Taille de police plus grande pour le texte */
            margin-top: 10px;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5); /* Ombre légère pour mieux faire ressortir le texte */
        }

        .form-container {
            background-color: rgba(255, 255, 255, 0.8); /* Fond blanc avec une opacité pour que le texte ressorte */
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 80vw; /* Largeur en pourcentage de la largeur de l'écran */
            max-width: 400px; /* Limite la largeur à 400px sur les grands écrans */
            margin: 50px auto;
            padding: 20px;
            text-align: center;
        }

        .form-container a {
            font-size: 0.8em;
            color: #fff; /* Couleur du lien en rapport avec la marque ASBH */
            text-decoration: none;
            display: block;
            margin: 15px 0;
            padding: 10px;
            background-color: #CC0A0A; /* Fond rouge ASBH */
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .form-container a:hover {
            background-color: #990808; /* Effet au survol pour donner un retour visuel */
        }

        /* ==================== Style du bouton retour ==================== */
        .header {
            width: 100%;
            display: flex;
            justify-content: flex-end; /* Pousse le bouton vers la droite */
            padding: 10px 20px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: transparent;
            z-index: 10;
        }

        .btn-retour {
            background-color: #CC0A0A;
            color: white;
            padding: 10px 20px;
            text-decoration: none;  /* Empêche le soulignement du lien */
            border-radius: 5px;
            transition: background-color 0.3s ease, transform 0.3s ease;
            font-size: 1rem;
            margin-right: 4vw;
        }

        .btn-retour:hover {
            background-color: #d93e00;
            transform: scale(1.05);
            text-decoration: none; 
        }

        /* Media Queries pour les appareils mobiles */
        @media (max-width: 768px) {
            h1 {
                font-size: 2.2em; /* Réduire la taille du titre sur les petits écrans */
            }

            .form-container {
                width: 90vw; /* Ajuster la largeur pour les petits écrans */
                padding: 15px; /* Réduire le padding pour optimiser l'espace */
            }

            p {
                font-size: 1.2em; /* Réduire la taille du texte sur les petits écrans */
            }

            .form-container a {
                font-size: 1em; /* Réduire la taille des liens sur les petits écrans */
            }

            .btn-retour {
                padding: 8px 16px; /* Réduire la taille du bouton sur mobile */
                font-size: 0.9rem; /* Ajuster la taille de la police sur mobile */
            }
        }

        @media (max-width: 480px) {
            h1 {
                font-size: 1.8em; /* Réduire encore la taille du titre sur les très petits écrans */
            }

            .form-container {
                width: 95vw; /* Prendre plus de largeur disponible sur les très petits écrans */
                padding: 10px; /* Réduire encore plus le padding */
            }

            p {
                font-size: 1em; /* Réduire la taille du texte */
            }

            .form-container a {
                font-size: 0.8em; /* Réduire la taille des liens */
                padding: 8px; /* Réduire le padding des boutons */
            }

            .btn-retour {
                padding: 6px 12px; /* Encore plus petit pour le bouton */
                font-size: 0.8rem; /* Réduire la taille de la police sur très petit écran */
            }
        }
    </style>
</head>
<body>

<h1>Bienvenue sur la plateforme VIZIA</h1>
<p>Veuillez choisir un type de compte à créer :</p>

<!-- Déconnexion -->
<div class="header">
    <a href="../accueil_staff.php" class="btn-retour">Retour à l'accueil</a>
</div>

<div class="form-container">
    <p>
        <a href='joueur.php'>Créer un compte pour Joueur</a>
    </p>
    <p>
        <a href='staff.php'>Créer un compte pour Staff</a>
    </p>
    <p>
        <a href='valider_invalider.php'>Valider / Invalider un compte</a>
    </p>
    <p>
        <a href='effacer.php'>Effacer un compte</a>
    </p>
</div>

</body>
</html>
