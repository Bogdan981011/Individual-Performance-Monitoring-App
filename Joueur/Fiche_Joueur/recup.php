<?php
session_start();
$host = 'localhost';
$dbname = 'vizia';
$username = 'root';
$password = 'root';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

if (isset($_SESSION['role'])) {
    
    $user_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($user_id === false || $user_id === null) {
        
        $equipe = filter_input(INPUT_GET, 'eq', FILTER_SANITIZE_STRING);

        echo "<p>Une erreur est survenue. Redirection en cours...</p>";
        echo '<script>
        const equipe = "' . $equipe . '";
        setTimeout(() => {
                    if (equipe === "A") {
                        window.location.href = `/vizia/Staff/Equipe/CadetA/joueurs_cadetA.php`;
                    } else if (equipe === "B") {
                        window.location.href = `/vizia/Staff/Equipe/CadetB/joueurs_cadetB.php`;
                    } else if (equipe === "C") {
                        window.location.href = `/vizia/Staff/Equipe/Crabos/joueurs_crabos.php`;
                    }else if (equipe === "E") {
                        window.location.href = `/vizia/Staff/Equipe/Espoirs/joueurs_espoirs.php`;
                    } else {
                        window.location.href = `/vizia/Staff/accueil_staff.html`;
                    }
                }, 1000); // Fermer setTimeout ici
        </script>';
        exit;
    }

} else $user_id = $_SESSION['user_id']; 

try {
    // Données joueur
    $stmt = $pdo->prepare("
        SELECT nom, prenom, poste, annee
        FROM joueur
        WHERE id_joueur = :id
    ");
    $stmt->execute(['id' => $user_id]);
    $joueurs = $stmt->fetch(PDO::FETCH_ASSOC);

    // Tests fonctionnels
    $stmt = $pdo->prepare("SELECT squat_arrache, iso_leg_curl, souplesse_chaine_post, flamant_rose, souplesse_membres_supérieurs, date_test FROM tests_fonctionnnels WHERE id_joueur = :id ORDER BY date_test DESC");
    $stmt->execute(['id' => $user_id]);
    $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupération de toutes les mesures (poids, taille, img, gmc) ordonnées par date descendante
    $stmt = $pdo->prepare("SELECT type_mesure, valeur, date_mesure FROM mesure WHERE id_joueur = :id AND type_mesure IN ('poids', 'taille', 'img', 'gmc') ORDER BY date_mesure DESC");
    $stmt->execute(['id' => $user_id]);
    $mesures_temps = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Initialiser les dernières mesures à null
    $dernieres_mesures = [
        'poids' => null,
        'taille' => null,
        'img' => null,
        'gmc' => null
    ];

    // Parcourir les mesures à l'envers pour obtenir la plus récente
    $mesures_temps = array_reverse($mesures_temps);

    foreach ($mesures_temps as $m) {
        $type = $m['type_mesure'];
        $valeur = $m['valeur'];

        // Vérifier si la mesure est attendue et si elle est valide (≠ null ou 0)
        if (array_key_exists($type, $dernieres_mesures)) {
            if ($dernieres_mesures[$type] === null && $valeur !== null && floatval($valeur) != 0) {
                $dernieres_mesures[$type] = $valeur;
            }
        }
    }

    // Tests physiques
    $stmt = $pdo->prepare("SELECT type_test, mesure_test, date_test 
                                    FROM tests_physiques 
                                    WHERE id_joueur = :id
                                    AND mesure_test IS NOT NULL 
                                    AND mesure_test != 0");
    $stmt->execute(['id' => $user_id]);
    $mesures = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Graph preprocessing data
    $graph_data = [];
    foreach ($mesures as $entry) {
        $type = $entry['type_test'];
        $graph_data[$type][] = [
            'date' => date('Y-m-d', strtotime($entry['date_test'])),
            'value' => floatval($entry['mesure_test'])
        ];
    }


    // Données médicales
    $stmt = $pdo->prepare("SELECT date_blessure, type_blessure, gravite, recommandation, reprise FROM medical_form WHERE id_joueur = :id ORDER BY date_blessure DESC LIMIT 1");
    $stmt->execute(['id' => $user_id]);
    $medicalData = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur base de données : " . $e->getMessage());
}
$historique_mesures = [
    'poids' => ['dates' => [], 'valeurs' => []],
    'taille' => ['dates' => [], 'valeurs' => []],
    'img' => ['dates' => [], 'valeurs' => []]
];

$stmt = $pdo->prepare("
    SELECT type_mesure, valeur, date_mesure 
    FROM mesure 
    WHERE id_joueur = :id 
      AND type_mesure IN ('poids', 'taille', 'img') 
      AND valeur IS NOT NULL 
      AND valeur != 0 
    ORDER BY date_mesure ASC
");
$stmt->execute(['id' => $user_id]);
$mesures_all = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($mesures_all as $m) {
    $type = $m['type_mesure'];
    $valeur = floatval($m['valeur']);
    $date = $m['date_mesure'];

    if (!isset($historique_mesures[$type])) continue;

    $historique_mesures[$type]['dates'][] = $date;
    $historique_mesures[$type]['valeurs'][] = $valeur;
}
?>
