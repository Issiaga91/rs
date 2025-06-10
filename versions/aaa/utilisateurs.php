<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Vérification de la requête AJAX et authentification
if (!isset($_POST['ajax']) || $_POST['ajax'] != 1) {
    echo json_encode(['status' => 'error', 'message' => 'Requête non autorisée']);
    exit;
}

include "includes/auth.php";
$db = new SQLite3('db/database.sqlite');

$action = $_POST['action'] ?? '';

// Charger la liste des dossiers
if ($action === 'charger_dossiers') {
    try {
        $result = $db->query("SELECT id, raison_sociale FROM dossiers ORDER BY raison_sociale ASC");
        $dossiers = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $dossiers[] = $row;
        }
        echo json_encode(['status' => 'success', 'dossiers' => $dossiers]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Erreur SQL : ' . $e->getMessage()]);
    }
    exit;
}

// Charger les utilisateurs d'un dossier spécifique
if ($action === 'charger_utilisateurs') {
    $id_dossier = intval($_POST['id_dossier'] ?? 0);
    
    if ($id_dossier <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID dossier invalide']);
        exit;
    }

    try {
        $stmt = $db->prepare("SELECT id, login, niveau FROM utilisateurs WHERE id_dossier = :id_dossier ORDER BY login ASC");
        $stmt->bindValue(':id_dossier', $id_dossier, SQLITE3_INTEGER);
        $result = $stmt->execute();

        ob_start();
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
?>
            <tr class="border-b odd:bg-white even:bg-gray-100 hover:bg-blue-50">
                <td class="p-2"><?= htmlspecialchars($row['login']) ?></td>
                <td class="p-2"><?= htmlspecialchars($row['niveau']) ?></td>
                <td class="p-2 flex items-center space-x-2">
                    <button data-edit data-id="<?= $row['id'] ?>" 
                            data-login="<?= htmlspecialchars($row['login']) ?>" 
                            data-niveau="<?= htmlspecialchars($row['niveau']) ?>" 
                            class="p-1 hover:bg-blue-100 hover:text-blue-600 rounded-full transition">✏️</button>
                    <button data-delete="<?= $row['id'] ?>" 
                            class="p-1 hover:bg-red-100 hover:text-red-600 rounded-full transition">🗑️</button>
                </td>
            </tr>
<?php
        }
        $html = ob_get_clean();
        echo json_encode(['status' => 'success', 'html' => $html]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Erreur SQL : ' . $e->getMessage()]);
    }
    exit;
}

// Ajouter un nouvel utilisateur
if ($action === 'ajouter_utilisateur') {
    $id_dossier = intval($_POST['id_dossier']);
    $login = trim($_POST['login'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $niveau = trim($_POST['niveau'] ?? '');

    // Validation des données
    if ($id_dossier <= 0 || empty($login) || empty($password) || empty($niveau)) {
        echo json_encode(['status' => 'error', 'message' => 'Tous les champs sont obligatoires']);
        exit;
    }

    // Vérification de l'unicité du login
    try {
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM utilisateurs WHERE login = :login AND id_dossier = :id_dossier");
        $stmt->bindValue(':login', $login, SQLITE3_TEXT);
        $stmt->bindValue(':id_dossier', $id_dossier, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $exists = $result->fetchArray(SQLITE3_ASSOC)['count'];

        if ($exists > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Ce login est déjà utilisé pour ce dossier']);
            exit;
        }

        // Hashage du mot de passe
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Insertion
        $stmt = $db->prepare("INSERT INTO utilisateurs (id_dossier, login, mot_de_passe, niveau) VALUES (:id_dossier, :login, :mot_de_passe, :niveau)");
        $stmt->bindValue(':id_dossier', $id_dossier, SQLITE3_INTEGER);
        $stmt->bindValue(':login', $login, SQLITE3_TEXT);
        $stmt->bindValue(':mot_de_passe', $passwordHash, SQLITE3_TEXT);
        $stmt->bindValue(':niveau', $niveau, SQLITE3_TEXT);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Utilisateur ajouté avec succès']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erreur lors de l\'ajout']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Erreur : ' . $e->getMessage()]);
    }
    exit;
}

// Modifier un utilisateur existant
if ($action === 'modifier_utilisateur') {
    $id = intval($_POST['id'] ?? 0);
    $login = trim($_POST['login'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $niveau = trim($_POST['niveau'] ?? '');

    if ($id <= 0 || empty($login) || empty($niveau)) {
        echo json_encode(['status' => 'error', 'message' => 'Champs obligatoires manquants']);
        exit;
    }

    try {
        // Vérification de l'unicité du login (sauf pour l'utilisateur actuel)
        $stmt = $db->prepare("SELECT id_dossier FROM utilisateurs WHERE id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $user = $result->fetchArray(SQLITE3_ASSOC);
        
        if (!$user) {
            echo json_encode(['status' => 'error', 'message' => 'Utilisateur introuvable']);
            exit;
        }

        $stmt = $db->prepare("SELECT COUNT(*) as count FROM utilisateurs WHERE login = :login AND id_dossier = :id_dossier AND id != :id");
        $stmt->bindValue(':login', $login, SQLITE3_TEXT);
        $stmt->bindValue(':id_dossier', $user['id_dossier'], SQLITE3_INTEGER);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $exists = $result->fetchArray(SQLITE3_ASSOC)['count'];

        if ($exists > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Ce login est déjà utilisé pour ce dossier']);
            exit;
        }

        // Mise à jour
        if (!empty($password)) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE utilisateurs SET login = :login, mot_de_passe = :mot_de_passe, niveau = :niveau WHERE id = :id");
            $stmt->bindValue(':mot_de_passe', $passwordHash, SQLITE3_TEXT);
        } else {
            $stmt = $db->prepare("UPDATE utilisateurs SET login = :login, niveau = :niveau WHERE id = :id");
        }
        
        $stmt->bindValue(':login', $login, SQLITE3_TEXT);
        $stmt->bindValue(':niveau', $niveau, SQLITE3_TEXT);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Utilisateur modifié avec succès']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la modification']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Erreur : ' . $e->getMessage()]);
    }
    exit;
}

// Supprimer un utilisateur
if ($action === 'supprimer_utilisateur') {
    $id = intval($_POST['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID utilisateur invalide']);
        exit;
    }

    try {
        // Empêcher la suppression de l'utilisateur actuel
        if (isset($_SESSION['user_id']) && $id == $_SESSION['user_id']) {
            echo json_encode(['status' => 'error', 'message' => 'Vous ne pouvez pas supprimer votre propre compte']);
            exit;
        }

        $stmt = $db->prepare("DELETE FROM utilisateurs WHERE id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Utilisateur supprimé avec succès']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la suppression']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Erreur : ' . $e->getMessage()]);
    }
    exit;
}

// Action non reconnue
echo json_encode(['status' => 'error', 'message' => 'Action non reconnue']);
?>