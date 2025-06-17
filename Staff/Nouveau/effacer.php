<?php
session_start(); 
if (!isset($_SESSION['user_id'])) {
    // L'utilisateur n'est pas connecté, on le redirige
    header("Location: /vizia/accueil.html");
    exit;
}

require_once '../../bd.php';

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected'])) {
    foreach ($_POST['selected'] as $entry) {
        [$id, $entryType] = explode('|', $entry);
        $id = intval($id);

        if ($entryType === 'joueur') {
            $stmt = $pdo->prepare("DELETE FROM joueur WHERE id_joueur = :id");
        } else {
            $stmt = $pdo->prepare("DELETE FROM staff WHERE id_staff = :id");
        }
        $stmt->execute(['id' => $id]);
    }

    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . '?' . http_build_query($_GET));
    exit;
}

// Filters
$type = isset($_GET['type']) && $_GET['type'] === 'staff' ? 'staff' : 'joueur';
$team_id = isset($_GET['team']) ? intval($_GET['team']) : null;
$search = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';

// Fetch members
function getPlayers($pdo, $team_id = null) {
    if ($team_id) {
        $stmt = $pdo->prepare("SELECT id_joueur AS id, nom, prenom, poste FROM joueur WHERE id_equipe = :id");
        $stmt->execute(['id' => $team_id]);
    } else {
        $stmt = $pdo->prepare("SELECT id_joueur AS id, nom, prenom, poste FROM joueur");
        $stmt->execute();
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$players = getPlayers($pdo, $type === 'joueur' ? $team_id : null);

$stmt = $pdo->prepare("SELECT id_staff AS id, nom, prenom, role FROM staff");
$stmt->execute();
$staff = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Filter by name
function filterByName($array, $search) {
    if ($search === '') return $array;
    return array_filter($array, function($item) use ($search) {
        return strpos(strtolower($item['nom']), $search) !== false ||
               strpos(strtolower($item['prenom']), $search) !== false;
    });
}

$data_filtered = $type === 'joueur' ? filterByName($players, $search) : filterByName($staff, $search);
?>
<?php include('../../chatbot/chatbot.php'); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Supprimer un Membre</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; padding: 30px; background: url('../../Images/background.svg') no-repeat center center fixed;
            background-size: cover; }
        h1 { text-align: center; margin-bottom: 30px; color:rgb(255, 255, 255); }
        .selector { text-align: center; margin-bottom: 30px; }
        select, input[type="text"], button {
            padding: 8px 12px; font-size: 16px; border-radius: 6px; border: 1px solid #ccc;
        }
        button { background-color: #b00020; color: white; border: none; cursor: pointer; }
        button:hover {background-color: rgb(139, 27, 27);}
        .searchbar { margin-top: 15px; }
        .cards {background:rgba(255, 255, 255, 0.71);              /* white background */
                padding: 20px;                    /* inner spacing */
                border-radius: 12px;              /* rounded corners */
                box-shadow: 0 4px 8px rgba(0,0,0,0.1); /* soft drop-shadow */
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 20px;
                opacity : 0.98
            }
        .card {
            background: rgb(232, 16, 16); border: 1px solid rgb(255, 0, 0); padding: 15px;
            border-radius: 8px; width: 280px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); cursor: pointer;
             opacity: 0.95;
        }
        .card:hover { background:rgb(185, 6, 6); }
        .card.selected { border: 5px solid rgb(67, 0, 12); background-color:rgb(149, 17, 17); }
        .card strong { font-size: 17px; color: white; }
        .card em { color: white; font-size: 14px; }
        .actions { text-align: center; margin-top: 30px; }
        @media (max-width: 768px) {
            .cards { flex-direction: column; align-items: center; }
        }
        .return-btn-container {
            text-align: right;
            margin-bottom: 20px;
        }

        .return-btn {
            display: inline-block;
            color: white;
            background-color: #e74c3c;
            padding: 10px 15px;
            border-radius: 6px;
            text-decoration: none;
        }

        .return-btn:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>

    <div class="return-btn-container">
        <a href="../accueil_staff.php" class="return-btn">Retour à l'accueil</a>
    </div>

    <h1>Supprimer <?= $type === 'staff' ? 'un Membre du Staff' : 'un Joueur' ?></h1>

    <div class="selector">
        <form method="GET">
            <label for="type">Type :</label>
            <select name="type" id="type" onchange="this.form.submit()">
                <option value="joueur" <?= $type === 'joueur' ? 'selected' : '' ?>>Joueur</option>
                <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'directeur' || $_SESSION['role'] === 'admin')): ?>
                    <option value="staff" <?= $type === 'staff' ? 'selected' : '' ?>>Staff</option>
                <?php endif; ?>
            </select>

            <?php if ($type === 'joueur'): ?>
                <label for="team">Équipe :</label>
                <select name="team" id="team" onchange="this.form.submit()">
                    <option value="">Toutes</option>
                    <option value="1" <?= $team_id === 1 ? 'selected' : '' ?>>Cadets A</option>
                    <option value="2" <?= $team_id === 2 ? 'selected' : '' ?>>Cadets B</option>
                    <option value="3" <?= $team_id === 3 ? 'selected' : '' ?>>Crabos</option>
                    <option value="4" <?= $team_id === 4 ? 'selected' : '' ?>>Espoirs</option>
                </select>
            <?php endif; ?>

            <div class="searchbar">
                <input type="text" name="search" placeholder="Rechercher un nom..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit">Rechercher</button>
            </div>
        </form>
    </div>

    <?php if ($search !== '' && empty($data_filtered)): ?>
        <p style="text-align: center; color: red; font-weight: bold;">
            Aucun résultat trouvé pour « <?= htmlspecialchars($search) ?> »
        </p>
    <?php endif; ?>

    <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer les membres sélectionnés ? Cette action est irréversible.');">
        <div class="cards">
            <?php foreach ($data_filtered as $p): ?>
                <?php $value = htmlspecialchars($p['id'] . '|' . $type); ?>
                <div class="card" onclick="toggleSelection(this, '<?= $value ?>')">
                    <strong><?= htmlspecialchars($p['prenom']) ?> <?= htmlspecialchars($p['nom']) ?></strong><br>
                    <em><?= htmlspecialchars($p['poste'] ?? $p['role']) ?></em>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="actions">
            <button type="submit">Supprimer les membres sélectionnés</button>
        </div>

        <div id="selectedInputs"></div>
    </form>

    <script>
        const selectedInputs = document.getElementById('selectedInputs');
        const selectedSet = new Set();

        function toggleSelection(card, value) {
            if (selectedSet.has(value)) {
                selectedSet.delete(value);
                card.classList.remove('selected');
            } else {
                selectedSet.add(value);
                card.classList.add('selected');
            }
            updateHiddenInputs();
        }

        function updateHiddenInputs() {
            selectedInputs.innerHTML = '';
            selectedSet.forEach(value => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected[]';
                input.value = value;
                selectedInputs.appendChild(input);
            });
        }
    </script>

</body>
</html>
