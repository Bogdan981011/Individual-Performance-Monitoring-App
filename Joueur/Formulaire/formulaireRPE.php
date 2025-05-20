<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire Entraînement ASBH</title>
    <link rel="stylesheet" href="../Styles/rpe.css"> <!-- Le lien vers ton fichier CSS -->
    <script src="rpe.js"></script>

</head>
<body>
  <!-- Bouton retour -->
    <div class="header">
        <a href="formulaires.html" class="btn-retour">Retour à l'acceuil</a>
    </div>
    <div class="form-container">
        <h1>
            <img src="../../Images/asbh.svg" alt="Logo ASBH" class="logo-asbh"> Formulaire RPE
        </h1>
        <form action="reponse_rpe.php?id=<?= $id_joueur ?>" method="POST">

            <!-- Type d'entraînement -->
            <label for="type-entrainement">Type d’entraînement</label>
            <select id="type-entrainement" name="type-entrainement" required>
                <option value="rugby">Rugby/Match</option>
                <option value="musculation">Musculation</option>
                <option value="fit">Fit</option>
                <option value="energie">Énergie</option>
                <option value="pid">PID</option>
                <option value="poste">Poste</option>
                <option value="halterophilie">Haltérophilie</option>
                <option value="ecole-cours">École de course</option>
                <option value="fat-club">Fat-club</option>
                <option value="prehab">Préhab</option>
            </select>

            <!-- Temps d'entraînement -->
            <label for="temps-entrainement">Temps d’entraînement</label>
            <select id="temps-entrainement" name="temps-entrainement" required>
                <option value="30">30 min</option>
                <option value="45">45 min</option>
                <option value="60">60 min</option>
            </select>

            <!-- Difficulté -->
            <label for="difficulte">Difficulté</label>

            <div class="range-wrapper">
                <!-- Ajout de Facile et Difficile au-dessus de l'échelle -->
                <div class="difficulty-labels">
                    <span class="facile">Facile</span>
                    <span class="difficile">Difficile</span>
                </div>

                <input type="range" id="difficulte" name="difficulte" min="0" max="5" step="1" value="5" 
                    oninput="difficulteOutput.value = difficulte.value">

                <div class="range-labels">
                    <span style="left: 0%">0</span>
                    <span style="left: 20%">1</span>
                    <span style="left: 40%">2</span>
                    <span style="left: 60%">3</span>
                    <span style="left: 80%">4</span>
                    <span style="left: 100%">5</span>
                </div>
            </div>

            <!-- Observations -->
            <label for="observations">Observations</label>
            <textarea id="observations" name="observations" rows="4" placeholder="Observations" required></textarea>

            <!-- Bouton Submit -->
            <button type="submit">Submit</button>
            <?php $id_joueur = $_GET['id'] ?>
        </form>
    </div>

</body>
</html>
