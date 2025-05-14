<?php
session_start();
$_SESSION['user_id'] = 1;

$userData = [
    'nom' => 'Jean Dupont',
    'poste' => 'Défenseur',
    'date_naissance' => '1998-07-15',
    'age' => 26,
    'poids' => 78,
    'taille' => 1.82,
    'img' => 14.5,
    'gmc' => 'Bonne'
];

$medicalData = [
    'date_examen' => '2025-04-12',
    'blessure' => 'Entorse cheville',
    'duree' => 3,
    'recommandation' => 'Repos complet + kiné'
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Fiche Joueur ASBH</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #fdfdfd;
      color: #1a1a1a;
    }

    .header {
        background: none;
        padding: 30px 0;
        display: flex;
        justify-content: center;
    }

    .header-box {
        background: linear-gradient(to right, #002766, #d72638);
        color: white;
        border-bottom-left-radius: 30px;
        border-bottom-right-radius: 30px;
        max-width: 1480px;
        width: 100%;
        padding: 30px 20px;
        box-sizing: border-box;
    }

    .container {
      display: flex;
      align-items: center;
    }

    .container img {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      border: 3px solid white;
      margin-right: 20px;
    }

    .player-name {
      font-size: 28px;
      font-weight: bold;
      margin: 0;
    }

    .container div {
      text-align: right;
      flex: 1;
    }

    .main-wrapper {
      max-width: 1480px;
      margin: 0 auto;
      padding: 20px;
      box-sizing: border-box;
    }

    .fixed-info {
      display: flex;
      justify-content: space-between;
      padding: 15px;
      margin: 10px 0;
      border-radius: 15px;
      background-color: #fff;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .fixed-info div {
      text-align: center;
      font-size: 16px;
    }

    .section {
      display: none;
      padding: 20px 0;
    }

    .section.active {
      display: block;
    }

    .card {
      background-color: #fff;
      border-radius: 20px;
      margin: 10px 0;
      padding: 15px;
      box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .card h3 {
      font-size: 18px;
      color: #002766;
      margin-bottom: 8px;
    }

    .nav {
      position: fixed;
      bottom: 0;
      width: 100%;
      background: #ffffff;
      border-top: 1px solid #ccc;
      display: flex;
      justify-content: space-around;
      padding: 10px 0;
    }

    .nav button {
      background: none;
      border: none;
      font-size: 14px;
      color: #555;
      text-align: center;
    }

    .nav button.active {
      color: #d72638;
      font-weight: bold;
    }

    .nav button span {
      display: block;
      font-size: 18px;
    }

    .physique-row {
      display: flex;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 20px;
    }

    .btn-retour {
      position: fixed;
      top: 20px;
      right: 20px;
      background-color: #d72638;
      color: white;
      border: none;
      border-radius: 25px;
      padding: 10px 20px;
      font-size: 14px;
      cursor: pointer;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
      z-index: 1000;
      transition: background-color 0.3s;
      text-decoration: none;
    }

    .btn-retour:hover {
      background-color: #002766;
    }
  </style>
</head>
<body>

  <!-- Header ASBH -->
  <div class="header">
    <div class="header-box">
      <div class="container">
        <img src="player.jpg" alt="Photo du joueur" />
        <div>
          <p class="player-name"><?php echo $userData['nom']; ?></p>
          <p><strong>ASBH</strong> - Équipe</p>
          <p><strong><?php echo $userData['poste']; ?></strong> - Poste</p>
          <p><strong><?php echo $userData['date_naissance']; ?></strong> - Naissance</p>
        </div>
      </div>
    </div>
  </div>

 <?php
  $retourLink = ($_SESSION['user_id'] == 1) ? '../accueil_joueur.html' : '../../Staff/acceuil-staff.html';
?>
<a href="<?php echo $retourLink; ?>" class="btn-retour">Accueil</a>

  <!-- Sections -->
  <div class="main-wrapper">
    <div id="infos" class="section active">
      <div class="card">
        <h3>Profil Physique</h3>
        <div class="physique-row">
          <p><strong>Âge :</strong> <?php echo $userData['age']; ?> ans</p>
          <p><strong>Poids :</strong> <?php echo $userData['poids']; ?> kg</p>
          <p><strong>Taille :</strong> <?php echo $userData['taille']; ?> m</p>
          <p><strong>IMG :</strong> <?php echo $userData['img']; ?>%</p>
          <p><strong>GMC :</strong> <?php echo $userData['gmc']; ?></p>
        </div>
      </div>
    </div>

    <div id="medical" class="section">
      <div class="card">
        <h3>Suivi Médical</h3>
        <p><strong>Dernier examen :</strong> <?php echo $medicalData['date_examen']; ?></p>
        <p><strong>Blessure :</strong> <?php echo $medicalData['blessure']; ?></p>
        <p><strong>Durée :</strong> <?php echo $medicalData['duree']; ?> semaines</p>
        <p><strong>Recommandation :</strong> <?php echo $medicalData['recommandation']; ?></p>
      </div>
    </div>
    <div id="tests" class="section">
        <div class="card">
            <h3>Tests Physiques</h3>
            <p><strong>Test de vitesse :</strong> 30m en 4.2s</p>
            <p><strong>Test d’endurance :</strong> VMA 17 km/h</p>
            <p><strong>Souplesse :</strong> Toucher 12cm au-delà des pieds</p>
            <p><strong>Force :</strong> Squat max 120kg</p>
        </div>
    </div>
    <div id="graphes" class="section">
        <div class="card">
            <h3>Visualisation des Données</h3>
            <p>Graphiques de performance en cours de développement.</p>
            <div style="text-align:center; margin-top:20px;">
            <img src="graph-placeholder.png" alt="Graphique exemple" style="max-width:100%; border-radius:10px;" />
            </div>
        </div>
    </div>
  </div>

  <!-- Navigation -->
  <div class="nav">
    <button onclick="showSection('infos')" class="active"><span>📄</span>Infos</button>
    <button onclick="showSection('medical')"><span>🩺</span>Médical</button>
    <button onclick="showSection('tests')"><span>🏋️</span>Tests</button>
    <button onclick="showSection('graphes')"><span>📊</span>Visualiser</button>
  </div>

  <script>
    function showSection(id) {
      document.querySelectorAll('.section').forEach(el => el.classList.remove('active'));
      document.querySelectorAll('.nav button').forEach(btn => btn.classList.remove('active'));
      document.getElementById(id).classList.add('active');
      document.querySelector(`.nav button[onclick="showSection('${id}')"]`).classList.add('active');
    }
  </script>

</body>
</html>
