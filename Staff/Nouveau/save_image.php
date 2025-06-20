<?php
    session_start();

    require_once '../../bd.php';

    // Dossier de destination
    $uploadDir = __DIR__ . '/../../Image_joueur/'; // Chemin absolu vers le dossier
    $baseUrl = '/vizia/Image_joueur/'; // Pour enregistrer dans la BDD

    // S'assurer que le dossier existe
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    foreach ($_FILES['photo']['tmp_name'] as $index => $tmpName) {
        if ($_FILES['photo']['error'][$index] === UPLOAD_ERR_OK) {
            $idJoueur = $_POST['id_joueur'][$index];
            $originalName = $_FILES['photo']['name'][$index];
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            // Sécuriser l'extension
            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'tiff', 'tif', 'heic', 'heif', 'raw', 'bmp', 'pdf'])) {
                continue; // ou gérer avec un message d’erreur
            }

            $filename = $idJoueur . '.' . $extension;
            $destination = $uploadDir . $filename;

            if (move_uploaded_file($tmpName, $destination)) {
                // Mettre à jour la base de données
                $photoUrl = $baseUrl . $filename;
                $stmt = $pdo->prepare("UPDATE joueur SET photo_url = :url WHERE id_joueur = :id");
                $stmt->execute([
                    ':url' => $photoUrl,
                    ':id' => $idJoueur
                ]);
            }
        } else {
            echo "Erreur lors de l'envoi du fichier.";
            exit;
        }
    }

    echo "ok";
    exit;
?>

