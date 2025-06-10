<?php
include "includes/auth.php";
include "includes/header.php";

if (!isset($_SESSION['niveau']) || 
    ($_SESSION['niveau'] !== 'avance' && 
     $_SESSION['niveau'] !== 'intermediaire' &&
     (!isset($_SESSION['user']) || $_SESSION['user'] !== 'issiaga'))
) {
    header('Location: index.php');
    exit;
}
?>


<div class="flex min-h-screen">
  <?php include "includes/sidebar.php"; ?>

  <main class="flex-1 bg-gray-100 p-6 overflow-auto">
    <h1 class="text-2xl font-bold mb-4">Paramètres</h1>

    <!-- Onglets -->
    <div class="flex space-x-4 mb-4">
      <button class="tab-button bg-white text-blue-600 font-medium px-4 py-2 rounded-t shadow-sm border-b-2 border-blue-500 transition" data-tab="dossiers">
        🗂 Dossiers
      </button>
      <button class="tab-button text-gray-600 hover:bg-gray-100 px-4 py-2 rounded-t border-b-2 border-transparent transition-colors duration-200" data-tab="utilisateurs">
        👥 Utilisateurs
      </button>
    </div>

    <!-- Contenu -->
<div id="contenu-parametres">
  <?php
    include "modules/dossiers/tableau.php";
    include "modules/dossiers/modals.php"; // ce fichier contient le modal-suppression
  ?>
</div>
  </main>
</div>

<!-- Toast -->
<div id="toast" class="fixed bottom-4 right-4 z-50 bg-green-500 text-white px-4 py-2 rounded shadow-lg hidden transition-opacity duration-300">
  <span id="toast-message">Notification</span>
</div>

<?php include "includes/footer.php"; ?>

<!-- Script dynamique -->
<script>
// Fonction pour charger les scripts dynamiquement
function loadScript(src, callback) {
  const oldScript = document.getElementById('dynamic-module-script');
  if (oldScript) oldScript.remove();

  const script = document.createElement('script');
  script.src = src;
  script.id = 'dynamic-module-script';
  script.onload = callback;
  document.body.appendChild(script);
}

// Fonction pour afficher les toasts
function showToast(message, color = "bg-green-500") {
  const toast = document.getElementById("toast");
  const toastMessage = document.getElementById("toast-message");

  toastMessage.textContent = message;
  toast.className = `fixed bottom-4 right-4 z-50 text-white px-4 py-2 rounded shadow-lg ${color}`;
  toast.classList.remove("hidden");

  setTimeout(() => {
    toast.classList.add("hidden");
  }, 3000);
}

// Gestion des onglets - Modifier cette partie
document.querySelectorAll('.tab-button').forEach(button => {
  button.addEventListener('click', () => {
    // Styles des onglets (conserver existant)
    
    const tab = button.getAttribute('data-tab');
    let tableauFile, modalFile, jsFile;

    if (tab === 'dossiers') {
      tableauFile = 'modules/dossiers/tableau.php';
      modalFile = 'modules/dossiers/modals.php';
      jsFile = 'modules/dossiers/script.js';
    } else if (tab === 'utilisateurs') {
      tableauFile = 'modules/utilisateurs/tableau.php';
      modalFile = 'modules/utilisateurs/modals.php';
      jsFile = 'modules/utilisateurs/script.js';
    }

    // Chargement du contenu
    Promise.all([
      fetch(tableauFile).then(res => res.text()),
      fetch(modalFile).then(res => res.text())
    ]).then(([tableHtml, modalsHtml]) => {
      const container = document.getElementById('contenu-parametres');
      container.innerHTML = tableHtml + modalsHtml;
      loadScript(jsFile, () => {
        if (tab === 'dossiers') {
          if (typeof initialiserFormulaireDossier === 'function') initialiserFormulaireDossier();
          if (typeof reinitialiserEvenementsBoutons === 'function') reinitialiserEvenementsBoutons();
        } else if (tab === 'utilisateurs') {
          if (typeof initialiserFormulaireUtilisateur === 'function') initialiserFormulaireUtilisateur();
          if (typeof reinitialiserEvenementsUtilisateurs === 'function') reinitialiserEvenementsUtilisateurs();
        }
      });
    });
  });
});

// Initialisation au chargement
document.addEventListener('DOMContentLoaded', function() {
  // Vérifie quel onglet est actif au chargement
  const activeTab = document.querySelector('.tab-button.bg-white')?.getAttribute('data-tab') || 'dossiers';
  
  if (activeTab === 'utilisateurs') {
    loadScript('modules/utilisateurs/script.js', () => {
      if (typeof chargerUtilisateurs === 'function') chargerUtilisateurs();
    });
  } else {
    loadScript('modules/dossiers/script.js', () => {
      if (typeof initialiserFormulaireDossier === 'function') initialiserFormulaireDossier();
      if (typeof reinitialiserEvenementsBoutons === 'function') reinitialiserEvenementsBoutons();
    });
  }
});
</script>