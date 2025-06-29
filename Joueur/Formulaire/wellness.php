<?php 
session_start(); 
if (!isset($_SESSION['user_id'])) {
  // L'utilisateur n'est pas connecté, on le redirige
  header("Location: /vizia/accueil.html");
  exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Formulaire Bien-être ASBH</title>
  <link rel="stylesheet" href="../Styles/rpe.css">
  <script src="wellness.js"></script>
</head>
<body>
    <!-- Bouton retour -->
    <div class="header">
        <a href="formulaires.html" class="btn-retour">Retour à l'accueil</a>
    </div>
  <div class="form-container">
    <h1><img src="../../Images/asbh.svg" class="logo-asbh" alt="ASBH">WELLNESS</h1>

    <form>
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

      <label for="date">Date</label>
      <input type="date" id="date" name="date" required>
      

      <label for="sommeil">Qualité du sommeil</label>
      <div class="range-wrapper">
        <div class="range-labels">
          <span style="left: 0%;">Très Mauvais</span>
          <span style="left: 25%;">Mauvais</span>
          <span style="left: 50%;">Moyen</span>
          <span style="left: 75%;">Bon</span>
          <span style="left: 100%;">Excellent</span>
        </div>
        <input type="range" id="sommeil" name="sommeil" min="1" max="5" step="1" class="range-sommeil">
      </div>

      <label for="courbatureHaut">Courbature Haut</label>
      <div class="range-wrapper">
        <div class="difficulty-labels">
          <span class="facile">Aucune</span>
          <span class="difficile">Forte</span>
        </div>
        <input type="range" id="courbatureHaut" name="courbatureHaut" min="0" max="10" step="1">
      </div>

      <label for="courbatureBas">Courbature Bas</label>
      <div class="range-wrapper">
        <div class="difficulty-labels">
          <span class="facile">Aucune</span>
          <span class="difficile">Forte</span>
        </div>
        <input type="range" id="courbatureBas" name="courbatureBas" min="0" max="10" step="1">
      </div>

      <label for="humeur">Score humeur</label>
      <div class="range-wrapper">
        <div class="range-labels">
          <span style="left: 0%;">Très Bas</span>
          <span style="left: 25%;">Bas</span>
          <span style="left: 50%;">Neutre</span>
          <span style="left: 75%;">Bon</span>
          <span style="left: 100%;">Excellent</span>
        </div>
        <input type="range" id="humeur" name="humeur" min="1" max="5" step="1" class="range-sommeil">
      </div>

      <label for="observations">Observations</label>
      <textarea id="observations" name="observations" rows="4" placeholder="Notes, douleurs, émotions..."></textarea>

      <button type="submit">Submit</button>
      <?php $id_joueur = $_GET['id'] ?>
    </form>
  </div>
</body>
</html>
