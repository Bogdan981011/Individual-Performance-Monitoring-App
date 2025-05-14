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

    .date-field, .select-test {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
    }

    .select-test select {
      padding: 6px;
      font-size: 14px;
      border-radius: 4px;
      border: 1px solid #ccc;
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
</head>
<body>
<a href="../../accueil_staff.html" class="return-btn">Retour à l’accueil</a>
<div class="main-container">
  <h1>Test Physique</h1>

    <form>
    <div class="search-bar">
      <input type="text" placeholder="Rechercher un nom ou prénom...">
    </div>

  <div class="date-section-flex">
    <div class="date-field">
      <label for="date1">Date :</label>
      <input type="date" id="date1" name="date1">
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
            <input type="text" name="nom1" class="name">
            <div class="error-message" id="error-nom1"></div>
          </td>
          <td>
            <input type="text" name="prenom1" class="name">
            <div class="error-message" id="error-prenom1"></div>
          </td>
          <td>
            <input type="number" name="note1" class="note" min="0" step="0.01">
            <div class="error-message"></div>
          </td>
        </tr>
      </tbody>
    </table>

    <!--  BOUTON ENREGISTRER -->
    <button class="save-button">Enregistrer</button>
  </div>

</form>

<script>
  function showError(input, message) {
    const errorDiv = input.parentElement.querySelector(".error-message");
    errorDiv.textContent = message;
  }

  function clearError(input) {
    const errorDiv = input.parentElement.querySelector(".error-message");
    errorDiv.textContent = "";
  }

  function validateName(input) {
    const value = input.value.trim();
    if (/\d/.test(value)) {
      showError(input, "Pas de chiffres dans ce champ");
    } else {
      clearError(input);
    }
  }

  document.querySelectorAll("input.name").forEach(input => {
    input.addEventListener("input", () => validateName(input));
  });
</script>

</body>
</html>
