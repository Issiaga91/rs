<?php
// Cette seule ligne gère la session, la connexion BDD et la vérification de l'authentification.
require_once 'bootstrap.php';

// Toute la logique ci-dessous peut supposer que $pdo existe et que l'utilisateur est connecté.

// Récupérer l'ID du dossier actif depuis la session
$id_dossier_actif = $_SESSION['id_dossier'] ?? null;
$nom_dossier_actif = "Aucun dossier sélectionné";

if ($id_dossier_actif) {
    // Récupérer le nom du dossier pour l'affichage
    $stmt = $pdo->prepare("SELECT raison_sociale FROM dossiers WHERE id = ?");
    $stmt->execute([$id_dossier_actif]);
    $nom_dossier_actif = htmlspecialchars($stmt->fetchColumn() ?? 'Inconnu');
}

// On inclut le header HTML APRÈS la logique.
include "includes/header.php";
?>

<div class="flex min-h-screen">
  <?php include "includes/sidebar.php"; ?>

  <main class="flex-1 bg-gray-100 p-6 overflow-auto">
    <h1 class="text-2xl font-bold mb-4">Tableau de bord</h1>

    <?php if ($id_dossier_actif): ?>
      <p class="mb-4 text-lg">Vous travaillez actuellement sur le dossier : <span class="font-semibold text-blue-600"><?= $nom_dossier_actif ?></span></p>
      <?php else: ?>
      <p class="mb-4 text-lg text-red-500">Aucun dossier n'est sélectionné. Veuillez en choisir un dans le panneau latéral.</p>
      <?php endif; ?>
  </main>
</div>

<?php include "includes/footer.php"; ?>