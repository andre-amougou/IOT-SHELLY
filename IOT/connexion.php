<?php
session_start();

$host = "localhost";
$dbname = "iot";
$username = "phpmyadmin";
$password = "bella";

$erreur = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $nom = $_POST['nom_utilisateur'];
        $mdp = $_POST['mot_de_passe'];

        $stmt = $pdo->prepare("SELECT * FROM users WHERE nom_utilisateur = ?");
        $stmt->execute([$nom]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($mdp, $user['mot_de_passe'])) {
            $_SESSION['utilisateur'] = $user['nom_utilisateur'];
            $_SESSION['role'] = $user['role'];
            header("Location: accueil.php");
            exit;
        } else {
            $erreur = "❌ Nom d'utilisateur ou mot de passe incorrect.";
        }
    } catch (PDOException $e) {
        $erreur = "Erreur : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Connexion</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-7">

        <!-- Présentation du projet -->
        <div class="text-center mb-4">
          <h1 class="fw-bold text-primary">🔌 Projet IOT Shelly – 100% Libre</h1>
          <p class="text-muted">
            Cette application vous permet de contrôler vos appareils Shelly sans dépendre de solutions payantes.
            Elle fonctionne entièrement en local, pour un maximum d'autonomie et de confidentialité.
          </p>
        </div>

        <!-- Présentation des auteurs -->
        <div class="card border-0 mb-4 bg-white shadow-sm">
          <div class="card-body text-center">
            <h5 class="text-secondary fw-bold mb-3">✨ Ce projet a été réalisé par :</h5>
            <div class="d-flex justify-content-center gap-4">
              <div>
                <div class="fs-2">👨‍💻</div>
                <strong>André</strong>
              </div>
              <div>
                <div class="fs-2">👩‍💻</div>
                <strong>Séréna</strong>
              </div>
              <div>
                <div class="fs-2">👨‍🔧</div>
                <strong>Peguy</strong>
              </div>
            </div>
            <p class="mt-3 text-muted fst-italic small">
              Leur expertise, leur créativité et leur collaboration ont permis à cette solution de voir le jour.
            </p>
          </div>
        </div>

        <!-- Formulaire de connexion -->
        <div class="card shadow rounded">
          <div class="card-header bg-dark text-white text-center">
            <h3>Connexion utilisateur</h3>
          </div>
          <div class="card-body">
            <?php if ($erreur): ?>
              <div class="alert alert-danger text-center"><?= $erreur ?></div>
            <?php endif; ?>

            <form method="POST" action="">
              <div class="mb-3">
                <label class="form-label">Nom d'utilisateur</label>
                <input type="text" name="nom_utilisateur" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Mot de passe</label>
                <input type="password" name="mot_de_passe" class="form-control" required>
              </div>
              <button type="submit" class="btn btn-primary w-100">Se connecter</button>
            </form>

            <div class="mt-3 text-center">
              <a href="creer_utilisateur.php">Créer un compte</a>
            </div>
          </div>
        </div>

        <footer class="text-center mt-4 text-muted small">
          Projet éducatif open-source – Libre et fièrement autonome 💡
        </footer>
      </div>
    </div>
  </div>
</body>
</html>
