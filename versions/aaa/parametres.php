<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include "includes/auth.php"; 
include "includes/header.php";
include_once __DIR__ . '/db_init.php';

// Protection spéciale : seuls niveau "avancé", "intermédiaire" OU issiaga peuvent accéder
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

  <main class="flex-1 bg-gray-100 flex flex-col h-screen overflow-hidden p-6">
    <h1 class="text-2xl font-bold mb-6">Paramètres</h1>

    <div class="flex h-full overflow-hidden border rounded shadow bg-white">
      <aside class="w-64 border-r bg-gray-50 p-4 space-y-4">
        <button id="btnDossiers" class="w-full text-left p-2 rounded hover:bg-gray-200 transition active">📁 Dossiers</button>
        <button id="btnUtilisateurs" class="w-full text-left p-2 rounded hover:bg-gray-200 transition">👤 Utilisateurs</button>
      </aside>

      <div class="flex-1 p-6 overflow-y-auto relative" id="contentArea">
        <div id="loader" class="hidden absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 z-10">
          <div class="animate-spin rounded-full h-10 w-10 border-t-4 border-blue-600"></div>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- Modal Ajouter/Modifier Dossier -->
<div id="modal-dossier" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
  <div class="bg-white p-6 rounded shadow-md w-full max-w-md">
    <h2 id="modalTitleDossier" class="text-xl font-bold mb-4">Ajouter un dossier</h2>
    <form id="formDossier">
      <input type="hidden" name="action" id="actionTypeDossier" value="ajouter">
      <input type="hidden" name="id" id="dossierId">
      <div class="mb-4">
        <label class="block mb-1">Raison sociale</label>
        <input type="text" name="raison_sociale" id="raisonSocialeInput" required class="w-full p-2 border rounded">
      </div>
      <div class="mb-4">
        <label class="block mb-1">SIREN</label>
        <input type="text" name="siren" id="sirenInput" required class="w-full p-2 border rounded" maxlength="9" pattern="\d{9}">
      </div>
      <div class="flex justify-end gap-2">
        <button type="button" id="cancelBtnDossier" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Annuler</button>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Valider</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Ajouter/Modifier Utilisateur -->
<div id="modal-utilisateur" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
  <div class="bg-white p-6 rounded shadow-md w-full max-w-md">
    <h2 id="modalTitleUtilisateur" class="text-xl font-bold mb-4">Ajouter un utilisateur</h2>
    <form id="formUtilisateur">
      <input type="hidden" name="action" id="actionTypeUtilisateur" value="ajouter_utilisateur">
      <input type="hidden" name="id" id="utilisateurId">
      <input type="hidden" name="id_dossier" id="utilisateurIdDossier">
      <div class="mb-4">
        <label class="block mb-1">Login</label>
        <input type="text" name="login" id="loginInput" required class="w-full p-2 border rounded">
      </div>
      <div class="mb-4">
        <label class="block mb-1">Mot de passe</label>
        <input type="password" name="password" id="passwordInput" class="w-full p-2 border rounded">
        <small id="passwordHelp" class="text-gray-500 hidden">Laisser vide pour conserver l'ancien mot de passe</small>
      </div>
      <div class="mb-4">
        <label class="block mb-1">Niveau</label>
        <select name="niveau" id="niveauInput" required class="w-full p-2 border rounded">
          <option value="">-- Sélectionner --</option>
          <option value="basique">Basique</option>
          <option value="intermediaire">Intermédiaire</option>
          <option value="avance">Avancé</option>
        </select>
      </div>
      <div class="flex justify-end gap-2">
        <button type="button" id="cancelBtnUtilisateur" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Annuler</button>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Valider</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Confirmation Suppression -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
  <div class="bg-white p-6 rounded shadow-md w-full max-w-md">
    <h2 class="text-xl font-bold mb-4 text-center">Confirmer la suppression</h2>
    <p class="text-center mb-6">Voulez-vous vraiment supprimer cet élément ?</p>
    <div class="flex justify-center gap-4">
      <button id="confirmDeleteBtn" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Supprimer</button>
      <button id="cancelDeleteBtn" class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">Annuler</button>
    </div>
  </div>
