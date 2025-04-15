<?php
include 'session_config.php';

if (!isset($_SESSION['utilisateur'])) {
    header("Location: connexion.php");
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=iot;charset=utf8mb4", "phpmyadmin", "bella");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupérer les équipements de l’utilisateur
$stmtUser = $pdo->prepare("SELECT id FROM users WHERE nom_utilisateur = ?");
$stmtUser->execute([$_SESSION['utilisateur']]);
$user_id = $stmtUser->fetchColumn();

$stmtEquip = $pdo->prepare("SELECT id, nom_appareil FROM equipement WHERE user_id = ?");
$stmtEquip->execute([$user_id]);
$equipements = $stmtEquip->fetchAll(PDO::FETCH_ASSOC);

$consommation = null;
$message = "";

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['equipement'], $_GET['date_debut'], $_GET['date_fin'])) {
    $id_eq = $_GET['equipement'];
    $date_debut = $_GET['date_debut'];
    $date_fin = $_GET['date_fin'];

    if (!empty($id_eq) && !empty($date_debut) && !empty($date_fin)) {
        $stmt = $pdo->prepare("
            SELECT 
                MIN(energie_totale) as debut, 
                MAX(energie_totale) as fin 
            FROM aenergy_log 
            WHERE equipement_id = ? AND date_heure BETWEEN ? AND ?
        ");
        $stmt->execute([$id_eq, $date_debut, $date_fin]);
        $result = $stmt->fetch();

        if ($result && $result['debut'] !== null && $result['fin'] !== null) {
            $consommation = round($result['fin'] - $result['debut'], 3);
        } else {
            $message = "<div class='alert alert-warning'>❗Aucune donnée disponible pour cette période.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Calcul de la consommation</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include 'menu.php'; ?>

<div class="container py-5">
  <h2 class="text-center text-primary mb-4">🔋 Calcul de la consommation par période</h2>

  <form class="card p-4 shadow-sm bg-white mb-4" method="GET" action="">
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">📟 Équipement</label>
        <select name="equipement" class="form-select" required>
          <option value="">-- Choisir un équipement --</option>
          <?php foreach ($equipements as $eq): ?>
            <option value="<?= $eq['id'] ?>" <?= isset($_GET['equipement']) && $_GET['equipement'] == $eq['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($eq['nom_appareil']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">🗓️ Date de début</label>
        <input type="date" name="date_debut" class="form-control" value="<?= $_GET['date_debut'] ?? '' ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">🗓️ Date de fin</label>
        <input type="date" name="date_fin" class="form-control" value="<?= $_GET['date_fin'] ?? '' ?>" required>
      </div>
    </div>
    <div class="text-center mt-4">
      <button type="submit" class="btn btn-success px-4">🔍 Calculer</button>
    </div>
  </form>

  <?php if ($consommation !== null): ?>
    <div class="alert alert-info text-center fs-5">
      ✅ <strong>Consommation :</strong> <?= $consommation ?> Wh
    </div>
  <?php else: ?>
    <?= $message ?>
  <?php endif; ?>

  <div class="text-center mt-4">
    <a href="accueil.php" class="btn btn-outline-primary">← Retour à l'accueil</a>
  </div>
</div>

</body>
</html>
