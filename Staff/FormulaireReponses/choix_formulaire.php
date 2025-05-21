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
  <?php 
  $id_equipe = filter_input(INPUT_GET, 'id_eq', FILTER_VALIDATE_INT);    
  if ($id_equipe === false) {
    echo "<p>Une erreur est survenue. Redirection...</p>";
    echo "<script>setTimeout(() => window.location.href = '../../accueil_staff.html', 1000);</script>";
    exit;
  }   
  ?>

  <h2 class="page-title">Réponses aux formulaires</h2>

  <div class="container">
    <div class="option-section">
      <a href="reponse_rpe.php?id_eq=<?= $id_equipe ?>" class="btn-option">Réponse au formulaire RPE</a>
      <a href="reponse_wellness.php?id_eq=<?= $id_equipe ?>" class="btn-option">Réponse au formulaire Wellness</a>
    </div>
  </div>

</body>
</html>
