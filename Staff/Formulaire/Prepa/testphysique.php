<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Test Physique - ASBH</title>
  <style> 
    :root {
      --bleu: #190C63;
      --rouge: #CC0A0A;
      --gris: #f4f4f4;
    }

    body {
      font-family: Arial, sans-serif;
      background-color: var(--gris);
      margin: 0;
      padding: 0;
      display: flex;
    }

    .main-container {
      flex: 1;
      padding: 20px;
    }

    h1 {
      text-align: center;
      color: var(--bleu);
    }

    .search-bar {
      text-align: center;
      margin-bottom: 20px;
    }

    .search-bar input[type="text"] {
      width: 60%;
      padding: 8px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    table {
      width: 80%;
      margin: 0 auto;
      background-color: white;
      border-collapse: collapse;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    th, td {
      padding: 10px;
      border: 1px solid #ddd;
      text-align: center;
    }

    th {
      background-color: var(--bleu);
      color: white;
    }

    input[type="text"], input[type="number"], input[type="date"] {
      width: 90%;
      padding: 6px;
      border-radius: 4px;
      border: 1px solid #ccc;
      text-align: center;
    }

    .error-message {
      color: red;
      font-size: 12px;
    }

    .btn-option {
      display: block;
      background-color: var(--bleu);
      color: white;
      padding: 10px;
      margin-bottom: 10px;
      text-align: center;
      border-radius: 4px;
      text-decoration: none;
      font-size: 14px;
    }

    .btn-option:hover {
      background-color: #0e0640;
    }

    .return-btn {
      position: absolute;
      top: 20px;
      left: 20px;
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

    .date-section-flex {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 20px;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }
    .select-test select {
      width: 100%;
      padding: 6px;
      border-radius: 4px;
      border: 1px solid #ccc;
      text-align: center;
      font-size: 16px;
      background-color: white;
      appearance: none; 
      box-sizing: border-box;
      background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='10' viewBox='0 0 14 10'%3E%3Cpath fill='none' stroke='%23190C63' stroke-width='2' d='M1 1l6 6 6-6'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 10px center;
      background-size: 14px 10px;
      }

    .date-field, .select-test {
      display: flex;
      align-items: center; 
      flex-direction: column;
      flex: 1;
      min-width: 250px;
    }

    .date-field input, .select-test select {
      width: 100%;
      box-sizing: border-box;
      text-align: center;
    }

    .save-button {
      display: block;
      margin: 30px auto;
      padding: 12px 24px;
      background-color: var(--bleu);
      color: white;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    .save-button:hover {
      background-color: #a10808;
    }

    @media (max-width: 768px) {
      .main-container, .sidebar {
        padding: 10px;
      }

      table {
        width: 100%;
        font-size: 14px;
      }

      .search-bar input {
        width: 90%;
      }

      .sidebar {
        width: 100%;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
      }

      .btn-option {
        width: 45%;
        margin: 5px;
      }

      body {
        flex-direction: column;
      }
    }
  </style>
  <script src="testphysique.js"></script>
</head>
  <body>
  <a href="../../accueil_staff.html" class="return-btn">Retour à l’accueil</a>
  <div class="main-container">
    <h1>Test Physique</h1>

      <form action="enregistrer_test.php" method="POST">
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
          <option value="pesee">Pesée</option>
          <option value="squat">Squat Nuque</option>
          <option value="broadjump">BROADJUMP</option>
          <option value="dc">DC</option>
          <option value="tp">TP</option>
          <option value="10m">10m</option>
          <option value="20m">20m</option>
          <option value="bronco">BRONCO</option>
          <option value="yoyo">YOYO</option>
          <option value="rfu">RFU Test Avant</option>
          <option value="cmg">CMG</option>
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
            <th>Note</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <input type="text" name="nom" class="name">
            </td>
            <td>
              <input type="text" name="prenom" class="name">
            </td>
            <td>
              <input type="number" name="note" class="note" min="0" step="0.01">
              <div class="error-message"></div>
            </td>
          </tr>

          <!-- <?php
        require_once '/../../../bd.php';

        try {
          $id_equipe = $_GET['id_eq']; // Récupère l'id de l'équipe passé en GET

          $stmt = $pdo->prepare("SELECT nom, prenom, id_joueur FROM joueurs WHERE id_equipe = :id_equipe");
          $stmt->execute(['id_equipe' => $id_equipe]);
          $joueurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

          foreach ($joueurs as $joueur) {
            ?>
            <tr>
              <td style="display: none;">
                <input type="hidden" name="id_joueur[]" value="<?= htmlspecialchars($joueur['id_joueur']) ?>">
              </td>
              <td>
                <input type="text" name="nom[]" class="name" value="<?= htmlspecialchars($joueur['nom']) ?>">
              </td>
              <td>
                <input type="text" name="prenom[]" class="name" value="<?= htmlspecialchars($joueur['prenom']) ?>">
              </td>
              <td>
                <input type="number" name="note[]" class="note" min="0" step="0.01">
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
      <button class="save-button">Enregistrer</button>
    </div>

  </form>

</body>
</html>
