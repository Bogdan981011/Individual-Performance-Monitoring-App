<?php 
session_start(); 
if (!isset($_SESSION['user_id'])) {
    // L'utilisateur n'est pas connecté, on le redirige
    header("Location: /vizia/accueil.html");
    exit;
}
?>
<?php include('../../chatbot/chatbot.php'); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Modifier un joueur</title>
    <link rel="stylesheet" href="../../Styles/nouveau.css">
    <script src="modif_joueur.js"></script>
</head>
<body>
    <a  id="btn-retour" class="btn-retour" href="../accueil_staff.php">Retour</a>

<div class="form-container">
    <?php
        include_once "../../bd.php";

        try{
        $id_joueur = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($id_joueur === false) {
            echo "<p>Une erreur est survenue. Redirection...</p>";
            echo "<script>setTimeout(() => window.location.href = '../../accueil_staff.php', 1000);</script>";
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM joueur WHERE id_joueur = :id");
        $stmt -> execute(['id' => $id_joueur]);
        $joueur = $stmt->fetch(PDO::FETCH_ASSOC);

        $prenom = htmlspecialchars($joueur['prenom']);
        $nom = htmlspecialchars($joueur['nom']);
        $naissance = htmlspecialchars($joueur['annee']);
        $poste = htmlspecialchars($joueur['poste']);
        $email = htmlspecialchars($joueur['email']);
        $id_eq = htmlspecialchars($joueur['id_equipe']);

        $stmt = $pdo->prepare("SELECT nom_equipe FROM equipe WHERE id_equipe = :id");
        $stmt -> execute(['id' => $id_eq]);
        $equipe = $stmt->fetch(PDO::FETCH_ASSOC);

        $equipe = htmlspecialchars($equipe['nom_equipe']);

        } catch (PDOException $e){
        echo "Erreur : " . $e->getMessage();
        }
    ?>
    
    <h2><?= "Modification du profil de " . $prenom. " " . $nom ?></h2>
    <p> Merci de ne remplir que les champs à modifier.</p>

    <form>
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <p>
            <label for="nom_equipe">Équipe :</label>
            <select name="nom_equipe" id="nom_equipe">
                <option value="crabos" <?php if (strtolower($equipe) == "crabos") echo "disabled selected"?> >CRABOS</option>
                <option value="cadets a" <?php if (strtolower($equipe) == "cadets a") echo "disabled selected"?> >CADETS A</option>
                <option value="cadets b"<?php if (strtolower($equipe) == "cadets b") echo "disabled selected"?> >CADETS B</option>
                <option value="espoirs"<?php if (strtolower($equipe) == "espoirs") echo "disabled selected"?> >ESPOIRS</option>
            </select>
        </p>

        <!-- Année -->
        <p>
            <label for="annee">Date de naissance :</label>
            <input type="date" name="annee" id="annee" value="<?= $naissance ?>">
        </p>

        <!-- Nom -->
        <p>
            <label for="nom">Nom :</label>
            <input type="text" name="nom" id="nom" placeholder="<?= $nom ?>" >
        </p>

        <!-- Prénom -->
        <p>
            <label for="prenom">Prénom :</label>
            <input type="text" name="prenom" id="prenom" placeholder="<?= $prenom ?>">
        </p>

        <!-- Poste -->
        <p>
            <label for="poste">Poste :</label>
            <input type="text" name="poste" id="poste" placeholder="<?= $poste ?>">

        </p>

        <!-- Email -->
        <p>
            <label for="email">Email :</label>
            <input type="email" name="email" id="email"  placeholder="<?= $email ?>">
        </p>

        <!-- Mot de passe -->
        <p>
            <label for="mdp">Mot de passe provisoire :</label>
            <input type="password" name="mdp" id="mdp">
        </p>

        <p>
            <input type="submit" value="Ajouter">
        </p>
    </form>
</div>

</body>
</html>
