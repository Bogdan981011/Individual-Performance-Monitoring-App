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
      <a href="cadetB.php" class="ruban-link active" id="cadetsB">CADETS B</a>
      <a href="../Espoirs/joueurs_espoirs.php" class="ruban-link" id="espoirs">ESPOIRS</a>
    </div>
    <a href="../../accueil_staff.html" class="btn-retour">Retour à la section</a>
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

        $stmt->execute(['nom_equipe' => 'Cadets b']);
        $joueurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($joueurs as $joueur) {
          ?>
          <div class="joueur-card">
            <span class="nom-joueur"><?= htmlspecialchars($joueur['prenom']) . " " . htmlspecialchars($joueur['nom']) ?></span>
            <a href="../../../Joueur/Fiche_joueur/performance.php" class="btn-formulaire">Tests et Performance</a>
            <a href="../../Formulaire/Medical/formmedical.php?id=<?= $joueur['id_joueur'] ?>" class="btn-formulaire">Formulaire médical</a>
          </div>
          <?php
        }

    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
    ?>
    <!-- Ajouter d'autres joueurs en dupliquant ce bloc -->
  </div>

</body>
</html>
