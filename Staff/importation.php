<?php
// Démarre la session pour accéder au message
session_start();
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Importer un fichier</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background-color: #f4f4f4;
        }

        .header {
            background-color: #CC0A0A;
            padding: 15px 30px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }

        .btn-retour {
            color: white;
            text-decoration: none;
            font-size: 16px;
            background-color: #990000;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .btn-retour:hover {
            background-color: #660000;
            transform: scale(1.05);
        }

        .logo-section {
            text-align: center;
            margin: 30px 0 10px 0;
        }

        .central-logo {
            height: 90px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto 50px auto;
            background-color: white;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
        }

        h2 {
            color: #CC0A0A;
            margin-bottom: 30px;
            font-weight: 600;
        }

        input[type="file"] {
            font-size: 16px;
            margin-bottom: 20px;
        }

        .btn-option {
            background-color: #CC0A0A;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.2s ease, background-color 0.3s ease;
        }

        .btn-option:hover {
            transform: scale(1.05);
            background-color: #990000;
        }

        .message {
            margin-top: 20px;
            font-weight: bold;
            font-size: 16px;
            color: #006600;
        }

        .message.error {
            color: #CC0000;
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="sectiontests.html" class="btn-retour">⬅ Retour</a>
    </div>

    <div class="logo-section">
        <img src="../Images/logo.svg" alt="Logo ASBH" class="central-logo">
    </div>

    <div class="container">
        <h2>Importer un fichier</h2>

        <?php if ($message): ?>
            <div class="message <?= str_contains($message, '❌') || str_contains($message, '❗') ? 'error' : '' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form action="upload.php" method="post" enctype="multipart/form-data">
            <input type="file" name="fichier" required>
            <br>
            <button type="submit" class="btn-option">Importer</button>
        </form>
    </div>
</body>
</html>
