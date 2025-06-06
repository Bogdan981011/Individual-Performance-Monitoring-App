<?php 
session_start(); 
if (!isset($_SESSION['user_id'])) {
  // L'utilisateur n'est pas connecté, on le redirige
  header("Location: /vizia/accueil.html");
  exit;
}

include_once 'recup.php'; // ou require_once 'recup.php';
?>
<?php include('../../chatbot/chatbot.php'); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Fiche Joueur ASBH</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="perf.css">
</head>
<body>

    <!-- Header ASBH -->
    <div class="header">
        <div class="header-box">
        <div class="container">
            <img src="player.jpg" alt="Photo du joueur" />
            <div>
            <p class="player-name"><?= $joueurs['prenom'] . ' ' . $joueurs['nom']; ?></p>
            <p><strong><?= $joueurs['poste'] ?></strong> - Poste</p>
            <p><strong><?= $joueurs['annee'] ?></strong> - Naissance</p>

            </div>
        </div>
        </div>
    </div>
  
  <a id="btn-retour" class="btn-retour">Accueil</a>



  <!-- Section Infos Générales -->
  <div id="infos" class="section active">
    <div class="card">
      <h3>Profil Physique</h3>
      <div class="physique-row">
        <p><strong>Âge :</strong> <?= date('Y') - $joueurs['annee']; ?> ans</p>
        <p><strong>Poids :</strong> <?= $dernieres_mesures['poids'] ?? 'N.C.'; ?> kg</p>
        <p><strong>Taille :</strong> <?= $dernieres_mesures['taille'] ?? 'N.C.'; ?> m</p>
        <p><strong>IMG :</strong> <?= $dernieres_mesures['img'] ?? 'N.C.'; ?> %</p>
        <p><strong>GMC :</strong> <?= $dernieres_mesures['gmc'] ?? 'N.C.'; ?></p>


      </div>
    </div>
    <div class="card">
      <h3>Temps de jeu</h3>
      <p><strong>Dernier match :</strong> 45 min</p>
      <p><strong>Moyenne :</strong> 53 min</p>
    </div>
      <!-- Section Graphes -->
    <div class="card">
        <h3>Graphiques</h3>
        <div class="graph-grid">
          <div class="graph-box">
            <h3>Poids</h3>
            <canvas id="graph-poids"></canvas>
          </div>
          <div class="graph-box">
            <h3>Taille</h3>
            <canvas id="graph-taille"></canvas>
          </div>
          <div class="graph-box">
            <h3>IMG</h3>
            <canvas id="graph-img"></canvas>
          </div>
        </div>

        <script>
            const dernieres_mesures = <?php echo json_encode($historique_mesures); ?>;
        </script>
        <script src="perf.js"></script>

    </div>
  </div>
  

  <!-- Section Fiche Médicale -->
  <div id="medical" class="section">
    <div class="card">
      <h3>Suivi Médical</h3>
      <p><strong>Dernier examen :</strong> <?= $medicalData['date_blessure'] ?? 'Non renseigné'; ?></p>
      <p><strong>Blessure :</strong> <?= $medicalData['type_blessure'] ?? 'Aucune'; ?></p>
      <p><strong>Durée :</strong> <?= $medicalData['gravite'] ?? 'N.C.'; ?></p>
      <p><strong>Recommandation :</strong> <?= $medicalData['recommandation'] ?? 'N.C.'; ?></p>
      <p><strong>Reprise :</strong> <?= $medicalData['reprise'] ?? 'N.C.'; ?></p>

    </div>
  </div>

 <!-- Section Tests -->
