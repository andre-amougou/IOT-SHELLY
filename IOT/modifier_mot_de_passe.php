<?php
include 'session_config.php';

if (!isset($_SESSION['utilisateur'])) {
    header("Location: connexion.php");
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=iot;charset=utf8mb4", "phpmyadmin", "bella");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupérer utilisateur connecté
$stmtUser = $pdo->prepare("SELECT * FROM users WHERE nom_utilisateur = ?");
$stmtUser->execute([$_SESSION['utilisateur']]);
$user = $stmtUser->fetch();

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ancien = $_POST['ancien'] ?? '';
    $nouveau = $_POST['nouveau'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if (!password_verify($ancien, $user['mot_de_passe'])) {
        $message = "<div class='alert alert-danger'>❌ Ancien mot de passe incorrect.</div>";
    } elseif ($nouveau !== $confirm) {
        $message = "<div class='alert alert-danger'>❌ Les mots de passe ne correspondent pas.</div>";
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $nouveau)) {
        $message = "<div class='alert alert-danger'>❌ Le mot de passe ne respecte pas les règles de sécurité.</div>";
    } else {
        $hash = password_hash($nouveau, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET mot_de_passe = ? WHERE id = ?");
        $stmt->execute([$hash, $user['id']]);
        $message = "<div class='alert alert-success'>✅ Mot de passe mis à jour avec succès.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Modifier mon mot de passe</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include 'menu.php'; ?>

<div class="container py-5">
  <h2 class="text-center text-primary mb-4">🔒 Modifier mon mot de passe</h2>

  <div class="row justify-content-center">
    <div class="col-md-6">
      <?= $message ?>
      <div class="card shadow-sm p-4">
        <form method="POST">
          <div class="mb-3">
            <label class="form-label">Ancien mot de passe</label>
            <input type="password" name="ancien" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Nouveau mot de passe</label>
            <input type="password" name="nouveau" class="form-control" required>
            <div class="form-text">
              Au moins 8 caractères, une majuscule, un chiffre et un caractère spécial.
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Confirmer le mot de passe</label>
            <input type="password" name="confirm" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-success w-100">🔁 Mettre à jour</button>
        </form>
      </div>
    </div>
  </div>

  <div class="text-center mt-4">
    <a href="accueil.php" class="btn btn-outline-secondary">← Retour</a>
  </div>
</div>

</body>
</html>
