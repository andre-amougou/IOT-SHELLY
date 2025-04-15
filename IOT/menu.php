<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$utilisateur = $_SESSION['utilisateur'] ?? 'Invité';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="accueil.php">📡 Mon IoT</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      
      <!-- Liens à gauche -->
      <ul class="navbar-nav me-auto">

        <!-- Équipements -->
        <li class="nav-item">
          <a class="nav-link" href="equipement.php">➕ Ajouter équipement</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="liste_equipements.php">📋 Liste des équipements</a>
        </li>

        <!-- Contrôle -->
        <li class="nav-item">
          <a class="nav-link" href="control_group.php">🎛️ Contrôle groupé</a>
        </li>

        <!-- Énergie -->
        <li class="nav-item">
          <a class="nav-link" href="collecte_energy.php">⚡ Collecte énergie</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="liste_aenergy.php">📈 Historique énergie</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="consommation_par_periode.php">📅 Conso par période</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="consommation_multiple.php">📊 Conso multi-équipements</a>
        </li>

        <!-- Utilisateurs -->
        <li class="nav-item">
          <a class="nav-link" href="creer_utilisateur.php">👤 Ajouter utilisateur</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="liste_utilisateurs.php">👥 Utilisateurs</a>
        </li>

      </ul>

      <!-- Infos utilisateur à droite -->
      <ul class="navbar-nav align-items-center">
        <li class="nav-item me-3">
          <span class="navbar-text text-white">
            👤 <strong><?= htmlspecialchars($utilisateur) ?></strong>
          </span>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white fw-bold" href="logout.php">🚪 Déconnexion</a>
        </li>
      </ul>

    </div>
  </div>
</nav>