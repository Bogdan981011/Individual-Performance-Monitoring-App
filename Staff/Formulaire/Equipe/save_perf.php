<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../../../bd.php';

// 0) CSRF protection
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die("Erreur de sécurité : jeton CSRF invalide.");
}

// 1) Retrieve and sanitize inputs
$equipe_adverse_raw = trim($_POST['eq_advers'] ?? '');
$lieu_match_raw     = trim($_POST['lieu_match'] ?? '');
$date_match_raw     = trim($_POST['date_match'] ?? '');

// 2) Validate equipe adverse (required, string length 1-100)
if ($equipe_adverse_raw === '' || mb_strlen($equipe_adverse_raw) > 100) {
    die("Nom de l'équipe adverse invalide (requis, max 100 caractères).");
}
$equipe_adverse = htmlspecialchars($equipe_adverse_raw, ENT_QUOTES, 'UTF-8');

// 3) Validate lieu match (required, string length 1-50)
if ($lieu_match_raw === '' || mb_strlen($lieu_match_raw) > 50) {
    die("Lieu du match invalide (requis, max 50 caractères).");
}
$lieu_match = htmlspecialchars($lieu_match_raw, ENT_QUOTES, 'UTF-8');

// 4) Validate date_match (YYYY-MM-DD, not future)
$date_match = $date_match_raw;
$date_obj   = DateTime::createFromFormat('Y-m-d', $date_match);
$today      = new DateTime('today');
if (!$date_obj || $date_obj->format('Y-m-d') !== $date_match) {
    die("Format de date invalide (attendu AAAA-MM-JJ).");
}
if ($date_obj > $today) {
    die("Date du match ne peut pas être dans le futur.");
}

// 5) Validate scores (integers >= 0)
$score_asbh    = filter_input(INPUT_POST, 'sc_eq_asbh', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
$score_adverse = filter_input(INPUT_POST, 'sc_eq_adv',  FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
if ($score_asbh === false || $score_adverse === false) {
    die("Les scores doivent être des nombres entiers positifs ou nuls.");
}

// 6) Determine match_gagnant
if ($score_asbh > $score_adverse) {
    $match_gagnant = 'asbh';
} elseif ($score_asbh < $score_adverse) {
    $match_gagnant = 'adverse';
} else {
    $match_gagnant = 'null';
}

// 7) Validate id_equipe from GET
$id_equipe = filter_input(INPUT_GET, 'id_eq', FILTER_VALIDATE_INT);
if ($id_equipe === false || $id_equipe <= 0) {
    die("Identifiant d'équipe invalide.");
}

// 8) Validate players and minutes arrays
$id_joueurs  = $_POST['id_joueur']   ?? [];
$mins_played = $_POST['mins_played'] ?? [];
if (!is_array($id_joueurs) || !is_array($mins_played) || count($id_joueurs) !== count($mins_played)) {
    die("Données des joueurs incohérentes.");
}

foreach ($id_joueurs as $idx => $jid) {
    // validate player id
    if (!filter_var($jid, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
        die("Identifiant de joueur invalide à l'index " . ($idx+1) . ".");
    }
    // validate minutes played
    $mins = $mins_played[$idx];
    if ($mins === '' || $mins === null) {
        $mins_played[$idx] = 0;
    } elseif (!ctype_digit((string)$mins) || (int)$mins < 0) {
        die("Minutes jouées invalides pour le joueur #" . ($idx+1) . ".");
    }
}

// All validations passed: proceed to database insertion
try {
    $pdo->beginTransaction();

    // Insert match info
    $stmt_match = $pdo->prepare("
        INSERT INTO info_match
        (nom_equipe_adverse, lieu_match, date_match,
         score_equipe_adverse, score_equipe_asbh,
         match_gagnant, id_equipe)
        VALUES
        (:nom_adv, :lieu, :date,
         :score_adv, :score_asbh,
         :gagnant, :id_eq)
    ");
    $stmt_match->execute([
        'nom_adv'    => $equipe_adverse,
        'lieu'       => $lieu_match,
        'date'       => $date_match,
        'score_adv'  => $score_adverse,
        'score_asbh' => $score_asbh,
        'gagnant'    => $match_gagnant,
        'id_eq'      => $id_equipe
    ]);

    // Insert each player's minutes
    $stmt_player = $pdo->prepare("
        INSERT INTO analyse_joueur_match (minutes_joues, id_joueur)
        VALUES (:mins, :jid)
    ");
    foreach ($id_joueurs as $idx => $jid) {
        $stmt_player->execute([
            'mins' => $mins_played[$idx],
            'jid'  => $jid
        ]);
    }

    $pdo->commit();
    header("Location: ../../section_perf_globale.php?id_eq=" . urlencode($id_equipe) . "&success=1");
    exit;


} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("❌ Erreur lors de l'enregistrement : " . $e->getMessage());
}
