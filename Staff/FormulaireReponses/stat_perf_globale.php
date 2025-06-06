<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // L'utilisateur n'est pas connecté, on le redirige
    header("Location: /vizia/accueil.php");
    exit;
}

require_once '../../bd.php';

// Get the team ID
$id_equipe = filter_input(INPUT_GET, 'id_eq', FILTER_VALIDATE_INT);
if (!$id_equipe) {
    die("ID équipe manquant ou invalide.");
}

// Get team name
$stmt = $pdo->prepare("SELECT nom_equipe FROM equipe WHERE id_equipe = :id");
$stmt->execute(['id' => $id_equipe]);
$equipe = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$equipe) {
    die("Équipe introuvable.");
}

// Get stats
$stats = [];

// Total matches played by this team
$stmt = $pdo->prepare("SELECT COUNT(*) FROM info_match WHERE id_equipe = :id");
$stmt->execute(['id' => $id_equipe]);
$stats['total_matchs'] = $stmt->fetchColumn();

// Victories by this team
$stmt = $pdo->prepare("SELECT COUNT(*) FROM info_match WHERE id_equipe = :id AND match_gagnant = 'asbh'");
$stmt->execute(['id' => $id_equipe]);
$stats['victoires'] = $stmt->fetchColumn();

// Defeats by this team
$stmt = $pdo->prepare("SELECT COUNT(*) FROM info_match WHERE id_equipe = :id AND match_gagnant = 'adverse'");
$stmt->execute(['id' => $id_equipe]);
$stats['defaites'] = $stmt->fetchColumn();

// Draws by this team
$stmt = $pdo->prepare("SELECT COUNT(*) FROM info_match WHERE id_equipe = :id AND match_gagnant = 'match nul'");
$stmt->execute(['id' => $id_equipe]);
$stats['nuls'] = $stmt->fetchColumn();


