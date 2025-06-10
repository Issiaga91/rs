<div class="flex justify-between items-center mb-4">
  <h2 class="text-xl font-semibold">Gestion des dossiers</h2>
  <div class="flex items-center space-x-2">
    <button id="btn-ajouter-dossier" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded flex items-center">
      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
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
      <tr>
          <td colspan="3" class="p-4 text-center text-gray-500">Chargement...</td>
      </tr>
    </tbody>
  </table>
</div>

<?php
// On inclut toujours les modales, car leur HTML est nécessaire dans la page.
include __DIR__ . '/modals.php';
?>