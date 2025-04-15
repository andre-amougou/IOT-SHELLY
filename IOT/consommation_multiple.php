<?php
include 'session_config.php';

if (!isset($_SESSION['utilisateur'])) {
    header("Location: connexion.php");
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=iot;charset=utf8mb4", "phpmyadmin", "bella");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmtUser = $pdo->prepare("SELECT id FROM users WHERE nom_utilisateur = ?");
$stmtUser->execute([$_SESSION['utilisateur']]);
$user_id = $stmtUser->fetchColumn();

$stmtEquip = $pdo->prepare("SELECT id, nom_appareil FROM equipement WHERE user_id = ?");
$stmtEquip->execute([$user_id]);
$equipements = $stmtEquip->fetchAll(PDO::FETCH_ASSOC);

$resultats = [];
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['equipements'], $_GET['date_debut'], $_GET['date_fin'])) {
    $date_debut = $_GET['date_debut'];
    $date_fin = $_GET['date_fin'];
    $ids = $_GET['equipements'];

    if (!empty($ids) && !empty($date_debut) && !empty($date_fin)) {
        foreach ($ids as $id_eq) {
            $stmt = $pdo->prepare("
                SELECT MIN(energie_totale) as debut, MAX(energie_totale) as fin 
                FROM aenergy_log 
                WHERE equipement_id = ? AND date_heure BETWEEN ? AND ?
            ");
            $stmt->execute([$id_eq, $date_debut, $date_fin]);
            $res = $stmt->fetch();

            $nom = '';
            foreach ($equipements as $e) {
                if ($e['id'] == $id_eq) {
                    $nom = $e['nom_appareil'];
                    break;
                }
            }

            if ($res && $res['debut'] !== null && $res['fin'] !== null) {
                $conso = round($res['fin'] - $res['debut'], 3);
                $resultats[] = ['nom' => $nom, 'conso' => $conso];
            } else {
                $resultats[] = ['nom' => $nom, 'conso' => null];
            }
        }
    } else {
        $message = "<div class='alert alert-warning'>❗ Veuillez sélectionner au moins un équipement et une période.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Conso groupée</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">

<?php include 'menu.php'; ?>

<div class="container py-5">
  <h2 class="text-center text-primary mb-4">📊 Consommation de plusieurs équipements</h2>

  <form class="card p-4 shadow-sm bg-white mb-4" method="GET" action="">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">📟 Sélectionnez les équipements</label>
        <select name="equipements[]" class="form-select" multiple required size="6">
          <?php foreach ($equipements as $eq): ?>
            <option value="<?= $eq['id'] ?>" <?= (isset($_GET['equipements']) && in_array($eq['id'], $_GET['equipements'])) ? 'selected' : '' ?>>
              <?= htmlspecialchars($eq['nom_appareil']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <div class="form-text">Ctrl (ou Cmd) + clic pour en choisir plusieurs</div>
      </div>
      <div class="col-md-3">
        <label class="form-label">🗓️ Date de début</label>
        <input type="date" name="date_debut" class="form-control" value="<?= $_GET['date_debut'] ?? '' ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">🗓️ Date de fin</label>
        <input type="date" name="date_fin" class="form-control" value="<?= $_GET['date_fin'] ?? '' ?>" required>
      </div>
    </div>

    <div class="text-center mt-4">
      <button type="submit" class="btn btn-success px-4">🔍 Calculer</button>
    </div>
  </form>

  <?php if (!empty($resultats)): ?>
    <div class="card shadow-sm p-4 bg-white mb-4">
      <h5 class="text-primary mb-3">🔋 Consommation entre le <?= htmlspecialchars($date_debut) ?> et le <?= htmlspecialchars($date_fin) ?> :</h5>
      <ul class="list-group mb-4">
        <?php foreach ($resultats as $res): ?>
          <li class="list-group-item d-flex justify-content-between">
            <strong><?= htmlspecialchars($res['nom']) ?></strong>
            <?php if ($res['conso'] !== null): ?>
              <span><?= $res['conso'] ?> Wh</span>
            <?php else: ?>
              <span class="text-muted">Aucune donnée</span>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>

      <canvas id="consoChart" height="120"></canvas>

      <hr class="my-4">
      <h6 class="text-center text-muted">Répartition en camembert 🧁</h6>
      <canvas id="camembertChart" height="150"></canvas>

      <hr>
      <h6 class="text-end">🧮 Total consommation :
        <span class="badge bg-success fs-6">
          <?= number_format(array_sum(array_map(fn($e) => $e['conso'] ?? 0, $resultats)), 3) ?> Wh
        </span>
      </h6>

      <div class="text-center mt-3">
        <button class="btn btn-outline-primary me-2" onclick="downloadChart('consoChart', 'conso_barres')">📥 Télécharger le graphique à barres</button>
        <button class="btn btn-outline-success" onclick="downloadChart('camembertChart', 'conso_camembert')">📥 Télécharger le camembert</button>
      </div>
    </div>
  <?php else: ?>
    <?= $message ?>
  <?php endif; ?>

  <div class="text-center mt-4">
    <a href="accueil.php" class="btn btn-outline-primary">← Retour à l'accueil</a>
  </div>
</div>

<?php if (!empty($resultats)): ?>
<script>
  const noms = <?= json_encode(array_column($resultats, 'nom')) ?>;
  const consos = <?= json_encode(array_map(fn($e) => $e['conso'] ?? 0, $resultats)) ?>;

  new Chart(document.getElementById('consoChart').getContext('2d'), {
    type: 'bar',
    data: {
      labels: noms,
      datasets: [{
        label: 'Consommation (Wh)',
        data: consos,
        backgroundColor: 'rgba(13, 110, 253, 0.6)',
        borderColor: 'rgba(13, 110, 253, 1)',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      plugins: {
        title: {
          display: true,
          text: '📊 Consommation par équipement',
          font: { size: 18 }
        },
        legend: { display: false }
      },
      scales: {
        y: {
          beginAtZero: true,
          title: { display: true, text: 'Wh (Watt-heures)' }
        }
      }
    }
  });

  new Chart(document.getElementById('camembertChart').getContext('2d'), {
    type: 'pie',
    data: {
      labels: noms,
      datasets: [{
        label: 'Répartition (Wh)',
        data: consos,
        backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6610f2', '#20c997', '#fd7e14']
      }]
    },
    options: {
      responsive: true,
      plugins: {
        title: {
          display: true,
          text: 'Répartition de la consommation (%)',
          font: { size: 16 }
        },
        legend: { position: 'bottom' }
      }
    }
  });

  function downloadChart(id, filename) {
    const link = document.createElement('a');
    link.download = filename + '.png';
    link.href = document.getElementById(id).toDataURL('image/png');
    link.click();
  }
</script>
<?php endif; ?>

</body>
</html>