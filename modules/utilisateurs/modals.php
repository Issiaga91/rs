<?php
// modals.php
// Ce fichier est chargé via AJAX, il a donc besoin de ses propres initialisations.
include_once __DIR__ . '/../../db_init.php';

// Récupérer la liste des dossiers pour le sélecteur multiple
$dossiers = $pdo->query("SELECT id, raison_sociale FROM dossiers ORDER BY raison_sociale")->fetchAll(PDO::FETCH_ASSOC);
?>

<div id="modal-utilisateur" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
    <h2 class="text-lg font-semibold mb-4" id="modal-utilisateur-titre">Ajouter un utilisateur</h2>
    <form id="form-utilisateur">
      <input type="hidden" name="id" id="utilisateur-id" value="" />
      <div class="mb-4">
        <label for="login" class="block text-sm font-medium">Nom d'utilisateur</label>
        <input type="text" name="login" id="login" class="mt-1 w-full border rounded px-3 py-2" required />
      </div>
      <div class="mb-4">
        <label for="email" class="block text-sm font-medium">Email</label>
        <input type="email" name="email" id="email" class="mt-1 w-full border rounded px-3 py-2" required />
      </div>

      <div class="mb-4">
        <label for="mot_de_passe" class="block text-sm font-medium">Mot de passe</label>
        <input type="password" name="mot_de_passe" id="mot_de_passe" class="mt-1 w-full border rounded px-3 py-2" autocomplete="new-password" />
        <p class="text-sm text-gray-500 mt-1">Laissez vide pour ne pas modifier le mot de passe existant. Requis pour un nouvel utilisateur.</p>
      </div>
      
      <div class="mb-4">
        <label for="niveau" class="block text-sm font-medium">Niveau d'accès</label>
        <select name="niveau" id="niveau" class="mt-1 w-full border rounded px-3 py-2">
          <option value="avance">Avancé</option>
          <option value="intermediaire">Intermédiaire</option>
          <option value="basique">Basique</option>
        </select>
      </div>

      <div class="mb-4">
        <label for="dossiers" class="block text-sm font-medium">Dossiers</label>
        <select name="dossiers[]" id="dossiers" multiple class="mt-1 w-full border rounded px-3 py-2">
          <?php foreach ($dossiers as $d): ?>
            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['raison_sociale']) ?></option>
          <?php endforeach; ?>
        </select>
        <p class="text-sm text-gray-500 mt-1">Maintenez Ctrl (Windows) ou Cmd (Mac) pour sélection multiple</p>
      </div>
      <div class="flex justify-end gap-2">
        <button type="button" onclick="fermerModalUtilisateur()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Annuler</button>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Enregistrer</button>
      </div>
    </form>
  </div>
</div>