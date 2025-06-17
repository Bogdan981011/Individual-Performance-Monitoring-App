<?php
session_start(); 
if (!isset($_SESSION['user_id'])) {
    // L'utilisateur n'est pas connecté, on le redirige
    header("Location: /vizia/accueil.html");
    exit;
}

require_once '../../bd.php';

// Handle validation/invalidation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected']) && isset($_POST['toggle_statut'])) {
    foreach ($_POST['selected'] as $entry) {
        [$id, $entryType] = explode('|', $entry);
        $id = intval($id);

        if ($entryType === 'joueur') {
            $query = "UPDATE joueur SET validite = 1 - validite WHERE id_joueur = :id";
        } else {
            $query = "UPDATE staff SET validite = 1 - validite WHERE id_staff = :id";
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute(['id' => $id]);
    }

    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . '?' . http_build_query($_GET));
    exit;
}

// Determine selected type and team
$type = isset($_GET['type']) && $_GET['type'] === 'staff' ? 'staff' : 'joueur';
$team_id = isset($_GET['team']) ? intval($_GET['team']) : null;
$search = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';

// Fetch data based on filters
function getPlayers($pdo, $validite, $team_id = null) {
    if ($team_id) {
        $stmt = $pdo->prepare("SELECT id_joueur AS id, nom, prenom, poste FROM joueur WHERE validite = :validite AND id_equipe = :id");
        $stmt->execute(['validite' => $validite, 'id' => $team_id]);
    } else {
        $stmt = $pdo->prepare("SELECT id_joueur AS id, nom, prenom, poste FROM joueur WHERE validite = :validite");
        $stmt->execute(['validite' => $validite]);
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$valid_players = getPlayers($pdo, 1, $type === 'joueur' ? $team_id : null);
$invalid_players = getPlayers($pdo, 0, $type === 'joueur' ? $team_id : null);

$stmt = $pdo->prepare("SELECT id_staff AS id, nom, prenom, role FROM staff WHERE validite=1");
$stmt->execute();
$valid_staff = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT id_staff AS id, nom, prenom, role FROM staff WHERE validite=0");
$stmt->execute();
$invalid_staff = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Filter by search
function filterByName($array, $search) {
    if ($search === '') return $array;
    return array_filter($array, function($item) use ($search) {
        return strpos(strtolower($item['nom']), $search) !== false ||
               strpos(strtolower($item['prenom']), $search) !== false;
    });
}

if ($type === 'joueur') {
    $valid_filtered = filterByName($valid_players, $search);
    $invalid_filtered = filterByName($invalid_players, $search);
} else {
    $valid_filtered = filterByName($valid_staff, $search);
    $invalid_filtered = filterByName($invalid_staff, $search);
}
?>
<?php include('../../chatbot/chatbot.php'); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Membres</title>
    <style>
        body { font-family: 'Segoe UI', 
            sans-serif; padding: 30px; 
            background: url('../../Images/background.svg') no-repeat center center fixed;
            background-size: cover;
        }
        h1 { text-align: center; margin-bottom: 30px; color: white; }
        .selector { text-align: center; margin-bottom: 30px; }
        select, input[type="text"], button {
            padding: 8px 12px; font-size: 16px; border-radius: 6px; border: 1px solid #ccc;
        }
        button { background-color:rgb(197, 0, 0); color: white; border: none; cursor: pointer; }
        .searchbar { margin-top: 15px; }
        .columns { display: flex; gap: 40px; justify-content: center; flex-wrap: wrap; }
        .column {
            flex: 1; min-width: 300px; max-width: 500px; background-color: white;
            padding: 20px; border-radius: 12px; box-shadow: 0 4px 8px rgba(0,0,0,0.06);
            opacity: 0.95;
        }
        h2 { text-align: center; color:rgb(204, 0, 0); margin-top: 0; }
        .card {
            background:rgb(232, 39, 0); border: 1px solid white(225, 229, 236, 0.63); padding: 15px; margin: 10px 0;
            border-radius: 8px; transition: background 0.2s, border 0.2s; cursor: pointer;
        }
        .card:hover { background:rgb(145, 6, 6); }
        .card.selected { border: 5px solid black; background-color:rgb(145, 6, 6); }
        .card strong { font-size: 17px; color: white; }
        .card em { color: rgb(255,255,255); font-size: 14px; }
        .actions { text-align: center; margin-top: 30px; }
        @media (max-width: 768px) {
            .columns { flex-direction: column; gap: 20px; }
            .column { max-width: 100%; }
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

    <h1>Liste des <?= $type === 'staff' ? 'Membres du Staff' : 'Joueurs' ?></h1>

    <div class="selector">
        <form method="GET">
            <label for="type">Afficher :</label>
            <select name="type" id="type" onchange="this.form.submit()">
                <option value="joueur" <?= $type === 'joueur' ? 'selected' : '' ?>>Joueurs</option>
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

    <?php if ($search !== '' && empty($valid_filtered) && empty($invalid_filtered)): ?>
        <p style="text-align: center; color: red; font-weight: bold;">
            Aucun résultat trouvé pour « <?= htmlspecialchars($search) ?> »
        </p>
    <?php endif; ?>

    <form method="POST">
        <div class="columns">
            <div class="column">
                <h2>✅ Valide</h2>
                <?php foreach ($valid_filtered as $p): ?>
                    <?php $value = htmlspecialchars($p['id'] . '|' . $type); ?>
                    <div class="card" onclick="toggleSelection(this, '<?= $value ?>')">
                        <strong><?= htmlspecialchars($p['prenom']) ?> <?= htmlspecialchars($p['nom']) ?></strong><br>
                        <em><?= htmlspecialchars($p['poste'] ?? $p['role']) ?></em>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="column">
                <h2>❌ Invalide</h2>
                <?php foreach ($invalid_filtered as $p): ?>
                    <?php $value = htmlspecialchars($p['id'] . '|' . $type); ?>
                    <div class="card" onclick="toggleSelection(this, '<?= $value ?>')">
                        <strong><?= htmlspecialchars($p['prenom']) ?> <?= htmlspecialchars($p['nom']) ?></strong><br>
                        <em><?= htmlspecialchars($p['poste'] ?? $p['role']) ?></em>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="actions">
            <button type="submit" name="toggle_statut">Changer statut</button>
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
