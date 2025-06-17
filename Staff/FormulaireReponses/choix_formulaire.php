<?php

  session_start(); 
  if (!isset($_SESSION['user_id'])) {
    // L'utilisateur n'est pas connecté, on le redirige
    header("Location: /vizia/accueil.html");
    exit;
  }

  require_once '../../bd.php'; 

  $id_joueur = $_GET['id']; 
   
  $id_equipe = filter_input(INPUT_GET, 'id_eq', FILTER_VALIDATE_INT);
  if ($id_equipe === false) {
    echo "<p>Une erreur est survenue. Redirection...</p>";
    echo "<script>setTimeout(() => window.location.href = '../../accueil_staff.php', 1000);</script>";
    exit;
  }    

  // Récupère nom de l'équipe actuelle
  $stmt = $pdo->prepare("SELECT nom_equipe FROM equipe WHERE id_equipe = :id");
  $stmt->execute(['id' => $id_equipe]);
  $result = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$result) {
    echo "<p>Équipe introuvable. Redirection...</p>";
    echo "<script>setTimeout(() => window.location.href = '../../accueil_staff.php', 1000);</script>";
    exit;
  }

  $nom = strtoupper(trim($result['nom_equipe']));
  $equipe = strtolower(trim($result['nom_equipe']));

  // Déterminer l'URL de retour selon l'équipe
  switch ($equipe) {
    case 'cadets a':
      $url_retour = '/vizia/Staff/Equipe/CadetA/cadetA.php';
      break;
    case 'cadets b':
      $url_retour = '/vizia/Staff/Equipe/CadetB/cadetB.php';
      break;
    case 'crabos':
      $url_retour = '/vizia/Staff/Equipe/crabos/crabos.php';
      break;
    case 'espoirs':
      $url_retour = '/vizia/Staff/Equipe/espoirs/espoirs.php';
      break;
    default:
      $url_retour = '../../accueil_staff.php';
  }
?>
<?php include('../../chatbot/chatbot.php'); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Choix du formulaire</title>
  <link rel="stylesheet" href="../../Styles/section.css" />
  <style>
    .btn-retour {
      padding: 10px 15px;
      background-color: #CC0A0A;
      color: white;
      text-decoration: none;
      border-radius: 5px;
      font-weight: bold;
      z-index: 1000;
    }
    .btn-retour:hover {
      background-color: darkred;
    }
  </style>
</head>
<body>
  <?php 
  $id_equipe = filter_input(INPUT_GET, 'id_eq', FILTER_VALIDATE_INT);
  if ($id_equipe === false) {
    echo "<p>Une erreur est survenue. Redirection...</p>";
    echo "<script>setTimeout(() => window.location.href = '../../accueil_staff.php', 1000);</script>";
    exit;
  }    
  $stmt = $pdo->prepare("SELECT nom_equipe FROM equipe WHERE id_equipe =:id");
  $stmt -> execute(['id' => $id_equipe]);
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  $nom = strtoupper(trim($result['nom_equipe']));

  // Récupère toutes les équipes nécessaires
  $stmt = $pdo->query("SELECT id_equipe, nom_equipe FROM equipe");
  $equipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

  ?>
  <div class="header-ruban">
    <div class="ruban-section">
        <?php foreach ($equipes as $equipe): 
            $nomEquipe = strtoupper($equipe['nom_equipe']);
            $activeClass = ($nom === $nomEquipe) ? 'active' : '';
        ?>
            <a href="choix_formulaire.php?id_eq=<?= $equipe['id_equipe'] ?>"
            class="ruban-link <?= $activeClass ?>"
            id="<?= strtolower(str_replace(' ', '', $nomEquipe)) ?>">
            <?= $nomEquipe ?>
            </a>
        <?php endforeach; ?>
        <a href="<?= htmlspecialchars($url_retour) ?>" class="btn-retour"> Retour</a>
    </div>
  </div>

  <div class="container">
      <div class="logo-section">
        <img src="../../Images/logo.svg" alt="Logo ASBH" class="logo central-logo">
  </div>

  <div class="option-section">
      <a href="reponse_rpe.php?id_eq=<?= urlencode($id_equipe) ?>" class="btn-option">Réponse au formulaire RPE</a>
      <a href="reponse_wellness.php?id_eq=<?= urlencode($id_equipe) ?>" class="btn-option">Réponse au formulaire Wellness</a>
  </div>

</body>
</html>
