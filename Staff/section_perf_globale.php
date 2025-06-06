<?php 
session_start(); 
if (!isset($_SESSION['user_id'])) {
    // L'utilisateur n'est pas connecté, on le redirige
    header("Location: /vizia/accueil.html");
    exit;
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
<body>
    <?php 
    require_once "../bd.php";
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
                <a href="section_perf_globale.php?id_eq=<?= $equipe['id_equipe'] ?>"
                class="ruban-link <?= $activeClass ?>"
                id="<?= strtolower(str_replace(' ', '', $nomEquipe)) ?>">
                <?= $nomEquipe ?>
                </a>
            <?php endforeach; ?>
            <a href="accueil_staff.php" class="btn-retour">Accueil</a>
        </div>
    </div>

    

    <div class="container">
        <div class="logo-section">
          <img src="../Images/logo.svg" alt="Logo ASBH" class="logo central-logo">
    </div>
    
    <!-- Section des options -->
    <div class="option-section">
        <a href="Formulaire/Equipe/perf_globale.php?id_eq=<?= $id_equipe ?>" class="btn-option">Entrer Données</a>
        <a href="FormulaireReponses/stat_perf_globale.php?id_eq=<?= $id_equipe ?>" class="btn-option">Statistiques</a>
    </div>
    

</body>
</html>