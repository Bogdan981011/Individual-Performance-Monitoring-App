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
        <span>6</spaici n><span>7</span><span>8</span><span>9</span><span>10</span>
      </div>

     

      <!-- Date -->
      <label for="date">Date blessure</label>
      <input type="date" id="date" name="date" required>

      <!-- Observation -->
      <label for="observation">Observation</label>
      <textarea id="observation" name="observation" rows="3"></textarea>

      <button type="submit">Envoyer</button>

      <?php $id_joueur = $_GET['id'] ?>

    </form>
  </div>
</body>
</html>
