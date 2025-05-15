<?php
$id_joueur = $_GET['id'] ?? 'demo'; // Valeur temporaire pour test
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Choix du formulaire</title>
  <link rel="stylesheet" href="../../Styles/section.css" />
  
</head>
<body>

  <h2 class="page-title">Réponses aux formulaires</h2>

  <div class="container">
    <div class="option-section">
      <a href="reponse_rpe.php?id=<?= $id_joueur ?>" class="btn-option">Réponse au formulaire RPE</a>
      <a href="reponse_wellness.php?id=<?= $id_joueur ?>" class="btn-option">Réponse au formulaire Wellness</a>
    </div>
  </div>

</body>
</html>
