<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../../bd.php';

// CSRF check
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Erreur de sécurité.");
}

// Get form values
$equipe_adverse = htmlspecialchars(trim($_POST['eq_advers'] ?? ''));
$lieu_match = htmlspecialchars(trim($_POST['lieu_match'] ?? ''));
$score_asbh = filter_input(INPUT_POST, 'sc_eq_asbh', FILTER_VALIDATE_INT);
$score_adverse = filter_input(INPUT_POST, 'sc_eq_adv', FILTER_VALIDATE_INT);
$id_equipe = filter_input(INPUT_GET, 'id_eq', FILTER_VALIDATE_INT);
$date_match = $_POST['date_match'] ?? date('Y-m-d');

// Optional basic check to avoid garbage
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_match)) {
    $date_match = date('Y-m-d'); // fallback to today
}

// Get player data
$id_joueurs = $_POST['id_joueur'] ?? [];
$mins_played = $_POST['mins_played'] ?? [];

// Establishing match winner
if($score_asbh > $score_adverse){
    $match_gagnant = 'asbh';
}
elseif($score_asbh < $score_adverse){
    $match_gagnant = 'adverse';
}
else{
    $match_gagnant = 'match null';
}

// Data validation
if (count($id_joueurs) !== count($mins_played)) {
    die("Données des joueurs incohérentes.");
}

// ✅ Replace missing minutes with 0
foreach ($mins_played as $index => $value) {
    if ($value === '') {
        $mins_played[$index] = 0;
    }
}

// Database insertion
if(isset($equipe_adverse) &&
    isset($score_asbh) &&
    isset($score_adverse) &&
    isset($id_equipe) &&
    isset($date_match) &&
    isset($id_joueurs) &&
    isset($mins_played) &&
    isset($lieu_match)){
    
    try {
        $pdo->beginTransaction();

        // Datas match
        $stmt_infos_match = $pdo->prepare("
            INSERT INTO infos_match (
                nom_equipe_adverse,
                lieu_match,
                date_match,
                score_equipe_adverse,
                score_equipe_asbh,
                match_gagnant,
                id_equipe
            ) VALUES (
                :nom_equipe_adverse,
                :lieu_match,
                :date_match,
                :score_equipe_adverse,
                :score_equipe_asbh,
                :match_gagnant,
                :id_equipe
            )
        ");

        $stmt_infos_match->execute([
            'nom_equipe_adverse' => $equipe_adverse,
            'lieu_match' => 'Domicile', // or retrieve from form
            'date_match' => $date_match,
            'score_equipe_adverse' => $score_adverse,
            'score_equipe_asbh' => $score_asbh,
            'match_gagnant' => $match_gagnant,
            'id_equipe' => $id_equipe
        ]);


        // Get id_match just inserted
        $id_match = $pdo->lastInsertId();
        $id_staff = $_SESSION['user_id'];
 
        // 2. Insert each player's minutes
        $stmt_analyse = $pdo->prepare("
            INSERT INTO analyse_joueur_match (minutes_joues, id_joueur, id_match, id_staff)
            VALUES (:minutes_joues, :id_joueur, :id_match, :id_staff)
        ");

        foreach ($id_joueurs as $index => $id_joueur) {
            $stmt_analyse->execute([
                'minutes_joues' => $mins_played[$index],
                'id_joueur' => $id_joueur,
                'id_match' => $id_match,
                'id_staff' => $id_staff
            ]);
        }

        $pdo->commit();
        echo "<p>✅ Match enregistré avec succès !</p>";

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        die("❌ Erreur lors de l'insertion : " . $e->getMessage());
    }
}
else{
    die('Invalid data, check for errors :(');
}