</div>

<!-- Toast Container -->
<div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

<?php include "includes/footer.php"; ?>
<script>
// Variables globales
let contentArea, loader, btnDossiers, btnUtilisateurs;
let currentDossierId = null;

document.addEventListener('DOMContentLoaded', () => {
  contentArea = document.getElementById('contentArea');
  loader = document.getElementById('loader');
  btnDossiers = document.getElementById('btnDossiers');
  btnUtilisateurs = document.getElementById('btnUtilisateurs');

  btnDossiers.onclick = loadDossiers;
  btnUtilisateurs.onclick = loadUtilisateurs;

  document.getElementById('cancelBtnDossier').onclick = () => document.getElementById('modal-dossier').classList.add('hidden');
  document.getElementById('cancelBtnUtilisateur').onclick = () => document.getElementById('modal-utilisateur').classList.add('hidden');
  document.getElementById('cancelDeleteBtn').onclick = () => document.getElementById('deleteModal').classList.add('hidden');

  document.getElementById('confirmDeleteBtn').onclick = () => {
    if (window.toDelete && window.deleteType) {
      const action = window.deleteType === 'dossier' ? 'supprimer' : 'supprimer_utilisateur';
      const url = window.deleteType === 'dossier' ? 'dossiers.php' : 'utilisateurs.php';

      fetch(url, {
        method: 'POST',
        body: new URLSearchParams({ ajax: 1, action: action, id: window.toDelete })
      })
      .then(res => res.json())
      .then(data => {
        showToast(data.status, data.message);
        if (window.deleteType === 'dossier') loadDossiers();
        else chargerUtilisateurs(currentDossierId);
      });
    }
    window.toDelete = null;
    document.getElementById('deleteModal').classList.add('hidden');
  };

  loadDossiers();
});

function setActiveButton(activeBtn) {
  [btnDossiers, btnUtilisateurs].forEach(btn => btn.classList.remove('bg-blue-100', 'text-blue-700', 'font-semibold'));
  activeBtn.classList.add('bg-blue-100', 'text-blue-700', 'font-semibold');
}

function showToast(type, message) {
  const container = document.getElementById('toast-container');
  const toast = document.createElement('div');
  toast.className = `p-4 rounded shadow-md ${type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'}`;
  toast.textContent = message;
  container.appendChild(toast);
  setTimeout(() => toast.remove(), 3000);
}

// GESTION DOSSIERS
function loadDossiers() {
  loader.classList.remove('hidden');
  fetch('dossiers.php', {
    method: 'POST',
    body: new URLSearchParams({ ajax: 1, action: 'charger' })
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'success') {
      contentArea.innerHTML = `
        <div>
          <h2 class="text-2xl font-bold mb-4">Gestion des dossiers</h2>
          <div class="flex items-center gap-4 mb-4">
            <button id="addDossierBtn" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">➕ Ajouter un dossier</button>
            <input type="text" id="searchInput" placeholder="🔍 Rechercher..." class="p-2 border rounded ml-auto">
          </div>
          <div class="bg-white shadow rounded overflow-y-auto" style="max-height: calc(100vh - 20rem);">
            <table class="min-w-full table-auto text-base">
              <thead class="sticky top-0 bg-gray-200">
                <tr><th class="p-2 border-b">Raison sociale</th><th class="p-2 border-b">SIREN</th><th class="p-2 border-b">Actions</th></tr>
              </thead>
              <tbody id="dossiersBody">${data.html}</tbody>
            </table>
          </div>
        </div>`;
      bindDossierEvents();
      setActiveButton(btnDossiers);
    }
  })
  .finally(() => loader.classList.add('hidden'));
}

