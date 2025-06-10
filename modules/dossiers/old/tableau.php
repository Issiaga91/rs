<?php
include_once __DIR__ . '/../../db_init.php';

$recherche = isset($_GET['recherche']) ? trim($_GET['recherche']) : '';

$sql = "SELECT id, raison_sociale, siren FROM dossiers";
$params = [];

if (!empty($recherche)) {
    $sql .= " WHERE raison_sociale LIKE :recherche OR siren LIKE :recherche";
    $params[':recherche'] = "%$recherche%";
}

$sql .= " ORDER BY raison_sociale ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$dossiers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="flex justify-between items-center mb-4">
  <h2 class="text-xl font-semibold">Gestion des dossiers</h2>
  <div class="flex items-center space-x-2">
    <button id="btn-ajouter-dossier" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded flex items-center">
      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
      </svg>
      Ajouter un dossier
    </button>
    <input type="text" id="rechercheDossier" placeholder="🔍 Rechercher..." class="border border-gray-300 rounded px-3 py-1" />
  </div>
</div>

<div class="bg-white border rounded shadow overflow-auto">
  <table class="min-w-full text-sm text-left">
    <thead class="bg-gray-100">
      <tr>
        <th class="px-4 py-2 font-semibold text-gray-700">Raison sociale</th>
        <th class="px-4 py-2 font-semibold text-gray-700">SIREN</th>
        <th class="px-4 py-2 text-center font-semibold text-gray-700">Actions</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200" id="liste-dossiers">
      <?php foreach ($dossiers as $dossier): ?>
        <tr>
          <td class="px-4 py-2"><?= htmlspecialchars($dossier['raison_sociale']) ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($dossier['siren']) ?></td>
          <td class="px-4 py-2 text-center">
            <button onclick="openModalModification(<?= $dossier['id'] ?>)" class="text-orange-500 hover:text-orange-600 mr-2" title="Modifier">✏️</button>
            <button onclick="supprimerDossier(<?= $dossier['id'] ?>)" class="text-red-500 hover:text-red-600" title="Supprimer">🗑</button>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
