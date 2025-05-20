<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Formulaire Médical ASBH</title>
  <link rel="stylesheet" href="../../../Styles/formmedical.css">
  <script src="formmedical.js"></script>
</head>
<body>
  <div class="header">
       <a href="../../accueil_staff.html" class="btn-retour">Retour à l'accueil</a>
  </div>
  <div class="form-container">
    <div class="header">
      <img src="../../../Images/asbh.svg" alt="Logo ASBH" class="logo">
      <h1>Formulaire<br>Médical</h1>
    </div>

    <div>
      <?php
      include_once "../../../bd.php";

      try{
        $id_joueur = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($id_joueur === false) {
          echo "<p>Une erreur est survenue. Redirection...</p>";
          echo "<script>setTimeout(() => window.location.href = '../../accueil_staff.html', 1000);</script>";
          exit;
        }

        $stmt = $pdo->prepare("SELECT nom, prenom FROM joueur WHERE id_joueur=:id");
        $stmt -> execute(['id' => $id_joueur]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        ?>
        <h2><?= htmlspecialchars($result['prenom'] . " " . $result['nom']) ?></h2>
        <?php

      } catch (PDOException $e){
        echo "Erreur : " . $e->getMessage();
      }
      ?>
    </div>

    <form>
      <!-- Type de blessure -->
      <label for="type-blessure">Type de blessure</label>
      <input type="text" id="type" name="type" placeholder="Ex : Entorse" required>
    
      <!-- Gravité -->
      <label for="gravite">Gravité blessure</label>
      <div class="range-container">
        <!-- Texte au-dessus de l'échelle -->
        <div class="range-texts">
          <span class="range-start">Pas grave</span>
          <span class="range-end">Très grave</span>
        </div>
        <input type="range" id="gravite" name="gravite" min="1" max="10" value="1" step="1"
          oninput="document.getElementById('graviteOutput').value = gravite.value">
      </div>
      
      <!-- Affichage des chiffres sous l'échelle -->
      <div class="range-labels">
        <span>1</span><span>2</span><span>3</span><span>4</span><span>5</span>
        <span>6</span><span>7</span><span>8</span><span>9</span><span>10</span>
      </div>

      <!-- Date -->
      <label for="date">Date blessure</label>
      <input type="date" id="date" name="date" required>

      <!-- Recommandation -->
      <label for="recommandation">Recommandation</label>
      <textarea id="recommandation" name="recommandation" rows="2" maxlength="500"></textarea>
      
      <!-- Reprise -->
      <label for="duree">Reprise</label>
      <textarea id="reprise" name="reprise" rows="2" maxlength="500"></textarea>

      <button type="submit">Envoyer</button>

    </form>
  </div>
</body>
</html>
