<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Protéger l'accès direct
if (!isset($_POST['ajax']) || $_POST['ajax'] != 1) {
    http_response_code(403);
    echo "Accès interdit.";
    exit;
}

include "includes/auth.php";
include_once __DIR__ . '/db_init.php';

// Activer les clés étrangères
$db->exec('PRAGMA foreign_keys = ON');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $action = $_POST['action'];
    $response = ['status' => 'error', 'message' => 'Action inconnue.'];

    try {
        $db->beginTransaction();

        if ($action === 'ajouter') {
            $raison = trim($_POST['raison_sociale']);
            $siren = trim($_POST['siren']);
            
            if (empty($raison) || empty($siren)) {
                throw new Exception('Tous les champs sont obligatoires');
            }

            // Vérification unicité
            $stmt = $db->prepare("SELECT COUNT(*) FROM dossiers WHERE raison_sociale = ? OR siren = ?");
            $stmt->execute([$raison, $siren]);
            
            if ($stmt->fetchColumn() > 0) {
                throw new Exception('Un dossier avec cette raison sociale ou SIREN existe déjà');
            }

            $stmt = $db->prepare("INSERT INTO dossiers (raison_sociale, siren) VALUES (?, ?)");
            $stmt->execute([$raison, $siren]);
            
            $response = [
                'status' => 'success', 
                'message' => 'Dossier créé',
                'newId' => $db->lastInsertId()
            ];
        } 
        elseif ($action === 'modifier') {
            $id = (int)$_POST['id'];
            $raison = trim($_POST['raison_sociale']);
            $siren = trim($_POST['siren']);
            
            if ($id <= 0 || empty($raison) || empty($siren)) {
                throw new Exception('Tous les champs sont obligatoires');
            }

            // Vérification unicité
            $stmt = $db->prepare("SELECT COUNT(*) FROM dossiers WHERE (raison_sociale = ? OR siren = ?) AND id != ?");
            $stmt->execute([$raison, $siren, $id]);
            
            if ($stmt->fetchColumn() > 0) {
                throw new Exception('Un autre dossier avec cette raison sociale ou SIREN existe déjà');
            }

            $stmt = $db->prepare("UPDATE dossiers SET raison_sociale = ?, siren = ? WHERE id = ?");
            $stmt->execute([$raison, $siren, $id]);
            
            $response = [
                'status' => 'success', 
                'message' => 'Dossier mis à jour',
                'updatedId' => $id
            ];
        } 
        elseif ($action === 'supprimer') {
            $id = (int)$_POST['id'];
            if ($id <= 0) {
                throw new Exception('ID invalide');
            }

            // Suppression en cascade (SQLite gère mal les FOREIGN KEY)
            // 1. D'abord les utilisateurs
            $stmt = $db->prepare("DELETE FROM utilisateurs WHERE id_dossier = ?");
            $stmt->execute([$id]);
            $usersDeleted = $stmt->rowCount();

            // 2. Puis le dossier
            $stmt = $db->prepare("DELETE FROM dossiers WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() === 0) {
                throw new Exception('Dossier introuvable');
            }

            $response = [
                'status' => 'success', 
                'message' => 'Dossier supprimé ('.$usersDeleted.' utilisateurs supprimés)',
                'deletedId' => $id
            ];
        } 
        elseif ($action === 'charger') {
            ob_start();
            $stmt = $db->query("SELECT * FROM dossiers ORDER BY raison_sociale ASC");
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<tr class="border-b odd:bg-white even:bg-gray-100 hover:bg-blue-50 hover:shadow-md transition duration-300 ease-in-out">
                    <td class="p-2">'.htmlspecialchars($row['raison_sociale']).'</td>
                    <td class="p-2">'.htmlspecialchars($row['siren']).'</td>
                    <td class="p-2 flex items-center space-x-2">
                        <button data-edit data-id="'.$row['id'].'" 
                                data-raison="'.htmlspecialchars($row['raison_sociale']).'" 
                                data-siren="'.htmlspecialchars($row['siren']).'" 
                                class="p-1 hover:bg-blue-100 hover:text-blue-600 rounded-full transition">✏️</button>
                        <button data-delete="'.$row['id'].'" 
                                class="p-1 hover:bg-red-100 hover:text-red-600 rounded-full transition">🗑️</button>
                    </td>
                </tr>';
            }
            
            $html = ob_get_clean();
            echo json_encode(['status' => 'success', 'html' => $html]);
            exit;
        }

        $db->commit();
    } catch (Exception $e) {
        $db->rollBack();
        $response = ['status' => 'error', 'message' => $e->getMessage()];
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>

<!-- Interface HTML/JS reste identique à votre version originale -->

<!-- Zone Toast -->
<div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

<!-- Contenu Dossiers -->
<div>
  <h2 class="text-xl font-bold mb-4">Gestion des dossiers</h2>

  <div class="flex items-center gap-4 mb-4">
    <button id="addDossierBtn" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
      ➕ Ajouter un dossier
    </button>
    <div class="ml-auto">
      <input type="text" id="searchInput" placeholder="🔍 Rechercher..." class="p-2 border rounded">
    </div>
  </div>

  <div class="bg-white shadow rounded overflow-y-auto" style="max-height: calc(100vh - 20rem);">
    <table class="min-w-full table-auto border-collapse text-base">
      <thead class="sticky top-0 bg-gray-200 z-10 shadow-md">
        <tr class="text-left">
          <th class="p-2 border-b">Raison sociale</th>
          <th class="p-2 border-b">SIREN</th>
          <th class="p-2 border-b">Actions</th>
        </tr>
      </thead>
      <tbody id="dossiersBody"></tbody>
    </table>
  </div>
</div>

<!-- Modal Ajouter/Modifier -->
<div id="modal-dossier" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
  <div class="bg-white p-6 rounded shadow-md w-full max-w-md transform transition-all scale-95">
    <h2 id="modalTitle" class="text-xl font-bold mb-4">Ajouter un dossier</h2>
    <form id="formDossier">
      <input type="hidden" name="action" value="ajouter" id="actionType">
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
        <button type="button" id="cancelBtn" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition">Annuler</button>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Valider</button>
      </div>
    </form>
  </div>
</div>

<script>
function showToast(type, message) {
  const container = document.getElementById('toast-container');
  const toast = document.createElement('div');
  toast.className = `p-4 rounded shadow-md ${type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'}`;
  toast.textContent = message;
  container.appendChild(toast);
  setTimeout(() => toast.remove(), 3000);
}

function chargerDossiers() {
  fetch('dossiers.php', {
    method: 'POST',
    body: new URLSearchParams({ajax: 1, action: 'charger'})
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'success') {
      document.getElementById('dossiersBody').innerHTML = data.html;
      bindActions();
    }
  });
}

