<?php
include 'session_config.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: accueil.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: liste_utilisateurs.php");
    exit;
}

$utilisateur_id = $_GET['id'];

$pdo = new PDO("mysql:host=localhost;dbname=iot;charset=utf8mb4", "phpmyadmin", "bella");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Infos utilisateur
$stmtUser = $pdo->prepare("SELECT nom_utilisateur FROM users WHERE id = ?");
$stmtUser->execute([$utilisateur_id]);
$nom_utilisateur = $stmtUser->fetchColumn();

// Equipements
$stmtEquip = $pdo->prepare("SELECT * FROM equipement WHERE user_id = ?");
$stmtEquip->execute([$utilisateur_id]);
$equipements = $stmtEquip->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Équipements de l'utilisateur</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include 'menu.php'; ?>

<div class="container py-5">
  <h2 class="text-center text-primary mb-4">🔌 Équipements de <strong><?= htmlspecialchars($nom_utilisateur) ?></strong></h2>

  <?php if (count($equipements) === 0): ?>
    <div class="alert alert-info text-center">Aucun équipement associé à cet utilisateur.</div>
  <?php else: ?>
    <div class="card shadow-sm p-4">
      <table class="table table-bordered table-striped">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Nom de l'appareil</th>
            <th>Adresse IP</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($equipements as $eq): ?>
            <tr>
              <td><?= $eq['id'] ?></td>
              <td><?= htmlspecialchars($eq['nom_appareil']) ?></td>
              <td><?= htmlspecialchars($eq['ip']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <div class="text-center mt-4">
    <a href="liste_utilisateurs.php" class="btn btn-outline-secondary">← Retour à la liste</a>
  </div>
</div>

</body>
</html>