function bindDossierEvents() {
  document.getElementById('addDossierBtn').onclick = () => {
    document.getElementById('modalTitleDossier').textContent = 'Ajouter un dossier';
    document.getElementById('actionTypeDossier').value = 'ajouter';
    document.getElementById('dossierId').value = '';
    document.getElementById('raisonSocialeInput').value = '';
    document.getElementById('sirenInput').value = '';
    document.getElementById('modal-dossier').classList.remove('hidden');
  };

  document.querySelectorAll('[data-edit]').forEach(btn => {
    btn.onclick = () => {
      document.getElementById('modalTitleDossier').textContent = 'Modifier le dossier';
      document.getElementById('actionTypeDossier').value = 'modifier';
      document.getElementById('dossierId').value = btn.dataset.id;
      document.getElementById('raisonSocialeInput').value = btn.dataset.raison;
      document.getElementById('sirenInput').value = btn.dataset.siren;
      document.getElementById('modal-dossier').classList.remove('hidden');
    };
  });

  document.querySelectorAll('[data-delete]').forEach(btn => {
    btn.onclick = () => {
      window.toDelete = btn.dataset.delete;
      window.deleteType = 'dossier';
      document.getElementById('deleteModal').classList.remove('hidden');
    };
  });

  document.getElementById('formDossier').onsubmit = e => {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('ajax', 1);
    fetch('dossiers.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
      showToast(data.status, data.message);
      if (data.status === 'success') {
        document.getElementById('modal-dossier').classList.add('hidden');
        loadDossiers();
      }
    });
  };

  document.getElementById('searchInput').oninput = e => {
    const value = e.target.value.toLowerCase();
    document.querySelectorAll('#dossiersBody tr').forEach(row => {
      row.style.display = row.innerText.toLowerCase().includes(value) ? '' : 'none';
    });
  };
}

// GESTION UTILISATEURS
function loadUtilisateurs() {
  loader.classList.remove('hidden');
  fetch('utilisateurs.php', {
    method: 'POST',
    body: new URLSearchParams({ ajax: 1, action: 'charger_dossiers' })
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'success') {
      contentArea.innerHTML = `
        <div class="flex h-full">
          <aside class="w-64 border-r bg-gray-100 p-4 space-y-2 overflow-y-auto">
            <input type="text" id="rechercheDossier" class="mb-4 w-full p-2 border rounded" placeholder="🔍 Rechercher...">
            ${data.dossiers.map(d => `
              <button class="dossier-btn w-full text-left p-2 rounded hover:bg-gray-200 transition" data-id="${d.id}">
                ${d.raison_sociale}
              </button>
            `).join('')}
          </aside>
          <div class="flex-1 p-4 overflow-y-auto" id="utilisateursContent">
            <p class="text-gray-400">Sélectionnez un dossier pour voir ses utilisateurs.</p>
          </div>
        </div>`;

      document.getElementById('rechercheDossier').oninput = e => {
        const term = e.target.value.toLowerCase();
        document.querySelectorAll('.dossier-btn').forEach(btn => {
          const visible = btn.textContent.toLowerCase().includes(term);
          btn.style.display = visible ? '' : 'none';
        });
      };

      document.querySelectorAll('.dossier-btn').forEach(btn => {
        btn.onclick = () => chargerUtilisateurs(btn.dataset.id);
      });

      setActiveButton(btnUtilisateurs);
    }
  })
  .finally(() => loader.classList.add('hidden'));
}

