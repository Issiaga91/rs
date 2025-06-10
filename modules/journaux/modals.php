<?php
// modules/journaux/modals.php
// Plus besoin de db_init ici car le modal n'affiche plus de liste de dossiers
?>

<div id="modal-journal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
    <h2 class="text-lg font-semibold mb-4" id="modal-journal-titre">Ajouter un journal</h2>
    <form id="form-journal">
      <input type="hidden" name="id" id="journal-id" value="" />
      <div class="mb-4">
        <label for="code" class="block text-sm font-medium">Code du journal</label>
        <input type="text" name="code" id="code" class="mt-1 w-full border rounded px-3 py-2" required />
      </div>
      <div class="mb-4">
        <label for="libelle" class="block text-sm font-medium">Libellé du journal</label>
        <input type="text" name="libelle" id="libelle" class="mt-1 w-full border rounded px-3 py-2" required />
      </div>
      <div class="flex justify-end gap-2">
        <button type="button" onclick="fermerModalJournal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Annuler</button>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Enregistrer</button>
      </div>
    </form>
  </div>
</div>