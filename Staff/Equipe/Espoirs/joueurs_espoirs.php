<?php 
session_start(); 
if (!isset($_SESSION['user_id'])) {
    // L'utilisateur n'est pas connecté, on le redirige
    header("Location: /vizia/accueil.html");
    exit;
}
?>
<?php include('../../../chatbot/chatbot.php'); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Liste des joueurs - ASBH</title>
  <link rel="stylesheet" href="../../../Styles/joueurs.css" />
</head>
<body>

  <!-- Ruban de navigation -->
  <div class="header-ruban">
    <div class="ruban-section">
      <a href="../Crabos/joueurs_crabos.php" class="ruban-link" id="crabos">CRABOS</a>
      <a href="../CadetA/joueurs_cadetA.php" class="ruban-link" id="cadetsA">CADETS A</a>
      <a href="../CadetB/joueurs_cadetB.php" class="ruban-link" id="cadetsB">CADETS B</a>
      <a href="espoirs.php" class="ruban-link active" id="espoirs">ESPOIRS</a>
    </div>
    <a href="espoirs.php" class="btn-retour">Retour à l'équipe</a>
  </div>
  
  <!-- Titre de la page -->
  <h2 class="page-title">Liste des joueurs</h2>

  <!-- Barre de recherche -->
  <div class="search-container">
    <input type="text" class="search-bar" placeholder="Rechercher..." />
    <button class="search-btn">🔍</button>
  </div>


  <!-- Liste des joueurs -->
  <div class="joueurs-container">
    <?php
    require_once '../../../bd.php';

    try {
        $stmt = $pdo->prepare("
          SELECT nom, prenom, id_joueur
          FROM joueur
          JOIN equipe ON joueur.id_equipe = equipe.id_equipe
          WHERE equipe.nom_equipe = :nom_equipe
        ");

        $stmt->execute(['nom_equipe' => 'espoirs']);
        $joueurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($joueurs as $joueur) {
          ?>
          <div class="joueur-card">
            <span class="nom-joueur"><?= htmlspecialchars($joueur['prenom']) . " " . htmlspecialchars($joueur['nom']) ?></span>
            <a href="../../Modification/modif.php?id=<?= $joueur['id_joueur'] ?>" class="btn-formulaire"> Modifier les informations</a>
            <a href="../../../Joueur/Fiche_joueur/performance.php" class="btn-formulaire">Tests et Performance</a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'kine'): ?>
              <a href="../../Formulaire/Medical/formmedical.php?id=<?= $joueur['id_joueur'] ?>&eq=A" class="btn-formulaire">Formulaire médical</a>
            <?php endif; ?>
          </div>
          <?php
        }

    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
    ?>
  </div>

</body>
</html>
>>>>>>> 410cb254b20a5b3d074157af1c0e71edebc76f99
