<?php
session_start(); // Démarrer la session pour l'utilisateur
require_once '../../bd.php';

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer les données du formulaire
    $email = $_POST['mail'];
    $password = $_POST['mdp'];

        // Vérification des champs requis
    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo "Tous les champs sont obligatoires.";
        exit;
    }

    // Validation du format d'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo "Adresse e-mail invalide.";
        exit;
    }

   // Préparer la requête pour récupérer l'utilisateur basé sur l'e-mail
    try {
        $stmt = $pdo->prepare("SELECT id_staff, mdp, role FROM Staff WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        // Vérifier si l'utilisateur existe
        if ($stmt->rowCount() > 0) {
            // Récupérer l'utilisateur
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $hashed_password = $user['mdp'];
            $user_id = $user['id_staff'];
            $role = $user['role'];

            // Vérifier le mot de passe
            if (password_verify($password, $hashed_password)) {
                // Si le mot de passe est correct, créer une session
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_email'] = $email;
                $_SESSION['role'] = $role;
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                echo 'ok';
            } else {
                // Si le mot de passe est incorrect
                echo "email ou mot de passe incorrect.";
            }
        } else {
            // Si l'utilisateur n'existe pas
            echo "email ou mot de passe incorrect.";
        }
    } catch (PDOException $e) {
        // Gestion des erreurs
        echo "erreur lors de la récupération des données : " . $e->getMessage();
    }
}
?>
