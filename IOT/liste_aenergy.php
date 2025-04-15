<?php
include 'session_config.php';

if (!isset($_SESSION['utilisateur'])) {
    header("Location: connexion.php");
    exit;
}

// Connexion BDD
$pdo = new PDO("mysql:host=localhost;dbname=iot;charset=utf8mb4", "phpmyadmin", "bella");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupérer l'ID de l'utilisateur connecté
$stmtUser = $pdo->prepare("SELECT id FROM users WHERE nom_utilisateur = ?");
$stmtUser->execute([$_SESSION['utilisateur']]);
$user = $stmtUser->fetch();
$user_id = $user['id'];

// Récupérer toutes les mesures de ses équipements
$sql = "
    SELECT 
        aenergy_log.id,
        aenergy_log.energie_totale,
        aenergy_log.date_heure,
        equipement.nom_appareil,
        equipement.ip
    FROM aenergy_log
    JOIN equipement ON aenergy_log.equipement_id = equipement.id
    WHERE equipement.user_id = ?
    ORDER BY aenergy_log.date_heure DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$mesures = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Historique des mesures</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include 'menu.php'; ?>

<div class="container py-5">
  <h2 class="text-center text-primary mb-4">📊 Historique des consommations (Wh)</h2>

  <div class="card shadow-sm p-4">
    <div class="table-responsive">
      <table id="tableLog" class="table table-striped table-bordered align-middle">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Nom de l'équipement</th>
            <th>IP</th>
            <th>Énergie totale (Wh)</th>
            <th>Date & heure</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($mesures as $log): ?>
            <tr>
              <td><?= htmlspecialchars($log['id']) ?></td>
              <td><?= htmlspecialchars($log['nom_appareil']) ?></td>
              <td><?= htmlspecialchars($log['ip']) ?></td>
              <td><?= number_format($log['energie_totale'], 3) ?></td>
              <td><?= date('d/m/Y H:i:s', strtotime($log['date_heure'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="text-center mt-4">
    <a href="accueil.php" class="btn btn-outline-primary">← Retour à l'accueil</a>
  </div>
</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
$(document).ready(function () {
  $('#tableLog').DataTable({
    dom: 'Bfrtip',
    buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json"
    },
    pageLength: 10
  });
});
</script>

</body>
</html>