<?php 
session_start(); 
if (!isset($_SESSION['user_id'])) {
    // L'utilisateur n'est pas connecté, on le redirige
    header("Location: /vizia/accueil.html");
    exit;
}

require_once '../bd.php';

try {
    $stmt = $pdo->prepare("
        SELECT nom, prenom
        FROM staff
        WHERE id_staff = :id_staff
    ");

    $stmt->execute(['id_staff' => $_SESSION['user_id'] ]);
    $staff = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Section</title>
    <link rel="stylesheet" href="../Styles/section.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<?php include('../chatbot/chatbot.php'); ?>
<body>

    <!-- Bouton retour -->
    <div class="header">
        <a href="..\deconnexion.php" class="btn-retour">Déconnexion</a>
    </div>

    <!-- Container principal -->
    <div class="container">
        <!-- Section des options -->
        
        <!-- Message de bienvenue avec effet pop-up -->
        <h2 class="welcome-message">Bienvenue <?= $staff['prenom'] . ' ' . $staff['nom'] ?></h2>
        
        <div class="option-section">
            <a href="Equipe/Crabos/crabos.php" class="btn-option">CRABOS</a>
            <a href="Equipe/CadetA/cadetA.php" class="btn-option">CADETS A</a>
            <a href="Equipe/CadetB/cadetB.php" class="btn-option">CADETS B</a>
            <a href="Equipe/Espoirs/espoirs.php" class="btn-option">ESPOIRS</a>
            <a href="Nouveau/creer.php" class="btn-option">Gérer les accès</a>
            <a href="Modification/modif_staff.php" class="btn-option">Changer de mot de passe</a>
        </div>
    </div>
</body>
</html>
