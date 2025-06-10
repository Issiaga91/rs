<!-- index.php -->

<?php include "includes/auth.php"; ?>

<?php include "includes/header.php"; ?>

<div class="flex">
  <!-- Sidebar -->
  <?php include "includes/sidebar.php"; ?>


  <!-- Contenu principal -->
  <main class="flex-1 p-6 bg-gray-100 min-h-screen">
    <h1 class="text-2xl font-semibold mb-4">Bienvenue sur le tableau de bord</h1>
    <p class="text-gray-700">Ici tu pourras gérer tes fournisseurs et tes pièces comptables.</p>
  </main>
</div>

<?php include "includes/footer.php"; ?>
