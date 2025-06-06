<?php

session_start(); 
if (!isset($_SESSION['user_id'])) {
  // L'utilisateur n'est pas connecté, on le redirige
  header("Location: /vizia/accueil.php");
  exit;
}

require_once '../../bd.php';
ob_start();

try {
    $id_equipe = isset($_GET['id_eq']) ? intval($_GET['id_eq']) : null;

    if ($id_equipe === null) {
        throw new Exception("ID d'équipe manquant dans l'URL.");
    }

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Derniers Wellness Joueurs</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="reponse_rpe.css"> <!-- Tu peux garder le même CSS -->
</head>
<body>
<div class="back-button-container">
    <a href="choix_formulaire.php?id_eq=<?= urlencode($id_equipe) ?>" class="back-button-fixed">
        Retour au choix de formulaire
    </a>
</div>

<h1>Réponses Wellness</h1>

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

                $stmt_wellness = $pdo->prepare("
                    SELECT * FROM wellness_form 
                    WHERE id_joueur = ? 
                   
                ");
                $stmt_wellness->execute([$id_joueur]);
                $donnees = $stmt_wellness->fetch(PDO::FETCH_ASSOC);

                $player_data = [
                    'nom' => "$prenom $nom",
                    'sommeil' => $donnees['sommeil'] ?? '',
                    'haut' => $donnees['courbatures_haut'] ?? '',
                    'bas' => $donnees['courbatures_bas'] ?? '',
                    'humeur' => $donnees['humeur'] ?? '',
                    'observations' => $donnees['observations'] ?? '',
                ];
                $json_player_data = htmlspecialchars(json_encode($player_data), ENT_QUOTES, 'UTF-8');
            ?>
            <div class="player-card" data-player='<?= $json_player_data ?>' style="cursor:pointer;">
                <h3><?= "$prenom $nom" ?></h3>
                <?php if ($donnees) : ?>
                    <p><strong>Sommeil :</strong> <?= htmlspecialchars($donnees['sommeil']) ?>/10</p>
                    <p><strong>Courbatures Haut :</strong> <?= htmlspecialchars($donnees['courbatures_haut']) ?>/10</p>
                    <p><strong>Courbatures Bas :</strong> <?= htmlspecialchars($donnees['courbatures_bas']) ?>/10</p>
                    <p><strong>Humeur :</strong> <?= htmlspecialchars($donnees['humeur']) ?>/10</p>
                    <p class="observations"><strong>Observations :</strong><br><?= nl2br(htmlspecialchars($donnees['observations'])) ?></p>
                <?php else : ?>
                    <p class="error-message">Aucune réponse Wellness trouvée pour ce joueur.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal Wellness -->
<div class="modal-overlay" id="modalOverlay" aria-hidden="true" role="dialog" aria-labelledby="modalName">
    <div class="modal" role="document">
        <button class="modal-close" id="modalClose" aria-label="Fermer">&times;</button>
        <h2 id="modalName">Nom du joueur</h2>
        <p><strong>Sommeil :</strong> <span id="modalSommeil"></span>/10</p>
        <p><strong>Courbatures Haut :</strong> <span id="modalHaut"></span>/10</p>
        <p><strong>Courbatures Bas :</strong> <span id="modalBas"></span>/10</p>
        <p><strong>Humeur :</strong> <span id="modalHumeur"></span>/10</p>
        <p><strong>Observations :</strong></p>
        <p id="modalObservations"></p>
    </div>
</div>

<script>
// Modal logique
document.querySelectorAll('.player-card').forEach(card => {
    card.addEventListener('click', () => {
        const data = JSON.parse(card.getAttribute('data-player'));
        document.getElementById('modalName').textContent = data.nom || '';
        document.getElementById('modalSommeil').textContent = data.sommeil || '';
        document.getElementById('modalHaut').textContent = data.haut || '';
        document.getElementById('modalBas').textContent = data.bas || '';
        document.getElementById('modalHumeur').textContent = data.humeur || '';
        document.getElementById('modalObservations').innerHTML = (data.observations || '').replace(/\n/g, '<br>');

        const overlay = document.getElementById('modalOverlay');
        overlay.classList.add('active');
        overlay.setAttribute('aria-hidden', 'false');
    });
});

document.getElementById('modalClose').addEventListener('click', () => {
    const overlay = document.getElementById('modalOverlay');
    overlay.classList.remove('active');
    overlay.setAttribute('aria-hidden', 'true');
});

document.getElementById('modalOverlay').addEventListener('click', (e) => {
    if (e.target.id === 'modalOverlay') {
        e.currentTarget.classList.remove('active');
        e.currentTarget.setAttribute('aria-hidden', 'true');
    }
});
</script>
</body>
</html>
