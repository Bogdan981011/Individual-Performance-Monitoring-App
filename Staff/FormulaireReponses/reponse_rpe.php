<?php
require_once '../../bd.php';

ob_start(); // Mise en mémoire tampon

try {
    // Récupération de l'ID de l'équipe depuis l'URL
    $id_equipe = isset($_GET['id_eq']) ? intval($_GET['id_eq']) : null;

    if ($id_equipe === null) {
        throw new Exception("ID d'équipe manquant dans l'URL.");
    }

    // Récupération des joueurs de l'équipe spécifiée
    $stmt_joueurs = $pdo->prepare("
        SELECT id_joueur, nom, prenom
        FROM joueur
        WHERE id_equipe = ?
    ");
    $stmt_joueurs->execute([$id_equipe]);
    $joueurs = $stmt_joueurs->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $erreur = "Erreur : " . $e->getMessage();
} catch (PDOException $e) {
    $erreur = "Erreur de base de données : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Derniers RPE Joueurs</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="reponse_rpe.css">
</head>
<body>
    <div class="back-button-container">
        <a href="choix_formulaire.php?id_eq=<?= urlencode($id_equipe) ?>" class="back-button-fixed">
            Retour au choix de formulaire
        </a>
    </div>

    <h1>Réponses RPE</h1> <!-- En dehors de la grille -->

    <div class="container">
        <?php if (isset($erreur)) : ?>
            <p class="error-message"><?= htmlspecialchars($erreur) ?></p>
        <?php elseif (empty($joueurs)) : ?>
            <p class="error-message">Aucun joueur trouvé pour cette équipe.</p>
        <?php else : ?>
            <?php foreach ($joueurs as $joueur) : ?>
                <?php
                    $id_joueur = $joueur['id_joueur'];
                    $nom = htmlspecialchars($joueur['nom']);
                    $prenom = htmlspecialchars($joueur['prenom']);

                    $stmt_rpe = $pdo->prepare("
                        SELECT * FROM rpe_form 
                        WHERE id_joueur = ? 
                        ORDER BY id_RPE DESC 
                        LIMIT 1
                    ");
                    $stmt_rpe->execute([$id_joueur]);
                    $donnees = $stmt_rpe->fetch(PDO::FETCH_ASSOC);
                ?>
                <div class="player-card">
                    <h3><?= "$prenom $nom" ?></h3>
                    <?php if ($donnees) : ?>
                        <p><strong>Type entraînement :</strong> <?= htmlspecialchars($donnees['type_entrainement']) ?></p>
                        <p><strong>Temps entraînement :</strong> <?= htmlspecialchars($donnees['temps_entrainement']) ?> min</p>
                        <p><strong>Difficulté :</strong> <?= htmlspecialchars($donnees['difficulte']) ?>/10</p>
                        <p class="observations"><strong>Observations :</strong><br><?= nl2br(htmlspecialchars($donnees['observations'])) ?></p>
                    <?php else : ?>
                        <p class="error-message">Aucune réponse trouvée pour ce joueur.</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>

</html>
