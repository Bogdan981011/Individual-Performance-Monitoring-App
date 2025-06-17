<?php 
session_start(); 
if (!isset($_SESSION['user_id'])) {
    // L'utilisateur n'est pas connecté, on le redirige
    header("Location: /vizia/accueil.html");
    exit;
}
?>
<?php include('../../../chatbot/chatbot.php'); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Test Physique - ASBH</title>
  <style>
    :root {
      --bleu: #190C63;
      --rouge: #CC0A0A;
      --orange: #F9A825;
      --vert: #00C853;
    }

    body {
      font-family: Arial, sans-serif;
      background-color: #FBEAEA;
      padding: 20px;
      position: relative;
    }

    h1 {
      text-align: center;
      color: var(--rouge);
    }

    h2 {
      text-transform: uppercase;
      text-align: center;
      color: var(--rouge);
    }

    /* Style du bouton retour */
    .return-btn {
        position: fixed; /* Reste fixe même lors du défilement */
        top: 20px; /* Positionne le bouton à 20px du haut */
        right: 20px;
        background-color: var(--rouge); /* Rouge pour le bouton */
        color: white;
        padding: 8px 14px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: bold;
        text-decoration: none; /* Enlève le soulignement */
        transition: background-color 0.3s;
        z-index: 1000; /* S'assure que le bouton soit au-dessus des autres éléments */
    }

    .return-btn:hover {
        background-color: #0e0640; /* Changement de couleur au survol */
    }


    .date-section {
      margin-bottom: 20px;
      text-align: center;
    }

    table {
      width: 80%;
      border-collapse: collapse;
      background-color: white;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      margin: 0 auto;
    }

    th, td {
      border: 1px solid #ddd;
      padding: 8px;
      text-align: center;
    }

    th {
      background-color: var(--rouge);
      color: white;
    }

    input[type="text"], input[type="date"] {
      width: 90%;
      padding: 4px;
      border: 1px solid #ccc;
      border-radius: 4px;
      text-align: center;
      font-weight: bold;
    }
    
    .fakeinput{
      padding: 4px 25px;
      border: 1px solid #ccc;
      border-radius: 4px;
      text-align: center;
      font-weight: bold;
    }

    .A { background-color: var(--vert); color: white; }
    .EA { background-color: var(--orange); color: black; }
    .NA { background-color: var(--rouge); color: white; }

    .error-message {
      color: red;
      font-size: 12px;
      margin-top: 4px;
      text-align: center;
    }

    button[type="submit"] {
      padding: 10px 20px;
      background-color: var(--rouge);
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: bold;
      margin-top: 20px;
      
    }
    button[type="submit"]:hover {
      background-color: #a10808;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      table { width: 100%; font-size: 14px; }
      .return-btn { font-size: 12px; padding: 6px 10px; }
      input[type="text"], input[type="date"] { width: 95%; padding: 6px; font-size: 14px; }
      th, td { padding: 6px; }
      h1 { font-size: 24px; }
    }

    @media (max-width: 480px) {
      .return-btn { top: 10px; right: 10px; }
      h1 { font-size: 20px; }
      input[type="text"], input[type="date"] { font-size: 12px; }
      table { width: 100%; font-size: 12px; }
      .error-message { font-size: 10px; }
    }
  </style>
  <script src="testphysique.js"></script>
</head>
  <body>
  <?php
  require_once '../../../bd.php';
  $id_equipe = filter_input(INPUT_GET, 'id_eq', FILTER_VALIDATE_INT);
  if ($id_equipe === false) {
    echo "<p>Une erreur est survenue. Redirection...</p>";
    echo "<script>setTimeout(() => window.location.href = '../../accueil_staff.php', 1000);</script>";
    exit;
  }

  $stmt = $pdo->prepare("SELECT nom_equipe FROM equipe WHERE id_equipe =:id");
  $stmt -> execute(['id' => $id_equipe]);
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  ?>

  <a href="../../sectiontests.php?id_eq=<?= $id_equipe ?>" class="return-btn">Retour à la selection du test</a>
  <div class="main-container">
    <h1>Test Physique</h1>
    <h2><?= htmlspecialchars($result['nom_equipe']) ?></h2>

    <form action="enregistrer_test.php" method="POST">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      
      <div class="search-bar">
        <input type="text" placeholder="Rechercher un nom ou prénom...">
      </div>

      <div class="date-section-flex">
        <div class="date-field">
          <label for="date1">Date :</label>
          <input type="date" id="date" name="date"><span class="error-message"></span>
        </div>

        <div class="select-test">
          <label for="testType">Test :</label>
          <select id="testType" name="testType" required>
            <option value="" disabled selected>─── Choisir un test ───</option>
            <option value="PESEE">Pesée</option>
            <option value="SQUAT">Squat Nuque</option>
            <option value="BROADJUMP">BROADJUMP</option>
            <option value="DC">DC</option>
            <option value="TP">TP</option>
            <option value="10 m">10m</option>
            <option value="20 m">20m</option>
            <option value="BRONCO">BRONCO</option>
            <option value="YOYO">YOYO</option>
            <option value="RFU">RFU Test Avant</option>
            <option value="CMJ">CMJ</option>
            <option value="img">IMG</option>
            <option value="taille">Taille</option>
            <option value="poids">Poids</option>
          </select>
        </div>
      </div>

      <table>
        <thead>
          <tr>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Valeur</th>
          </tr>
        </thead>
        <tbody>
        <?php
        require_once '../../../bd.php';

        try {
          $stmt = $pdo->prepare("
            SELECT nom, prenom, id_joueur
            FROM joueur
            WHERE id_equipe = :id_equipe
          ");

          $stmt->execute(['id_equipe' => $id_equipe]);
          $joueurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

          foreach ($joueurs as $joueur) {
            ?>
            <tr>
              <td style="display: none;">
                <input type="hidden" class="id_joueur" name="id_joueur" value="<?= htmlspecialchars($joueur['id_joueur']) ?>">
              </td>
              <td>
                <span class="fakeinput"><?= htmlspecialchars($joueur['nom']) ?></span>
              </td>
              <td>
                <span class="fakeinput"><?= htmlspecialchars($joueur['prenom']) ?></span>
              </td>
              <td>
                <input type="number" name="note" class="note" min="0" step="0.01">
                <div class="error-message"></div>
              </td>
            </tr>
            
            <?php 
          }

        } catch (PDOException $e) {
          echo "Erreur : " . $e->getMessage();
        }
        ?>

        </tbody>
      </table>

      <!--  BOUTON ENREGISTRER -->
      <div style="text-align: center; margin-top: 20px;">
        <button type="submit">Enregistrer</button>
      </div>

  
    </form>
  </div>

</body>
</html>