function chargerUtilisateurs(idDossier) {
  currentDossierId = idDossier;
  const utilisateursContent = document.getElementById('utilisateursContent');
  loader.classList.remove('hidden');

  // Mise en évidence du bouton sélectionné
  document.querySelectorAll('.dossier-btn').forEach(btn => {
    btn.classList.remove('bg-blue-100', 'font-semibold', 'text-blue-700');
  });
  const selectedBtn = document.querySelector(`.dossier-btn[data-id="${idDossier}"]`);
  if (selectedBtn) {
    selectedBtn.classList.add('bg-blue-100', 'font-semibold', 'text-blue-700');
  }

  fetch('utilisateurs.php', {
    method: 'POST',
    body: new URLSearchParams({
      ajax: 1,
      action: 'charger_utilisateurs',
      id_dossier: idDossier
    })
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'success') {
      utilisateursContent.innerHTML = `
        <div>
          <h2 class="text-2xl font-bold mb-4">Utilisateurs</h2>
          <div class="flex items-center justify-between mb-4">
            <button onclick="ouvrirModalAjoutUtilisateur(${idDossier})" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">➕ Ajouter un utilisateur</button>
          </div>
          <div class="bg-white shadow rounded overflow-y-auto max-h-[60vh]">
            <table class="min-w-full text-base">
              <thead class="bg-gray-200">
                <tr>
                  <th class="p-2 border-b">Login</th>
                  <th class="p-2 border-b">Niveau</th>
                  <th class="p-2 border-b">Actions</th>
                </tr>
              </thead>
              <tbody id="utilisateursBody">${data.html}</tbody>
            </table>
          </div>
        </div>`;
      
      // Liez les événements après le chargement
      bindActionsUtilisateurs();
    } else {
      utilisateursContent.innerHTML = `<p class='text-red-500'>${data.message || "Erreur lors du chargement des utilisateurs."}</p>`;
    }
  })
  .catch(error => {
    console.error('Erreur AJAX :', error);
    utilisateursContent.innerHTML = "<p class='text-red-500'>Erreur lors du chargement des utilisateurs.</p>";
  })
  .finally(() => loader.classList.add('hidden'));
}

function ouvrirModalAjoutUtilisateur(idDossier) {
  document.getElementById('modalTitleUtilisateur').textContent = 'Ajouter un utilisateur';
  document.getElementById('actionTypeUtilisateur').value = 'ajouter_utilisateur';
  document.getElementById('utilisateurId').value = '';
  document.getElementById('utilisateurIdDossier').value = idDossier;
  document.getElementById('loginInput').value = '';
  document.getElementById('passwordInput').value = '';
  document.getElementById('passwordInput').required = true;
  document.getElementById('passwordHelp').classList.add('hidden');
  document.getElementById('niveauInput').value = 'basique';
  document.getElementById('modal-utilisateur').classList.remove('hidden');
}

function bindActionsUtilisateurs() {
  // Boutons d'édition
  document.querySelectorAll('[data-edit]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('modalTitleUtilisateur').textContent = 'Modifier utilisateur';
      document.getElementById('actionTypeUtilisateur').value = 'modifier_utilisateur';
      document.getElementById('utilisateurId').value = btn.dataset.id;
      document.getElementById('utilisateurIdDossier').value = currentDossierId;
      document.getElementById('loginInput').value = btn.dataset.login;
      document.getElementById('niveauInput').value = btn.dataset.niveau;
      document.getElementById('passwordInput').value = '';
      document.getElementById('passwordInput').required = false;
      document.getElementById('passwordHelp').classList.remove('hidden');
      document.getElementById('modal-utilisateur').classList.remove('hidden');
    });
  });

  // Boutons de suppression
  document.querySelectorAll('[data-delete]').forEach(btn => {
    btn.addEventListener('click', () => {
      window.toDelete = btn.dataset.delete;
      window.deleteType = 'utilisateur';
      document.getElementById('deleteModal').classList.remove('hidden');
    });
  });

  // Formulaire utilisateur
  document.getElementById('formUtilisateur').addEventListener('submit', (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('ajax', 1);
    
    fetch('utilisateurs.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      showToast(data.status, data.message);
      if (data.status === 'success') {
        document.getElementById('modal-utilisateur').classList.add('hidden');
        chargerUtilisateurs(currentDossierId);
      }
    })
    .catch(error => {
      showToast('error', 'Erreur lors de l\'opération');
      console.error(error);
    });
  });
}
</script>