<?php
require '../../bd.php'; // adapte ce chemin selon ton arborescence

$id_joueur = $_GET['id'] ?? 'demo'; // Valeur temporaire pour test

// Récupérer id_eq et valider
$id_equipe = filter_input(INPUT_GET, 'id_eq', FILTER_VALIDATE_INT);
if ($id_equipe === false) {
    echo "<p>Une erreur est survenue. Redirection...</p>";
    echo "<script>setTimeout(() => window.location.href = '../../accueil_staff.html', 1000);</script>";
    exit;
}

// Récupérer nom_equipe avec id_equipe
$stmt = $pdo->prepare("SELECT nom_equipe FROM equipe WHERE id_equipe = :id");
$stmt->execute(['id' => $id_equipe]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    echo "<p>Équipe introuvable. Redirection...</p>";
    echo "<script>setTimeout(() => window.location.href = '../../accueil_staff.html', 1000);</script>";
    exit;
}

$equipe = strtolower(trim($result['nom_equipe']));

// Déterminer l'URL de retour selon le nom de l'équipe
$url_retour = '';
if ($equipe === 'cadets a') {
    $url_retour = '/vizia/Staff/Equipe/CadetA/cadetA.php';
} elseif ($equipe === 'cadets b') {
    $url_retour = '/vizia/Staff/Equipe/CadetB/cadetB.php';
} elseif ($equipe === 'crabos') {
    $url_retour = '/vizia/Staff/Equipe/crabos/crabos.php';
} elseif ($equipe === 'espoirs') {
    $url_retour = '/vizia/Staff/Equipe/espoirs/espoirs.php';
} else {
    // Par défaut, revenir à l'accueil si le nom d'équipe ne correspond pas à un cas connu
    $url_retour = '../../accueil_staff.html';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Choix du formulaire</title>
  <link rel="stylesheet" href="../../Styles/section.css" />
  <style>
    .btn-retour {
      position: fixed;
      top: 20px;
      right: 20px;
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
  <div>
    <a href="<?= htmlspecialchars($url_retour) ?>" class="btn-retour">
        Retour au choix de formulaire
    </a>
  </div>

  <h2 class="page-title">Réponses aux formulaires</h2>

  <div class="container">
    <div class="option-section">
      <a href="reponse_rpe.php?id_eq=<?= urlencode($id_equipe) ?>" class="btn-option">Réponse au formulaire RPE</a>
      <a href="reponse_wellness.php?id_eq=<?= urlencode($id_equipe) ?>" class="btn-option">Réponse au formulaire Wellness</a>
    </div>
  </div>

</body>
</html>
