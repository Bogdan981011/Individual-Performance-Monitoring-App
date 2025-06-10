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
        FROM staff
        WHERE id_staff = :id_staff
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
    <link rel="stylesheet" href="../Styles/section.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>

    <!-- Bouton retour -->
    <div class="header">
        <a href="..\deconnexion.php" class="btn-retour">Déconnexion</a>
    </div>

    <!-- Container principal -->
    <div class="container">
        <!-- Section des options -->
        
        <!-- Message de bienvenue avec effet pop-up -->
        <h2 class="welcome-message">Bienvenue <?= $staff['prenom'] . ' ' . $staff['nom'] ?></h2>
        
        <div class="option-section">
            <a href="Equipe/Crabos/crabos.php" class="btn-option">CRABOS</a>
            <a href="Equipe/CadetA/cadetA.php" class="btn-option">CADETS A</a>
            <a href="Equipe/CadetB/cadetB.php" class="btn-option">CADETS B</a>
            <a href="Equipe/Espoirs/espoirs.php" class="btn-option">ESPOIRS</a>
            <a href="Nouveau/creer.html" class="btn-option">Gérer les accès</a>
            <a href="Modification/modif_staff.php" class="btn-option">Changer de mot de passe</a>
        </div>
    </div>

    <!-- Icône pour ouvrir le chatbot (supprimée car remplacée par l'image) -->
    <!-- <div id="chatbot-toggle">
        <i class="fas fa-robot"></i>
        <span id="chat-bubble"><i class="fas fa-comment"></i></span>
    </div> -->
<!-- Logo au-dessus du container -->
        <div id="chatbot-toggle">
            <!-- Ici on remplace l'icône par l'image du chatbot -->
            <img src="../Images/logochatbot.png" alt="Chatbot Rugby Ball" id="chatbot-image">
        </div>
    <!-- Container du chatbot -->
    <!-- Nouveau container avec iframe Gradio -->
    <div id="chat-container">
        <iframe 
            src="http://127.0.0.1:7861"
            width="100%" 
            height="100%" 
            style="border: none; border-radius: 10px;">
        </iframe>
    </div>


    <script>
        const toggleButton = document.getElementById('chatbot-toggle');
        const chatContainer = document.getElementById('chat-container');

        // Forcer fermeture au départ
        chatContainer.style.display = 'none';

        toggleButton.addEventListener('click', () => {
            if (chatContainer.style.display === 'none' || chatContainer.style.display === '') {
                chatContainer.style.display = 'block';
            } else {
                chatContainer.style.display = 'none';
            }
        });


        function sendMessage() {
            const input = document.getElementById('chat-input');
            const message = input.value.trim();
            if (message === '') return;

            const chatContent = document.getElementById('chat-content');
            const userMessage = document.createElement('p');
            userMessage.innerHTML = `<strong>Vous :</strong> ${message}`;
            chatContent.appendChild(userMessage);

            // Simuler une réponse automatique du bot
            const botMessage = document.createElement('p');
            botMessage.innerHTML = `<strong>JobBot :</strong> Merci pour votre message, nous reviendrons vers vous rapidement.`;
            chatContent.appendChild(botMessage);

            // Scroll vers le bas pour voir le dernier message
            chatContent.scrollTop = chatContent.scrollHeight;

            input.value = ''; // Effacer l'input
        }
    </script>
</body>
</html>
