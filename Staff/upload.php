<?php
if (isset($_FILES['fichier']) && $_FILES['fichier']['error'] == 0) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $uploadFile = $uploadDir . basename($_FILES['fichier']['name']);

    if (move_uploaded_file($_FILES['fichier']['tmp_name'], $uploadFile)) {
        // Stocke un message de succès dans la session
        session_start();
        $_SESSION['message'] = "✅ Fichier importé avec succès : " . htmlspecialchars($_FILES['fichier']['name']);
    } else {
        session_start();
        $_SESSION['message'] = "❌ Erreur lors de l'importation.";
    }
} else {
    session_start();
    $_SESSION['message'] = "❗ Aucun fichier sélectionné ou erreur lors de l'envoi.";
}

// Redirige vers la page d'importation
header("Location: importation.php");
exit();
?>
