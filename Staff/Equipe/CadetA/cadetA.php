<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Section</title>
    <link rel="stylesheet" href="../../../Styles/section.css">
</head>
<body>
    <!-- Ruban fixe en haut -->
    <div class="header-ruban">
        <div class="ruban-section">
            <a href="../Crabos/crabos.php" class="ruban-link" id="crabos">CRABOS</a>
            <a href="cadetA.php" class="ruban-link active" id="cadetsA">CADETS A</a>
            <a href="../CadetB/cadetB.php" class="ruban-link" id="cadetsB">CADETS B</a>
            <a href="../Espoirs/espoirs.php" class="ruban-link" id="espoirs">ESPOIRS</a>
        </div>
    </div>

    <!-- Déconnexion -->
    <div class="header">
        <a href="../../accueil_staff.html" class="btn-retour">Retour à l'accueil</a>
    </div>

    <div class="container">
        <div class="logo-section">
            <img src="../../../Images/logo.svg" alt="Logo ASBH" class="logo central-logo">
        </div>
    
        <!-- Section des options -->
        <?php
        require_once '../../../bd.php'; 

        $nomEquipe = 'cadets a';
        $sql = "SELECT id_equipe FROM Equipe WHERE nom_equipe COLLATE utf8_general_ci = :nom";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nom', $nomEquipe, PDO::PARAM_STR);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        ?>

        <div class="option-section">
            <a href="../../perf_globale.php?id_eq=<?= $result['id_equipe'] ?>" class="btn-option">Performance Globale</a>
            <a href="joueurs_cadetA.php" class="btn-option">Liste des joueurs</a>
            <a href="../../sectiontests.php?id_eq=<?= $result['id_equipe'] ?>" class="btn-option">Tests</a>
            <a href="../../FormulaireReponses/choix_formulaire.php?id_eq=<?= $result['id_equipe'] ?>" class="btn-option">Réponses aux formulaires</a>
        </div>
    </div>
</body>
</html>
