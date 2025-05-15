<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom1']);
    $prenom = trim($_POST['prenom1']);
    $note = floatval($_POST['note1']);
    $testType = trim($_POST['testType']);
    $date = $_POST['date1'];

    try {
        // Connexion à la base de données (adapte les identifiants)
        $pdo = new PDO('mysql:host=localhost;dbname=nom_de_ta_base', 'utilisateur', 'motdepasse');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Préparation de la requête
        $stmt = $pdo->prepare("INSERT INTO tests (nom, prenom, note, test_type, date_test) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nom, $prenom, $note, $testType, $date]);

        echo "✅ Test enregistré avec succès.";
    } catch (PDOException $e) {
        echo "❌ Erreur lors de l'enregistrement : " . $e->getMessage();
    }
} else {
    echo "❌ Méthode non autorisée.";
}
?>
