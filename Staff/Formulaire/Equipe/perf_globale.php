<?php 
session_start(); 
if (!isset($_SESSION['user_id'])) {
    // L'utilisateur n'est pas connecté, on le redirige
    header("Location: /vizia/accueil.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Test Fonctionnels - ASBH</title>
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

    .return-btn {
      position: absolute;
      top: 20px;
      right: 20px;
      background-color: var(--rouge);
      color: white;
      padding: 8px 14px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      text-decoration: none;
      font-weight: bold;
      transition: background-color 0.3s;
    }

    .return-btn:hover {
      background-color: #0e0640;
    }

    .date-section .row {
      display: flex;
      justify-content: center;
      gap: 20px;
      margin-bottom: 15px;
      flex-wrap: wrap;
    }

    .date-section .input-group {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      min-width: 150px;
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
  <script src="perf_globale.js"></script>
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

  <a href="../../section_perf_globale.php?id_eq=<?= $id_equipe ?>" class="return-btn">Retour à la section</a>

  <h1>Performance de l'équipe <br><?= htmlspecialchars($result['nom_equipe']) ?></h1>

  <form method="post" action="save_perf.php?id_eq=<?= $id_equipe ?>">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

    <div class="date-section">
      <div class="row">
        <div class="input-group">
          <label for="eq_advers">Nom Équipe Adversaire :</label>
          <input type="text" id="eq_advers" name="eq_advers" required>
          <div class="error-message"></div>
        </div>
        <div class="input-group">
          <label for="lieu_match">Lieu Match :</label>
          <input type="text" id="lieu_match" name="lieu_match" required>
          <div class="error-message"></div>
        </div>
      </div>

      <div class="row">
        <div class="input-group">
          <label for="date_match">Date du match :</label>
          <input type="date" id="date_match" name="date_match" required>
          <div class="error-message"></div>
        </div>
        <div class="input-group">
          <label for="sc_eq_asbh">Score Equipe ASBH :</label>
          <input type="number" id="sc_eq_asbh" name="sc_eq_asbh" min="0" required>
          <div class="error-message"></div>
        </div>
        <div class="input-group">
          <label for="sc_eq_adv">Score Adversaire :</label>
          <input type="number" id="sc_eq_adv" name="sc_eq_adv" min="0" required>
          <div class="error-message"></div>
        </div>
      </div>
    </div>  


    <table>
      <thead>
        <tr>
          <th>Nom</th>
          <th>Prénom</th>
          <th>Minutes Joués</th>
        </tr>
      </thead>
      <tbody>
        <?php
        try {
          $stmt = $pdo->prepare("
            SELECT nom, prenom, id_joueur, validite
            FROM joueur
            WHERE id_equipe = :id_equipe
          ");

          $stmt->execute(['id_equipe' => $id_equipe]);
          $joueurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

          foreach ($joueurs as $joueur) {
            if ($joueur['validite'])
            ?>
            <tr>
              <td style="display: none;">
                <input type="hidden" class="id_joueur" name="id_joueur[]" value="<?= htmlspecialchars($joueur['id_joueur']) ?>">
              </td>

              <td><?= htmlspecialchars($joueur['nom']) ?></td>

              <td><?= htmlspecialchars($joueur['prenom']) ?></td>

              <td>
                <input type="number" id="mins" name="mins_played[]" min="0" placeholder="0 si pas marqué" style ="text-align: center"><div class="error-message"></div>
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

    <div style="text-align: center;">
      <button type="submit">Enregistrer</button>
    </div>
  </form>
</body>
</html>
