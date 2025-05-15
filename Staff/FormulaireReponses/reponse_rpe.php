<?php
// Récupérer l'identifiant du joueur depuis l'URL
$id_joueur = $_GET['id'] ?? 'inconnu';

// Vérifier que le formulaire a bien été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sécuriser les entrées
    $type_entrainement = htmlspecialchars($_POST['type-entrainement'] ?? '');
    $temps_entrainement = htmlspecialchars($_POST['temps-entrainement'] ?? '');
    $difficulte = htmlspecialchars($_POST['difficulte'] ?? '');
    $observations = htmlspecialchars($_POST['observations'] ?? '');
} else {
    // Rediriger ou afficher un message si accès direct
    echo "<p class='error'>Aucune donnée reçue. Veuillez soumettre le formulaire.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Réponse RPE</title>
  <link rel="stylesheet" href="../Styles/rpe.css">
  <style>
    body {
      background-color: #f2f4f8;
      font-family: 'Segoe UI', sans-serif;
      padding: 30px;
      margin: 0;
    }

    .response-container {
      max-width: 700px;
      margin: 0 auto;
      background: #fff;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    }

    h1 {
      text-align: center;
      color: #d4002a;
      margin-bottom: 20px;
    }

    .response-box {
      background-color: #e9f0fb;
      border-left: 5px solid #007bff;
      padding: 20px;
      border-radius: 10px;
      margin-bottom: 20px;
    }

    .response-box p {
      margin: 10px 0;
      font-size: 16px;
      color: #333;
    }

    .btn-retour {
      display: inline-block;
      margin-top: 20px;
      padding: 10px 20px;
      background-color: #007bff;
      color: white;
      text-decoration: none;
      border-radius: 8px;
      transition: background-color 0.3s ease;
    }

    .btn-retour:hover {
      background-color: #0056b3;
    }

    .error {
      color: red;
      text-align: center;
    }
  </style>
</head>
<body>

  <div class="response-container">
    <h1>Réponses au formulaire RPE</h1>
    
    <div class="response-box">
      <p><strong>ID Joueur :</strong> <?= $id_joueur ?></p>
      <p><strong>Type d'entraînement :</strong> <?= $type_entrainement ?></p>
      <p><strong>Temps d'entraînement :</strong> <?= $temps_entrainement ?> minutes</p>
      <p><strong>Difficulté :</strong> <?= $difficulte ?> / 5</p>
      <p><strong>Observations :</strong> <?= nl2br($observations) ?></p>
    </div>

    <a href="formulaires.html" class="btn-retour">Retour à l'accueil</a>
  </div>

</body>
</html>
