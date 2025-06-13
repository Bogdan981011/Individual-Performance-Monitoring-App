<?php
include( '../bd.php');
 // Assure-toi que ce fichier contient bien la connexion PDO : $conn
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



if (!isset($_SESSION['user_id'])) {
    // Rediriger ou bloquer l'accès
    die("Accès non autorisé.");
}

$userId = $_SESSION['user_id'];

// Déterminer le rôle si pas encore défini
if (!isset($_SESSION['role'])) {
    $stmt = $pdo->prepare("SELECT role FROM staff WHERE id_staff = ?");

    $stmt->execute([$userId]);
    $staff = $stmt->fetch();

    $_SESSION['role'] = $staff ? 'staff' : 'joueur';
}

$role = $_SESSION['role'];
?>

<style>
    #chatbot-toggle {
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 70px;
        height: 70px;
        background-color: #f5f7f9;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 9999;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    #chatbot-toggle img {
        width: 40px;
        height: 40px;
        object-fit: cover;
    }

    #chatbot-toggle:hover {
        transform: scale(1.1) rotate(5deg);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
    }

    #chat-container {
        position: fixed;
        bottom: 100px;
        right: 20px;
        width: 360px;
        height: 500px;
        background: linear-gradient(135deg, #ecf0f1, #bdc3c7);
        border: 1px solid #7f8c8d;
        border-radius: 10px;
        display: none;
        z-index: 9998;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        overflow: hidden;
    }

    #chat-container iframe {
        width: 100%;
        height: 100%;
        border: none;
        border-radius: 10px;
    }

    #chat-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        z-index: 9997;
    }
</style>

<!-- Bouton flottant du chatbot -->
<div id="chatbot-toggle">
   <img src="http://localhost/vizia/Images/logochatbot.png" alt="Chatbot" />

</div>

<!-- Overlay pour fermer le chatbot -->
<div id="chat-overlay"></div>

<!-- Conteneur iframe du chatbot --> 
<div id="chat-container">
    <iframe src="http://127.0.0.1:7860?userId=<?php echo urlencode($userId); ?>&role=<?php echo urlencode($role); ?></iframe>
</div>


<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggleButton = document.getElementById('chatbot-toggle');
    const chatContainer = document.getElementById('chat-container');
    const chatOverlay = document.getElementById('chat-overlay');

    // Masquer le chatbot au démarrage
    if (chatContainer) chatContainer.style.display = 'none';
    if (chatOverlay) chatOverlay.style.display = 'none';

    if (toggleButton && chatContainer && chatOverlay) {
        toggleButton.addEventListener('click', () => {
            const isVisible = chatContainer.style.display === 'block';
            chatContainer.style.display = isVisible ? 'none' : 'block';
            chatOverlay.style.display = isVisible ? 'none' : 'block';
        });

        chatOverlay.addEventListener('click', () => {
            chatContainer.style.display = 'none';
            chatOverlay.style.display = 'none';
        });
    }
});
</script>

