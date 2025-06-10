<!-- Inclure Font Awesome dans le <head> -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<?php
// Démarrer la session seulement si nécessaire
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$user_name = isset($_SESSION['user']) ? $_SESSION['user'] : 'Invité';
$nom_dossier = isset($_SESSION['dossier']) ? $_SESSION['dossier'] : 'Nom_Dossier';
$current_page = basename($_SERVER['PHP_SELF']);

// Lire niveau en session
$niveau = isset($_SESSION['niveau']) ? $_SESSION['niveau'] : 'basique';

// Exception spéciale pour Issiaga : toujours niveau avancé
if ($user_name === 'issiaga') {
    $niveau = 'avance';
}
?>

<aside class="w-64 h-screen bg-gray-800 text-white p-4 flex flex-col justify-between">
  <div>
    <!-- Partie utilisateur connecté -->
    <div class="flex items-center gap-3 mb-6 p-3 bg-gray-700 rounded">
      <div class="w-10 h-10 flex items-center justify-center bg-blue-500 rounded-full">
        <i class="fas fa-user text-white"></i>
      </div>
      <div>
        <p class="text-sm text-gray-300">Connecté : <?= htmlspecialchars($user_name) ?></p>
        <p class="text-xs italic text-gray-400">
          <?php
            if ($user_name === 'issiaga') {
              echo 'Niveau Avancé (Super Admin)';
            } else {
              switch ($niveau) {
                case 'avance':
                  echo 'Niveau Avancé';
                  break;
                case 'intermediaire':
                  echo 'Niveau Intermédiaire';
                  break;
                default:
                  echo 'Niveau Basique';
              }
            }
          ?>
        </p>
      </div>
    </div>

    <!-- Nom du dossier -->
    <div class="text-center mb-6">
      <h2 class="text-lg font-bold border-b border-gray-600 pb-2"><?= htmlspecialchars($nom_dossier) ?></h2>
    </div>

    <nav class="flex flex-col space-y-3">
      <a href="index.php" class="flex items-center gap-3 p-2 rounded hover:bg-gray-700 <?= $current_page == 'index.php' ? 'bg-gray-700' : '' ?>">
        <i class="fas fa-home text-blue-400"></i>
        <span>Tableau de bord</span>
      </a>

      <a href="clients.php" class="flex items-center gap-3 p-2 rounded hover:bg-gray-700 <?= $current_page == 'clients.php' ? 'bg-gray-700' : '' ?>">
        <i class="fas fa-users text-green-400"></i>
        <span>Clients</span>
      </a>

      <a href="fournisseurs.php" class="flex items-center gap-3 p-2 rounded hover:bg-gray-700 <?= $current_page == 'fournisseurs.php' ? 'bg-gray-700' : '' ?>">
        <i class="fas fa-store text-yellow-400"></i>
        <span>Fournisseurs</span>
      </a>

      <?php if ($niveau === 'avance' || $user_name === 'issiaga' || $niveau === 'intermediaire') : ?>
      <a href="parametres.php" class="flex items-center gap-3 p-2 rounded hover:bg-gray-700 <?= $current_page == 'parametres.php' ? 'bg-gray-700' : '' ?>">
        <i class="fas fa-cog text-purple-400"></i>
        <span>Paramètres</span>
      </a>
      <?php endif; ?>
    </nav>
  </div>

  <a href="logout.php" class="flex items-center gap-3 mt-4 bg-red-600 hover:bg-red-700 text-white justify-center p-2 rounded">
    <i class="fas fa-sign-out-alt"></i>
    <span>Déconnexion</span>
  </a>
</aside>
