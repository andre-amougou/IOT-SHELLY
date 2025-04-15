<?php
include 'session_config.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: accueil.php");
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=iot;charset=utf8mb4", "phpmyadmin", "bella");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// On récupère les utilisateurs avec le nombre d'équipements
$stmt = $pdo->query("
    SELECT u.id, u.nom_utilisateur, u.role, COUNT(e.id) AS nb_equipements
    FROM users u
    LEFT JOIN equipement e ON u.id = e.user_id
    GROUP BY u.id
    ORDER BY u.id ASC
");
$utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Liste des utilisateurs</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include 'menu.php'; ?>

<div class="container py-5">
  <h2 class="text-center text-primary mb-4">👥 Liste des utilisateurs</h2>

  <div class="card shadow-sm p-4">
    <div class="table-responsive">
      <table id="tableUsers" class="table table-striped table-bordered align-middle">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Nom d'utilisateur</th>
            <th>Rôle</th>
            <th>Nb équipements</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($utilisateurs as $user): ?>
            <tr>
              <td><?= $user['id'] ?></td>
              <td><?= htmlspecialchars($user['nom_utilisateur']) ?></td>
              <td><?= htmlspecialchars($user['role']) ?></td>
              <td><?= $user['nb_equipements'] ?></td>
              <td>
                <a href="equipements_utilisateur.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-outline-primary">
                  👀 Voir équipements
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="text-center mt-4">
    <a href="accueil.php" class="btn btn-outline-secondary">← Retour</a>
  </div>
</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
  $(document).ready(function () {
    $('#tableUsers').DataTable({
      language: {
        url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json"
      }
    });
  });
</script>

</body>
</html>