<div id="tests" class="section">
  <div class="card">
    <h3>Tests Fonctionnels</h3>

    <?php if (!empty($tests)) : ?>
      <table border="1" cellpadding="8" cellspacing="0">
        <thead>
          <tr>
            <th>Date du test</th>
            <th>Squat à l'arraché</th>
            <th>ISO Leg Curl</th>
            <th>Souplesse chaîne postérieure</th>
            <th>Flamant rose</th>
            <th>Souplesse membres supérieurs</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($tests as $test) : ?>
            <tr>
              <td><?= htmlspecialchars($test['date_test']) ?></td>
              <?php
              $champs = ['squat_arrache', 'iso_leg_curl', 'souplesse_chaine_post', 'flamant_rose', 'souplesse_membres_supérieurs'];
              foreach ($champs as $key) :
                $valeur = $test[$key];
                $classe = match ($valeur) {
                  'A' => 'test-A',
                  'NA' => 'test-NA',
                  'EA' => 'test-EA',
                  '', null => 'test-empty',
                  default => '',
                };
              ?>
                <td class="<?= $classe ?>"><?= htmlspecialchars($valeur) ?></td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <!-- Légende -->
      <div class="legend">
        <strong>Légende :</strong>
        <span class="test-A">A (Acquis)</span>
        <span class="test-NA">NA (Non Acquis)</span>
        <span class="test-EA">EA (En cours d’Acquisition)</span>
        <span class="test-empty">Pas de donnée</span>
      </div>

    <?php else : ?>
      <p>Aucun test fonctionnel disponible.</p>
    <?php endif; ?>
  </div>

  <div class="card_phys">
    <h3>Tests Physiques</h3>

    <div class="flex-test-layout">

      <!-- TABLE BLOCK -->
      <div class="table-physique">
      <?php
      // Prepare pivot data (same as before)
      $pivoted = [];
      $test_types = [];

      foreach ($mesures as $entry) {
          $date = date('Y-m-d', strtotime($entry['date_test']));
          $type = $entry['type_test'];
          $value = $entry['mesure_test'];

          $pivoted[$date][$type] = $value;
          $test_types[$type] = true;
      }

      krsort($pivoted);
      $pivoted = array_slice($pivoted, 0, 6, true);
      $test_types = array_keys($test_types);
      sort($test_types);

      echo '<table border="1" cellpadding="8" cellspacing="0">';
      echo '<thead><tr><th>Date</th>';
      foreach ($test_types as $type) {
          echo '<th>' . htmlspecialchars($type) . '</th>';
      }
      echo '</tr></thead><tbody>';

      foreach ($pivoted as $date => $testsOnDate) {
          echo '<tr>';
          echo '<td>' . htmlspecialchars($date) . '</td>';
          foreach ($test_types as $type) {
              $val = isset($testsOnDate[$type]) ? $testsOnDate[$type] : '-';
              echo '<td>' . htmlspecialchars($val) . '</td>';
          }
          echo '</tr>';
      }
      echo '</tbody></table>';
      ?>
    </div>

    <!-- GRAPH BLOCK -->
    <div class="graph-physique">
      <div id="graph-tests-container" class="graph-grid-phys">
      </div>
      <script>
        window.data_graph = <?= json_encode($mesures, JSON_UNESCAPED_UNICODE); ?>;
        console.log("[PHP → JS] data_graph:", data_graph);
      </script>
      <script src="graph_int_phys.js"></script>
    </div>

  </div> <!-- end .flex-test-layout -->
</div> <!-- end .card -->

</div>



  <!-- Navigation -->
  <div class="nav">
    <button onclick="showSection('infos')" class="active"><span>📄</span>Infos</button>
    <button onclick="showSection('medical')"><span>🩺</span>Médical</button>
    <button onclick="showSection('tests')"><span>🏋️</span>Tests</button>
    <button onclick="showSection('graphes')"><span>📊</span>Visualiser</button>
  </div>

  <!-- JS -->
  <script>
    function showSection(id) {
      document.querySelectorAll('.section').forEach(el => el.classList.remove('active'));
      document.querySelectorAll('.nav button').forEach(btn => btn.classList.remove('active'));
      document.getElementById(id).classList.add('active');
      document.querySelector(`.nav button[onclick="showSection('${id}')"]`).classList.add('active');
    }
    // Fonction pour récupérer les paramètres URL
    function getQueryParam(param) {
      const urlParams = new URLSearchParams(window.location.search);
      return urlParams.get(param);
    }
     const userRole = <?= isset($_SESSION['role']) ? json_encode($_SESSION['role']) : 'null'; ?>;
  const equipe = new URLSearchParams(window.location.search).get("eq");
let retourURL = "";

if (userRole) {
  switch (equipe) {
    case "A":
      retourURL = "/vizia/Staff/Equipe/CadetA/joueurs_cadetA.php";
      break;
    case "B":
      retourURL = "/vizia/Staff/Equipe/CadetB/joueurs_cadetB.php";
      break;
    case "C":
      retourURL = "/vizia/Staff/Equipe/Crabos/joueurs_crabos.php";
      break;
    case "E":
      retourURL = "/vizia/Staff/Equipe/Espoirs/joueurs_espoirs.php";
      break;
    default:
      retourURL = "/vizia/Staff/accueil_staff.html";
  }
} else {
  retourURL = '../accueil_joueur.html'; // ✅ Correction ici
}

document.getElementById('btn-retour').href = retourURL;

      
  </script>
  



</body>
</html>
