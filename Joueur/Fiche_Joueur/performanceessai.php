<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Fiche Joueur ASBH</title>
  <link rel="stylesheet" href="perf.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<!-- Affichage des joueurs -->
<?php foreach ($joueurs as $joueur): ?>
  <div class="header">
    <div class="header-box">
      <div class="container">
        <img src="player.jpg" alt="Photo du joueur" />
        <div>
          <p class="player-name"><?= htmlspecialchars($joueur['prenom']) . ' ' . htmlspecialchars($joueur['nom']) ?></p>
          <p><strong><?= htmlspecialchars($joueur['nom_equipe']) ?></strong> - Équipe</p>
          <p><strong><?= htmlspecialchars($joueur['poste']) ?></strong> - Poste</p>
          <p><strong><?= htmlspecialchars($joueur['date_naissance'] ?? 'N/A') ?></strong> - Naissance</p>
        </div>
      </div>
    </div>
  </div>
<?php endforeach; ?>

<a href="<?= $retourLink; ?>" class="btn-retour">Accueil</a>

<div class="main-wrapper">
  <div id="infos" class="section active">
    <div class="card">
      <h3>Profil Physique</h3>
      <div class="physique-row">
        <p><strong>Âge :</strong> <?= $userData['age'] ?> ans</p>
        <p><strong>Poids :</strong> <?= $userData['poids'] ?> kg</p>
        <p><strong>Taille :</strong> <?= $userData['taille'] ?> m</p>
        <p><strong>IMG :</strong> <?= $userData['img'] ?>%</p>
        <p><strong>GMC :</strong> <?= $userData['gmc'] ?></p>
      </div>
    </div>
    <div class="card">
      <h3>Visualisation des Données</h3>
      <canvas id="testChart" style="max-width: 100%;"></canvas>
    </div>
  </div>

  <div id="medical" class="section">
    <div class="card">
      <h3>Suivi Médical</h3>
      <p><strong>Dernier examen :</strong> <?= $medicalData['date_examen'] ?></p>
      <p><strong>Blessure :</strong> <?= $medicalData['blessure'] ?></p>
      <p><strong>Durée :</strong> <?= $medicalData['duree'] ?> semaines</p>
      <p><strong>Recommandation :</strong> <?= $medicalData['recommandation'] ?></p>
    </div>
  </div>

  <div id="tests" class="section">
    <div class="card">
      <h3>Historique des tests fonctionnels</h3>
      <table style="width:100%; border-collapse:collapse; margin-top:10px;">
        <thead>
          <tr style="background-color:#002766; color:white;">
            <th style="padding:8px; border:1px solid #ccc;">Date</th>
            <?php foreach ($typesDeTests as $type): ?>
              <th style="padding:8px; border:1px solid #ccc;"><?= ucfirst($type) ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($tableTests as $date => $testsDuJour): ?>
            <tr>
              <td style="padding:8px; border:1px solid #ccc;"><?= $date ?></td>
              <?php foreach ($typesDeTests as $type): ?>
                <td style="padding:8px; border:1px solid #ccc; text-align:center;">
                  <?= $testsDuJour[$type] ?? '-' ?>
                </td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
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

<script>
async function chargerDonneesEtAfficherGraphique() {
  const response = await fetch('donnees_tests.php'); // Assure-toi que ce fichier existe et renvoie du JSON
  const data = await response.json();

  const ctx = document.getElementById('testChart').getContext('2d');

  const datasets = Object.keys(data).map((type, index) => ({
    label: type,
    data: data[type].notes,
    borderColor: getColor(index),
    backgroundColor: getColor(index),
    fill: false,
    tension: 0.2
  }));

  const labels = data[Object.keys(data)[0]].dates;

  new Chart(ctx, {
    type: 'line',
    data: {
      labels: labels,
      datasets: datasets
    },
    options: {
      responsive: true,
      plugins: {
        title: {
          display: true,
          text: 'Évolution des performances par type de test'
        }
      }
    }
  });
}

function getColor(index) {
  const colors = ['#d72638', '#002766', '#f4a261', '#2a9d8f', '#e9c46a'];
  return colors[index % colors.length];
}

window.addEventListener('DOMContentLoaded', chargerDonneesEtAfficherGraphique);
</script>

</body>
</html>
