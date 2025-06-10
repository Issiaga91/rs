<?php
// 1. On initialise TOUT avec une seule ligne (session, BDD, authentification)
require_once 'bootstrap.php';

// 2. On vérifie les droits d'accès pour CETTE page
require_access_level(['avance', 'intermediaire']);

// 3. On inclut le header HTML
include "includes/header.php";
?>

<div class="flex min-h-screen">
  <?php include "includes/sidebar.php"; // Le sidebar corrigé sera inclus ici ?>

  <main class="flex-1 bg-gray-100 p-6 overflow-auto">
    <h1 class="text-2xl font-bold mb-4">Paramètres</h1>

    <div class="flex space-x-4 border-b mb-4">
      <button class="tab-button" data-tab="dossiers">🗂 Dossiers</button>
      <button class="tab-button" data-tab="utilisateurs">👥 Utilisateurs</button>
      <button class="tab-button" data-tab="journaux">📚 Journaux</button>
    </div>

    <div id="contenu-parametres">
      <p class="text-center text-gray-500 p-8">Veuillez sélectionner un onglet.</p>
    </div>
  </main>
</div>

<script src="assets/js/parametres.js"></script>

<?php include "includes/footer.php"; ?>