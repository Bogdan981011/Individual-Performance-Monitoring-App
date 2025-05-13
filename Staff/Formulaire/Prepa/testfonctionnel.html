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
</head>
<body>

<a href="../../accueil_staff.html" class="return-btn">Retour à l’accueil</a>

<h1>Tableau de Tests Fonctionnels</h1>

<form method="post" action="enregistrer_fonctionnel.php">

  <div class="date-section">
    <label for="date1">Date :</label>
    <input type="date" id="date1" name="date1" required>
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
        <td><input type="text" name="nom1" class="name" required><div class="error-message"></div></td>
        <td><input type="text" name="prenom1" class="name" required><div class="error-message"></div></td>
        <td><input type="text" name="squat1" class="note"><div class="error-message"></div></td>
        <td><input type="text" name="iso1" class="note"><div class="error-message"></div></td>
        <td><input type="text" name="souplesse1" class="note"><div class="error-message"></div></td>
        <td><input type="text" name="flamant1" class="note"><div class="error-message"></div></td>
        <td><input type="text" name="haut1" class="note"><div class="error-message"></div></td>
      </tr>
    </tbody>
  </table>

  <div style="text-align: center;">
    <button type="submit">Enregistrer</button>
  </div>
</form>

<script>
  function updateColor(input) {
    input.classList.remove("A", "EA", "NA");
    const value = input.value.trim().toUpperCase();
    if (value === "A") input.classList.add("A");
    else if (value === "EA") input.classList.add("EA");
    else if (value === "NA") input.classList.add("NA");
  }

  function showError(input, message) {
    const errorDiv = input.parentElement.querySelector(".error-message");
    errorDiv.textContent = message;
  }

  function clearError(input) {
    const errorDiv = input.parentElement.querySelector(".error-message");
    errorDiv.textContent = "";
  }

  function validateNote(input) {
    const value = input.value.trim().toUpperCase();
    updateColor(input);
    if (value && !["A", "EA", "NA"].includes(value)) {
      showError(input, "A, EA ou NA uniquement");
    } else {
      clearError(input);
    }
  }

  function validateName(input) {
    const value = input.value.trim();
    if (/\d/.test(value)) {
      showError(input, "Pas de chiffres dans ce champ");
    } else {
      clearError(input);
    }
  }

  document.querySelectorAll("input.note").forEach(input => {
    input.addEventListener("input", () => validateNote(input));
  });

  document.querySelectorAll("input.name").forEach(input => {
    input.addEventListener("input", () => validateName(input));
  });
</script>

</body>
</html>
