<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<?php
// Note : session_start() et require_once ont été supprimés.
// On fait confiance à bootstrap.php qui prépare la session et la connexion $pdo.

$user_name = $_SESSION['user'] ?? 'Invité';
$current_page = basename($_SERVER['PHP_SELF']);
$niveau = $_SESSION['niveau'] ?? 'basique';

// Récupérer les dossiers associés à l'utilisateur connecté
$dossiers_utilisateur = [];
$dossier_selectionne_id = $_SESSION['id_dossier'] ?? null;
$nom_dossier_selectionne = "Aucun dossier sélectionné";

if (isset($_SESSION['user_id'])) {
    try {
        // On utilise la variable $pdo qui est déjà disponible
        $stmt = $pdo->prepare("SELECT d.id, d.raison_sociale FROM dossiers d JOIN dossier_utilisateur du ON d.id = du.id_dossier WHERE du.id_utilisateur = ? ORDER BY d.raison_sociale");
        $stmt->execute([$_SESSION['user_id']]);
        $dossiers_utilisateur = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($dossier_selectionne_id) {
            foreach ($dossiers_utilisateur as $d) {
                if ($d['id'] == $dossier_selectionne_id) {
                    $nom_dossier_selectionne = htmlspecialchars($d['raison_sociale']);
                    break;
                }
            }
        } else if (!empty($dossiers_utilisateur)) {
            // Si aucun dossier n'est sélectionné et l'utilisateur a des dossiers, sélectionner le premier par défaut
            $_SESSION['id_dossier'] = $dossiers_utilisateur[0]['id'];
            $dossier_selectionne_id = $dossiers_utilisateur[0]['id'];
            $nom_dossier_selectionne = htmlspecialchars($dossiers_utilisateur[0]['raison_sociale']);
        }
    } catch (PDOException $e) {
        // Gérer l'erreur de la base de données
        error_log("Erreur PDO dans sidebar.php: " . $e->getMessage());
    }
}
?>

<aside class="w-64 h-screen bg-gray-800 text-white p-4 flex flex-col justify-between">
    <div>
        <div class="flex items-center gap-3 mb-6 p-3 bg-gray-700 rounded">
            <div class="w-10 h-10 flex items-center justify-center bg-blue-500 rounded-full">
                <i class="fas fa-user text-white"></i>
            </div>
            <div>
                <p class="text-sm text-gray-300">Connecté : <?= htmlspecialchars($user_name) ?></p>
                <p class="text-xs text-gray-400">Niveau : <?= htmlspecialchars($niveau) ?></p>
            </div>
        </div>

        <div class="mb-6 p-3 bg-gray-700 rounded">
            <label for="select-dossier" class="block text-sm font-medium text-gray-300 mb-2">Dossier Actif :</label>
            <select id="select-dossier" class="w-full p-2 rounded bg-gray-600 border border-gray-500 text-white text-sm">
                <?php if (empty($dossiers_utilisateur)): ?>
                    <option value="">Aucun dossier disponible</option>
                <?php else: ?>
                    <?php foreach ($dossiers_utilisateur as $dossier): ?>
                        <option value="<?= $dossier['id'] ?>" <?= ($dossier_selectionne_id == $dossier['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($dossier['raison_sociale']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <p class="text-xs text-gray-400 mt-2">Dossier sélectionné: <span id="nom-dossier-actif"><?= $nom_dossier_selectionne ?></span></p>
        </div>

        <nav class="flex flex-col space-y-3">
            <a href="index.php" class="flex items-center gap-3 p-2 rounded hover:bg-gray-700 <?= $current_page == 'index.php' ? 'bg-gray-700' : '' ?>">
                <i class="fas fa-home text-blue-400"></i>
                <span>Tableau de bord</span>
            </a>

            <?php if ($niveau === 'avance' || $niveau === 'intermediaire') : ?>
            <a href="clients.php" class="flex items-center gap-3 p-2 rounded hover:bg-gray-700 <?= $current_page == 'clients.php' ? 'bg-gray-700' : '' ?>">
                <i class="fas fa-users text-green-400"></i>
                <span>Clients</span>
            </a>
            <?php endif; ?>

            <a href="fournisseurs.php" class="flex items-center gap-3 p-2 rounded hover:bg-gray-700 <?= $current_page == 'fournisseurs.php' ? 'bg-gray-700' : '' ?>">
                <i class="fas fa-store text-yellow-400"></i>
                <span>Fournisseurs</span>
            </a>

            <?php if ($niveau === 'avance' || $niveau === 'intermediaire') : ?>
            <a href="parametres.php" class="flex items-center gap-3 p-2 rounded hover:bg-gray-700 <?= $current_page == 'parametres.php' ? 'bg-gray-700' : '' ?>">
                <i class="fas fa-cog text-purple-400"></i> <span>Paramètres</span>
            </a>
            <?php endif; ?>
        </nav>
    </div>

    <div class="mt-auto p-3 text-center">
        <a href="logout.php" class="inline-flex items-center justify-center w-full px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">
            <i class="fas fa-sign-out-alt mr-2"></i> Déconnexion
        </a>
    </div>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectDossier = document.getElementById('select-dossier');
    const nomDossierActifSpan = document.getElementById('nom-dossier-actif');

    if (selectDossier) {
        selectDossier.addEventListener('change', function() {
            const selectedDossierId = this.value;
            const selectedDossierText = this.options[this.selectedIndex].text;

            if (nomDossierActifSpan) {
                nomDossierActifSpan.innerText = selectedDossierText;
            }

            fetch('api/set_dossier_session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id_dossier=' + selectedDossierId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (typeof showToast === 'function') {
                        showToast("Dossier changé : " + selectedDossierText);
                    }
                    // Recharger la page pour que tout le contenu s'actualise avec le nouveau dossier
                    window.location.reload();
                } else {
                    if (typeof showToast === 'function') {
                        showToast(data.message || "Erreur de changement.", false);
                    }
                }
            })
            .catch(error => {
                if (typeof showToast === 'function') {
                    showToast("Erreur réseau.", false);
                }
            });
        });
    }
});
</script>