function bindActions() {
  document.querySelectorAll('[data-edit]').forEach(button => {
    button.onclick = () => {
      document.getElementById('modalTitle').textContent = 'Modifier le dossier';
      document.getElementById('actionType').value = 'modifier';
      document.getElementById('dossierId').value = button.dataset.id;
      document.getElementById('raisonSocialeInput').value = button.dataset.raison;
      document.getElementById('sirenInput').value = button.dataset.siren;
      document.getElementById('modal-dossier').classList.remove('hidden');
    };
  });

  document.querySelectorAll('[data-delete]').forEach(button => {
    button.onclick = () => {
      if (confirm('Confirmer la suppression du dossier et de tous ses utilisateurs ?')) {
        fetch('dossiers.php', {
          method: 'POST',
          body: new URLSearchParams({ajax: 1, action: 'supprimer', id: button.dataset.delete})
        })
        .then(res => res.json())
        .then(data => {
          showToast(data.status, data.message);
          chargerDossiers();
        });
      }
    };
  });
}

document.getElementById('formDossier').onsubmit = e => {
  e.preventDefault();
  const formData = new FormData(e.target);
  formData.append('ajax', 1);
  
  fetch('dossiers.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    showToast(data.status, data.message);
    if (data.status === 'success') {
      document.getElementById('modal-dossier').classList.add('hidden');
      chargerDossiers();
    }
  });
};

document.getElementById('addDossierBtn').onclick = () => {
  document.getElementById('modalTitle').textContent = 'Ajouter un dossier';
  document.getElementById('actionType').value = 'ajouter';
  document.getElementById('dossierId').value = '';
  document.getElementById('raisonSocialeInput').value = '';
  document.getElementById('sirenInput').value = '';
  document.getElementById('modal-dossier').classList.remove('hidden');
};

document.getElementById('cancelBtn').onclick = () => {
  document.getElementById('modal-dossier').classList.add('hidden');
};

document.getElementById('searchInput').addEventListener('input', function() {
  const searchValue = this.value.trim().toLowerCase();
  document.querySelectorAll('#dossiersBody tr').forEach(row => {
    const text = row.innerText.toLowerCase();
    row.style.display = text.includes(searchValue) ? '' : 'none';
  });
});

// Charger les dossiers au démarrage
chargerDossiers();
</script>