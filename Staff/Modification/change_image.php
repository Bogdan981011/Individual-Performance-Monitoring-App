<?php
    session_start();
    require_once '../../bd.php';

    // Vérifie si une photo a bien été envoyée
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        echo "Erreur lors de l'envoi du fichier.";
        exit;
    }

    // Vérifie que l'ID du joueur est fourni
    if (!isset($_POST['id_joueur']) || !is_numeric($_POST['id_joueur'])) {
        echo "ID joueur manquant ou invalide.";
        exit;
    }

    $idJoueur = intval($_POST['id_joueur']);
    $originalName = $_FILES['photo']['name'];
    $tmpName = $_FILES['photo']['tmp_name'];
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    // Vérifie l'extension
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'tiff', 'tif', 'heic', 'heif', 'raw', 'bmp', 'pdf'];
    if (!in_array($extension, $allowedExtensions)) {
        echo "Extension de fichier non autorisée.";
        exit;
    }

    $uploadDir = __DIR__ . '/../../Image_joueur/';
    $baseUrl = '/vizia/Image_joueur/';
    $filename = $idJoueur . '.' . $extension;
    $destination = $uploadDir . $filename;

    // Supprime les anciennes images pour ce joueur (toutes extensions confondues)
    foreach ($allowedExtensions as $ext) {
        $oldFile = $uploadDir . $idJoueur . '.' . $ext;
        if (file_exists($oldFile)) {
            unlink($oldFile);
        }
    }

    // Déplace le nouveau fichier
    if (!move_uploaded_file($tmpName, $destination)) {
        echo "Erreur lors du déplacement du fichier.";
        exit;
    }

    // Met à jour l’URL de la photo dans la base de données
    $photoUrl = $baseUrl . $filename;
    $stmt = $pdo->prepare("UPDATE joueur SET photo_url = :url WHERE id_joueur = :id");
    $stmt->execute([
        ':url' => $photoUrl,
        ':id' => $idJoueur
    ]);

    echo "ok";
    exit;
?>