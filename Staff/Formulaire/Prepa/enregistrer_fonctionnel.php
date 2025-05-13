<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $date = $_POST['date1'] ?? '';
    $nom = $_POST['nom1'] ?? '';
    $prenom = $_POST['prenom1'] ?? '';
    $squat = $_POST['squat1'] ?? '';
    $iso = $_POST['iso1'] ?? '';
    $souplesse = $_POST['souplesse1'] ?? '';
    $flamant = $_POST['flamant1'] ?? '';
    $haut = $_POST['haut1'] ?? '';

    // Connexion à la base de données
    $conn = new mysqli("localhost", "root", "", "asbh");

    if ($conn->connect_error) {
        die("Erreur de connexion : " . $conn->connect_error);
    }

    $stmt = $conn->prepare("INSERT INTO tests (date, nom, prenom, squat, iso, souplesse, flamant, haut) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $date, $nom, $prenom, $squat, $iso, $souplesse, $flamant, $haut);

    if ($stmt->execute()) {
        echo "✅ Données enregistrées avec succès.";
    } else {
        echo "❌ Erreur : " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
