<!-- Modal d'ajout/modification de dossier -->
<div id="modal-dossier" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
    <h2 class="text-lg font-semibold mb-4" id="modal-titre">Ajouter un dossier</h2>
    <form id="form-dossier" class="dossier-form">
      <input type="hidden" name="id" id="dossier-id" value="" />
      <div class="mb-4">
        <label for="raison_sociale" class="block text-sm font-medium">Raison sociale</label>
        <input type="text" name="raison_sociale" id="raison_sociale" class="mt-1 w-full border rounded px-3 py-2" required />
      </div>
      <div class="mb-4">
        <label for="siren" class="block text-sm font-medium">SIREN</label>
        <input type="text" name="siren" id="siren" class="mt-1 w-full border rounded px-3 py-2" pattern="[0-9]{9}" maxlength="9" title="Le SIREN doit contenir 9 chiffres" />
      </div>
      <div class="flex justify-end gap-2">
        <button type="button" onclick="fermerModalDossier()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Annuler</button>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Enregistrer</button>
      </div>
    </form>
    <button onclick="fermerModalDossier()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">&times;</button>
  </div>
</div>

<!-- Modal de confirmation de suppression -->
<div id="modal-suppression" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
    <h2 class="text-lg font-semibold mb-4 text-red-600">Confirmer la suppression</h2>
    <p class="mb-6 text-gray-700">Êtes-vous sûr de vouloir supprimer ce dossier ? Cette action est irréversible.</p>
    <div class="flex justify-end gap-2">
      <button type="button" onclick="fermerModalSuppression()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Annuler</button>
      <button id="btn-confirmer-suppression" type="button" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Supprimer</button>
    </div>
    <button onclick="fermerModalSuppression()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">&times;</button>
  </div>
</div>
