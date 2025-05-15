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
      background-color: #f4f4f4;
      padding: 20px;
      position: relative;
    }

    h1 {
      text-align: center;
      color: var(--bleu);
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
      background-color: var(--bleu);
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
      background-color: var(--bleu);
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
  <script src="testfonctionnel.js"></script>
</head>
<body>

<a href="../../accueil_staff.html" class="return-btn">Retour à l’accueil</a>

<h1>Tableau de Tests Fonctionnels</h1>

<form method="post" action="enregistrer_fonctionnel.php">

  <div class="date-section">
    <label for="date">Date :</label>
    <input type="date" id="date" name="date" required><div class="error-message"></div>
  </div>

  <table>
    <thead>
      <tr>
        <th>Nom</th>
        <th>Prénom</th>
        <th>Squat d'Arraché</th>
        <th>ISO Leg Curl</th>
        <th>Souplesse Chaîne Postérieure</th>
        <th>Flamant Rose / Équilibre</th>
        <th>Souplesse Membres Supérieurs</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td style="display: none;"><input type="hidden" class="id_joueur" name="id_joueur" value="???"></td>
        <td><input type="text" name="nom" class="name"><div class="error-message"></div></td>
        <td><input type="text" name="prenom" class="name"><div class="error-message"></div></td>
        <td><input type="text" name="squat" class="note"><div class="error-message"></div></td>
        <td><input type="text" name="iso" class="note"><div class="error-message"></div></td>
        <td><input type="text" name="souplesse" class="note"><div class="error-message"></div></td>
        <td><input type="text" name="flamant" class="note"><div class="error-message"></div></td>
        <td><input type="text" name="haut" class="note"><div class="error-message"></div></td>
      </tr>

      <!-- <?php
      require_once '/../../../bd.php';

      try {
          $id_equipe = $_GET['id_eq'];

          $stmt = $pdo->prepare("
              SELECT nom, prenom, id_joueur
              FROM joueurs
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
                  <input type="text" name="nom" class="name" value="<?= htmlspecialchars($joueur['nom']) ?>">
                </td>

                <td>
                  <input type="text" name="prenom" class="name" value="<?= htmlspecialchars($joueur['prenom']) ?>">
                </td>

                <td>
                  <input type="text" name="squat" class="note">
                </td>

                <td>
                  <input type="text" name="iso" class="note">
                </td>

                <td>
                  <input type="text" name="souplesse" class="note">
                </td>

                <td>
                  <input type="text" name="flamant" class="note">
                </td>

                <td>
                  <input type="text" name="haut" class="note">
                </td>
              </tr>
              <?php
          }

      } catch (PDOException $e) {
          echo "Erreur : " . $e->getMessage();
      }
      ?> -->


    </tbody>
  </table>

  <div style="text-align: center;">
    <button type="submit">Enregistrer</button>
  </div>
</form>

</body>
</html>
