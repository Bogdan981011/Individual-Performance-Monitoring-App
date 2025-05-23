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
    <title>Modifier un joueur</title>
    <link rel="stylesheet" href="../../Styles/nouveau.css">
    <script src="modif.js"></script>
</head>
<body>
    <b><a href="../accueil_joueur.html">Retour</a></b>

<div class="form-container">
    
    <h1>Modification du mot de passe</h1>

    <form>
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <!-- Mot de passe -->
        <p>
            <label for="mdp_av">Ancien mot de passe:</label>
            <input type="password" name="mdp_av" id="mdp_av">
        </p>

        <p>
            <label for="mdp">Nouveau mot de passe :</label>
            <input type="password" name="mdp" id="mdp">
        </p>

        <p>
            <label for="confirmation_mdp">Confirmation du nouveau mot de passe :</label>
            <input type="password" name="confirmation_mdp" id="confirmation_mdp">
        </p>

        <p>
            <input type="submit" value="Ajouter">
        </p>
    </form>
</div>

</body>
</html>
