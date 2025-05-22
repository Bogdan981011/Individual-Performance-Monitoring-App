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
    <style>
        /* Styles minimalistes pour le modal, adapte avec ton CSS */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .modal-overlay.active {
            display: flex;
        }
        .modal {
            background: #fff;
            padding: 20px 30px;
            border-radius: 8px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            position: relative;
        }
        .modal-close {
            position: absolute;
            top: 10px; right: 15px;
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #333;
        }
        .modal p {
            margin: 10px 0;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="back-button-container">
        <a href="choix_formulaire.php?id_eq=<?= urlencode($id_equipe) ?>" class="back-button-fixed">
            Retour au choix de formulaire
        </a>
    </div>

    <h1>Réponses RPE</h1>

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

                    // Préparer données JSON pour le modal
                    $player_data = [
                        'nom' => "$prenom $nom",
                        'type_entrainement' => $donnees['type_entrainement'] ?? '',
                        'temps_entrainement' => $donnees['temps_entrainement'] ?? '',
                        'difficulte' => $donnees['difficulte'] ?? '',
                        'observations' => $donnees['observations'] ?? '',
                    ];
                    $json_player_data = htmlspecialchars(json_encode($player_data), ENT_QUOTES, 'UTF-8');
                ?>
                <div class="player-card" data-player='<?= $json_player_data ?>' style="cursor:pointer;">
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

    <!-- Modal -->
    <div class="modal-overlay" id="modalOverlay" aria-hidden="true" role="dialog" aria-labelledby="modalName">
      <div class="modal" role="document">
        <button class="modal-close" id="modalClose" aria-label="Fermer">&times;</button>
        <h2 id="modalName">Nom du joueur</h2>
        <p><strong>Type entraînement :</strong> <span id="modalType"></span></p>
        <p><strong>Temps entraînement :</strong> <span id="modalTemps"></span> min</p>
        <p><strong>Difficulté :</strong> <span id="modalDifficulte"></span>/10</p>
        <p><strong>Observations :</strong></p>
        <p id="modalObservations"></p>
      </div>
    </div>

    <script>
    // Gestion ouverture/fermeture modal
    document.querySelectorAll('.player-card').forEach(card => {
      card.addEventListener('click', () => {
        const data = JSON.parse(card.getAttribute('data-player'));
        document.getElementById('modalName').textContent = data.nom || '';
        document.getElementById('modalType').textContent = data.type_entrainement || '';
        document.getElementById('modalTemps').textContent = data.temps_entrainement || '';
        document.getElementById('modalDifficulte').textContent = data.difficulte || '';
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
      if(e.target.id === 'modalOverlay') {
        e.currentTarget.classList.remove('active');
        e.currentTarget.setAttribute('aria-hidden', 'true');
      }
    });
    </script>
</body>
</html>
