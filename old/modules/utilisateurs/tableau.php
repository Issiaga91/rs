<?php
include_once __DIR__ . '/../../db/init.php';

try {
    $sql = "SELECT u.id, u.login, u.email,
                   GROUP_CONCAT(d.raison_sociale, ', ') AS dossiers
            FROM utilisateurs u
            LEFT JOIN dossier_utilisateur du ON u.id = du.id_utilisateur
            LEFT JOIN dossiers d ON du.id_dossier = d.id
            GROUP BY u.id, u.login, u.email
            ORDER BY u.login ASC";

    $stmt = $pdo->query($sql);
    $utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $utilisateurs = [];
    error_log("Erreur SQL: " . $e->getMessage());
}
?>

<div class="flex justify-between items-center mb-4">
  <h2 class="text-xl font-semibold">Gestion des utilisateurs</h2>
  <button id="btn-ajouter-utilisateur" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded flex items-center gap-2">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
      <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
    </svg>
    Ajouter
  </button>
</div>

<div class="bg-white border rounded-lg shadow overflow-hidden">
  <?php if (empty($utilisateurs)): ?>
    <div class="p-8 text-center text-gray-500">
      Aucun utilisateur trouvé
    </div>
  <?php else: ?>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dossiers</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <?php foreach ($utilisateurs as $u): ?>
            <tr class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($u['login']) ?></td>
              <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($u['email']) ?></td>
              <td class="px-6 py-4"><?= $u['dossiers'] ? htmlspecialchars($u['dossiers']) : 'Aucun' ?></td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button onclick="modifierUtilisateur(<?= $u['id'] ?>)" class="text-blue-600 hover:text-blue-900 mr-3" title="Modifier">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                  </svg>
                </button>
                <button onclick="supprimerUtilisateur(<?= $u['id'] ?>)" class="text-red-600 hover:text-red-900" title="Supprimer">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                  </svg>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>