<?php
include '../../bd.php';  // connexion PDO dans $conn

session_start();
$id_joueur = $_SESSION['user_id'];

if (!$id_joueur) {
    echo "<p class='error'>ID joueur manquant.</p>";
    exit;
}

// Préparation et exécution de la requête pour récupérer la dernière réponse
$sql = "SELECT * FROM rpe_form WHERE id_joueur = ? ORDER BY id_RPE DESC LIMIT 1";
$stmt = $pdo->prepare($sql);

if (!$stmt->execute([$id_joueur])) {
    echo "<p class='error'>Erreur lors de la récupération des données.</p>";
    exit;
}

$donnees = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$donnees) {
    echo "<p class='error'>Aucune réponse trouvée pour ce joueur.</p>";
    exit;
}

// Extraction et sécurisation des données
$type_entrainement = htmlspecialchars($donnees['type_entrainement']);
$temps_entrainement = htmlspecialchars($donnees['temps_entrainement']);
$difficulte = htmlspecialchars($donnees['difficulte']);
$observations = htmlspecialchars($donnees['observations']);

// Maintenant tu peux utiliser ces variables pour les afficher ou les traiter
echo "Dernière réponse récupérée avec succès.<br>";
echo "Type entraînement : $type_entrainement<br>";
echo "Temps entraînement : $temps_entrainement<br>";
echo "Difficulté : $difficulte<br>";
echo "Observations : $observations<br>";
?>



    require_once '../../../bd.php';

    try {
        $stmt = $pdo->prepare("
          SELECT nom, prenom, id_joueur
          FROM joueur
          JOIN equipe ON joueur.id_equipe = equipe.id_equipe
        ");