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
        FROM joueur
        WHERE id_joueur = :id_staff
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
    <link rel="stylesheet" href="Styles/section.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
</head>

<body>
    <?php include __DIR__ . '/../chatbot/chatbot.php'; ?>
    <!-- Bouton retour -->
    <div class="header">
        <a href="../deconnexion.php" class="btn-retour">Déconnexion</a>
    </div>

    <!-- Container principal -->
    <div class="container">
        <!-- Logo au-dessus du container -->
        <div class="logo-section">
            <img src="../Images/logo.svg" alt="Logo ASBH" class="central-logo">
        </div>

        <!-- Message de bienvenue avec effet pop-up -->
        <h2 class="welcome-message">Bienvenue <?= $staff['prenom'] . ' ' . $staff['nom'] ?></h2>

        <!-- Section des options -->
        <div class="option-section">
            <a href="Fiche_Joueur/performance.php" class="btn-option">Performance</a>
            <a href="Formulaire/formulaires.html" class="btn-option">Formulaires</a>
            <a href="Modification/modif.php" class="btn-option">Changer de mot de passe</a>
            <a href="informations.html" class="btn-option">Informations</a>
        </div>
    </div>

  


</body>
</html>
