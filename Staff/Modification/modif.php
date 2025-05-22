<?php 
session_start(); 
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un joueur</title>
    <link rel="stylesheet" href="../../Styles/nouveau.css">
</head>
<body>

<div class="form-container">
    <?php
    include_once "../../bd.php";

    try{
    $id_joueur = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id_joueur === false) {
        echo "<p>Une erreur est survenue. Redirection...</p>";
        echo "<script>setTimeout(() => window.location.href = '../../accueil_staff.html', 1000);</script>";
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
    <h1><?= "Modification du profil de " . $prenom. " " . 'nom' ?></h1>
    <p> Merci de ne remplir que les champs à modifier.</p>

    <form method="post" action="ajouter_joueur.php">
        <p>
            <label for="id_equipe">Équipe :</label>
            <select name="id_equipe" id="id_equipe">
                <option value="" disabled selected> <?= $equipe ?> </option>
                <option value="crabos">CRABOS</option>
                <option value="cadets a">CADETS A</option>
                <option value="cadets b">CADETS B</option>
                <option value="espoirs">ESPOIRS</option>
            </select>
        </p>

        <!-- Année -->
        <p>
            <label for="annee">Date de naissance :</label>
            <input type="number" name="annee" id="annee" placeholder="<?= htmlspecialchars($result['annee']) ?>">
        </p>

        <!-- Nom -->
        <p>
            <label for="nom">Nom :</label>
            <input type="text" name="nom" id="nom" placeholder="<?= htmlspecialchars($result['nom']) ?>" >
        </p>

        <!-- Prénom -->
        <p>
            <label for="prenom">Prénom :</label>
            <input type="text" name="prenom" id="prenom" placeholder="<?= htmlspecialchars($result['prenom']) ?>">
        </p>

        <!-- Poste -->
        <p>
            <label for="poste">Poste :</label>
            <input type="text" name="poste" id="poste">
        </p>

        <!-- Email -->
        <p>
            <label for="email">Email :</label>
            <input type="email" name="email" id="email" required>
        </p>

        <!-- Mot de passe -->
        <p>
            <label for="mdp">Mot de passe :</label>
            <input type="password" name="mdp" id="mdp" required>
        </p>

        <p>
            <input type="submit" value="Ajouter">
        </p>
    </form>
</div>

</body>
</html>
