<?php
// C'est CRITIQUE pour que $_SESSION soit accessible dans ce script AJAX.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Inclure db_init.php pour la connexion à la base de données
include_once __DIR__ . '/../../db_init.php';

// Récupérer le niveau de l'utilisateur connecté pour l'affichage
// Ceci est maintenant fiable car session_start() a été appelé.
$user_level = $_SESSION['niveau'] ?? 'basique';

// --- DÉBOGAGE : Ajout de lignes pour voir les valeurs de session ---
// Laissez ces lignes pour confirmer que le user_level est bien "avance"
error_log("DEBUG: modules/utilisateurs/tableau.php - SESSION: " . print_r($_SESSION, true));
error_log("DEBUG: modules/utilisateurs/tableau.php - user_level: " . $user_level);
error_log("DEBUG: modules/utilisateurs/tableau.php - Condition d'affichage bouton: " . ($user_level === 'avance' ? 'TRUE' : 'FALSE'));
// --- FIN DÉBOGAGE ---


$sql = "SELECT u.id, u.login, u.email, u.niveau AS user_niveau, -- Récupérer le niveau de l'utilisateur affiché
               GROUP_CONCAT(d.raison_sociale, ', ') AS dossiers
        FROM utilisateurs u
        LEFT JOIN dossier_utilisateur du ON u.id = du.id_utilisateur
        LEFT JOIN dossiers d ON du.id_dossier = d.id
        GROUP BY u.id, u.login, u.email, u.niveau -- Grouper aussi par niveau
        ORDER BY u.login ASC";

$stmt = $pdo->query($sql);
$utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="flex justify-between items-center mb-4">
  <h2 class="text-xl font-semibold">Utilisateurs</h2>
  <?php if ($user_level === 'avance'): // Seul le niveau 'avance' peut ajouter des utilisateurs ?>
    <button id="btn-ajouter-utilisateur" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">➕ Ajouter</button>
  <?php endif; ?>
</div>

<div class="bg-white border rounded shadow overflow-auto">
  <table class="min-w-full text-sm text-left">
    <thead class="bg-gray-100">
      <tr>
        <th class="px-4 py-2">Nom</th>
        <th class="px-4 py-2">Email</th>
        <th class="px-4 py-2">Niveau</th> <th class="px-4 py-2">Dossiers</th>
        <th class="px-4 py-2 text-center">Actions</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
      <?php foreach ($utilisateurs as $u): ?>
        <tr>
          <td class="px-4 py-2"><?= htmlspecialchars($u['login']) ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($u['email']) ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($u['user_niveau']) ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($u['dossiers'] ?? 'Aucun') ?></td>
          <td class="px-4 py-2 text-center">
            <?php
            // Laissez les logs de débogage pour les actions si nécessaire
            error_log("DEBUG: modules/utilisateurs/tableau.php - User {$u['login']} level: {$u['user_niveau']}. Connected user level for actions: {$user_level}");
            if ($user_level === 'avance'):
            ?>
              <button onclick="modifierUtilisateur(<?= $u['id'] ?>)" class="text-orange-500 hover:text-orange-600 mr-2">✏️</button>
              <?php if ($u['login'] !== 'issiaga' && $u['id'] !== ($_SESSION['user_id'] ?? null)): // Ajout de la condition pour ne pas se supprimer soi-même ?>
              <button onclick="supprimerUtilisateur(<?= $u['id'] ?>)" class="text-red-500 hover:text-red-600">🗑</button>
              <?php endif; ?>
            <?php else: ?>
              <span class="text-gray-400 text-xs">Non autorisé</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>