<?php
// modules/journaux/tableau.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/../../db_init.php';

// Récupérer le niveau de l'utilisateur connecté pour l'affichage des boutons
$user_level = $_SESSION['niveau'] ?? 'basique';
$id_dossier_actif = $_SESSION['id_dossier'] ?? null;

$journaux = [];
$message = '';

if (!$id_dossier_actif) {
    $message = "Veuillez sélectionner un dossier pour afficher les journaux.";
} else {
    $sql = "SELECT id, code, libelle FROM journaux_comptables WHERE id_dossier = ? ORDER BY code ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_dossier_actif]);
    $journaux = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($journaux) && $id_dossier_actif) {
        $message = "Aucun journal comptable trouvé pour le dossier sélectionné.";
    }
}
?>

<div class="flex justify-between items-center mb-4">
  <h2 class="text-xl font-semibold">Journaux Comptables</h2>
  <?php if ($user_level === 'avance' && $id_dossier_actif): // Seul le niveau 'avance' peut ajouter et un dossier doit être sélectionné ?>
    <button id="btn-ajouter-journal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">➕ Ajouter</button>
  <?php elseif (!$id_dossier_actif): ?>
    <span class="text-gray-500 text-sm">Sélectionnez un dossier pour ajouter</span>
  <?php endif; ?>
</div>

<div class="bg-white border rounded shadow overflow-auto">
  <table class="min-w-full text-sm text-left">
    <thead class="bg-gray-100">
      <tr>
        <th class="px-4 py-2">Code</th>
        <th class="px-4 py-2">Libellé</th>
        <th class="px-4 py-2 text-center">Actions</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
      <?php if (!empty($message)): ?>
        <tr>
          <td colspan="3" class="px-4 py-2 text-center text-gray-500"><?= htmlspecialchars($message) ?></td>
        </tr>
      <?php elseif (empty($journaux)): // Ce cas ne devrait plus arriver si $message est géré ?>
        <tr>
          <td colspan="3" class="px-4 py-2 text-center text-gray-500">Aucun journal comptable trouvé.</td>
        </tr>
      <?php else: ?>
        <?php foreach ($journaux as $journal): ?>
          <tr>
            <td class="px-4 py-2"><?= htmlspecialchars($journal['code']) ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($journal['libelle']) ?></td>
            <td class="px-4 py-2 text-center">
              <?php if ($user_level === 'avance'): ?>
                <button onclick="modifierJournal(<?= $journal['id'] ?>)" class="text-orange-500 hover:text-orange-600 mr-2">✏️</button>
                <button onclick="supprimerJournal(<?= $journal['id'] ?>)" class="text-red-500 hover:text-red-600">🗑</button>
              <?php else: ?>
                <span class="text-gray-400 text-xs">Non autorisé</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>