// Moyenne de minutes jouées par joueur
$stmt = $pdo->prepare("
    SELECT j.nom, j.prenom, AVG(a.minutes_joues) AS avg_minutes
    FROM joueur j
    JOIN analyse_joueur_match a ON j.id_joueur = a.id_joueur
    JOIN info_match m ON a.id_match = m.id_match
    WHERE m.id_equipe = :id AND j.validite = 1
    GROUP BY j.id_joueur
    ORDER BY avg_minutes DESC
    LIMIT 5
");
$stmt->execute(['id' => $id_equipe]);
$top_joueurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Dashboard - <?= htmlspecialchars($equipe['nom_equipe']) ?></title>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: Arial, sans-serif;
            overflow-x: hidden;
        }

        body {
            font-family: Arial;
            margin: 20px;
            background: #FBEAEA;
            padding-top: 80px; /* Ajouté pour créer un espace après le ruban */
        }

        h1, h2 { text-align: center; color: #CC0A0A; }
        .stats { display: flex; justify-content: space-around; margin-bottom: 30px; }
        .stat-box {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 200px;
            text-align: center;
        }
        table {
            margin: 0 auto;
            border-collapse: collapse;
            width: 60%;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            border: 1px solid #ccc;
        }
        th {
            background-color: #CC0A0A;
            color: white;
        }

        .header-ruban {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 55px;
            background: linear-gradient(to right, #CC0A0A, #a40808);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            z-index: 99;
        }

        .ruban-section {
            display: flex;
            justify-content: space-between;
            width: 80%;
        }

        .ruban-link {
            font-family: 'Bebas Neue', sans-serif;
            color: rgba(255, 255, 255, 0.666);
            text-decoration: none;
            padding: 10px 20px;
            text-align: center;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .ruban-link:hover {
            background-color: #a40808;
            transform: scale(1.05);
        }

        .ruban-link.active {
            text-decoration: underline;
            color: #aaa;
            pointer-events: none;
        }

        /* Style du bouton retour */
        .btn-retour {
            font-family: Arial, sans-serif;
            font-size: 14px;
            font-weight: normal;
            background-color: #CC0A0A;
            color: #f9f9f9;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 6px;
            transition: background-color 0.3s ease, transform 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center; /* centré aussi horizontalement */

        }
        .btn-retour:hover {
            background-color: #d93e00;
            transform: scale(1.05);
        }


        @media (max-width: 768px) {
            .ruban-section {
                width: 100%;
                justify-content: space-evenly;
            }

            .ruban-section {
                display: flex;
                overflow-x: auto;
                padding: 0 10px;
                scroll-behavior: smooth;
            }

        
            body.section-equipe {
                position: relative;
                top: auto;
                right: auto;
                align-self: flex-end;
                z-index: 100;
            }
            .ruban-link {
                font-size: 0.9rem;
                padding: 8px 12px;
                min-width: fit-content;
                flex-shrink: 0; /* empêche de rétrécir trop */
            }
            .ruban-section {
                display: flex;
                overflow-x: auto;
                white-space: nowrap;
                padding: 0 10px;
                gap: 8px;
            }


        }
    </style>
</head>
<body>

<?php 
    require_once "../../bd.php";
    $id_equipe = filter_input(INPUT_GET, 'id_eq', FILTER_VALIDATE_INT);
    if ($id_equipe === false) {
        echo "<p>Une erreur est survenue. Redirection...</p>";
        echo "<script>setTimeout(() => window.location.href = '../../accueil_staff.php', 1000);</script>";
        exit;
    }    
    $stmt = $pdo->prepare("SELECT nom_equipe FROM equipe WHERE id_equipe =:id");
    $stmt -> execute(['id' => $id_equipe]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $nom = strtoupper(trim($result['nom_equipe']));

    // Récupère toutes les équipes nécessaires
    $stmt = $pdo->query("SELECT id_equipe, nom_equipe FROM equipe");
    $equipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    ?>
    <div class="header-ruban">
        <div class="ruban-section">
            <?php foreach ($equipes as $equipe): 
                $nomEquipe = strtoupper($equipe['nom_equipe']);
                $activeClass = ($nom === $nomEquipe) ? 'active' : '';
            ?>
                <a href="stat_perf_globale.php?id_eq=<?= $equipe['id_equipe'] ?>"
                class="ruban-link <?= $activeClass ?>"
                id="<?= strtolower(str_replace(' ', '', $nomEquipe)) ?>">
                <?= $nomEquipe ?>
                </a>
            <?php endforeach; ?>
            <a href="../section_perf_globale.php?id_eq=<?= $id_equipe ?>" class="btn-retour">Retour à la section</a>
        </div>
    </div>

 




<h1>Dashboard collectif</h1>

<div class="stats">
    <div class="stat-box">
        <h2><?= $stats['total_matchs'] ?></h2>
        <p>Matchs joués</p>
    </div>
    <div class="stat-box">
        <h2><?= $stats['victoires'] ?></h2>
        <p>Victoires</p>
        <small><?= ($stats['total_matchs'] > 0) ? round(($stats['victoires'] / $stats['total_matchs']) * 100, 1) . '%' : '0%' ?></small>
    </div>
    <div class="stat-box">
        <h2><?= $stats['defaites'] ?></h2>
        <p>Défaites</p>
        <small><?= ($stats['total_matchs'] > 0) ? round(($stats['defaites'] / $stats['total_matchs']) * 100, 1) . '%' : '0%' ?></small>
    </div>
    <div class="stat-box">
        <h2><?= $stats['nuls'] ?></h2>
        <p>Matchs nuls</p>
        <small><?= ($stats['total_matchs'] > 0) ? round(($stats['nuls'] / $stats['total_matchs']) * 100, 1) . '%' : '0%' ?></small>
    </div>
</div>

<h2>⏱ Top 5 des joueurs ayant en moyenne le plus jouées</h2>
<table>
    <thead>
        <tr>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Moyenne (minutes)</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($top_joueurs as $joueur): ?>
        <tr>
            <td><?= htmlspecialchars($joueur['nom']) ?></td>
            <td><?= htmlspecialchars($joueur['prenom']) ?></td>
            <td><?= round($joueur['avg_minutes'], 1) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
