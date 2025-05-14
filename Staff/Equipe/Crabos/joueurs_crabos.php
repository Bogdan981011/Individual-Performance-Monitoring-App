<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Liste des joueurs - ASBH</title>
  <link rel="stylesheet" href="../../../Styles/joueurs.css" />
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>

  <!-- Ruban de navigation -->
  <div class="header-ruban">
    <div class="ruban-section">
      <a href="Crabos.html" class="ruban-link" id="crabos">CRABOS</a>
      <a href="../CadetA/cadetA.html" class="ruban-link" id="cadetsA">CADETS A</a>
      <a href="../CadetB/cadetB.html" class="ruban-link" id="cadetsB">CADETS B</a>
      <a href="../Espoirs/espoirs.html" class="ruban-link" id="espoirs">ESPOIRS</a>
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
    <!-- Carte joueur - Exemple -->
    <div class="joueur-card">
      <span class="nom-joueur">Joueur 1</span>
      <a href="#" class="btn-formulaire">Tests et Performance</a>
      <a href="../../Formulaire/Medical/formmedical.php" class="btn-formulaire">Formulaire médical</a>
    </div>

    <div class="joueur-card">
      <span class="nom-joueur">Joueur 2</span>
      <a href="../../../Joueur/Fiche_joueur/performance.html" class="btn-formulaire">Tests et Performance</a>
      <a href="../../Formulaire/Medical/formmedical.php" class="btn-formulaire">Formulaire médical</a>
    </div>

    <?php
    require_once '/../../../bd.php';

    try {
        $stmt = $pdo->prepare("
          SELECT nom, prenom, id_joueur
          FROM joueurs
          JOIN equipe ON joueurs.id_equipe = equipe.id_equipe
          WHERE equipe.nom_equipe = :nom_equipe
        ");

        $stmt->execute(['nom_equipe' => 'Crabos']);
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
  </div>

</body>
</html>
