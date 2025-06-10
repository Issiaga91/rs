<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Vérification de la requête AJAX
if (!isset($_POST['ajax']) || $_POST['ajax'] != 1) {
    echo json_encode(['status' => 'error', 'message' => 'Requête non autorisée']);
    exit;
}

include "includes/auth.php";
$db = new SQLite3('db/database.sqlite');
$db->enableExceptions(true);
$db->exec('PRAGMA foreign_keys = ON');

$action = $_POST['action'] ?? '';

// Charger la liste des utilisateurs avec leurs dossiers
if ($action === 'charger') {
    try {
        $query = "
            SELECT u.id, u.login, u.niveau, 
                   GROUP_CONCAT(d.raison_sociale, ', ') AS dossiers
            FROM utilisateurs u
            LEFT JOIN utilisateurs_dossiers ud ON u.id = ud.id_utilisateur
            LEFT JOIN dossiers d ON ud.id_dossier = d.id
            GROUP BY u.id
            ORDER BY u.login ASC
        ";
        
        $result = $db->query($query);
        $html = '';
        
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $html .= '<tr class="border-b odd:bg-white even:bg-gray-100 hover:bg-blue-50">
                <td class="p-2">'.htmlspecialchars($row['login']).'</td>
                <td class="p-2">'.htmlspecialchars($row['niveau']).'</td>
                <td class="p-2">'.htmlspecialchars($row['dossiers'] ?: 'Aucun dossier').'</td>
                <td class="p-2 flex items-center space-x-2">
                    <button data-edit data-id="'.$row['id'].'" 
                            data-login="'.htmlspecialchars($row['login']).'" 
                            data-niveau="'.htmlspecialchars($row['niveau']).'" 
                            class="p-1 hover:bg-blue-100 hover:text-blue-600 rounded-full transition">✏️</button>
                    <button data-delete="'.$row['id'].'" 
                            class="p-1 hover:bg-red-100 hover:text-red-600 rounded-full transition">🗑️</button>
                </td>
            </tr>';
        }
        
        echo json_encode(['status' => 'success', 'html' => $html]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Erreur SQL : ' . $e->getMessage()]);
    }
    exit;
}

// Charger la liste des dossiers pour le select
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

// Charger les dossiers d'un utilisateur avec état de sélection
if ($action === 'charger_dossiers_utilisateur') {
    $id_utilisateur = intval($_POST['id_utilisateur'] ?? 0);
    
    try {
        // Tous les dossiers
        $result = $db->query("SELECT id, raison_sociale FROM dossiers ORDER BY raison_sociale ASC");
        $allDossiers = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $allDossiers[] = $row;
        }
        
        // Dossiers associés à l'utilisateur
        $stmt = $db->prepare("SELECT id_dossier FROM utilisateurs_dossiers WHERE id_utilisateur = ?");
        $stmt->bindValue(1, $id_utilisateur, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $userDossiers = [];
        while ($row = $result->fetchArray(SQLITE3_NUM)) {
            $userDossiers[] = $row[0];
        }
        
        // Marquer les dossiers sélectionnés
        $dossiers = array_map(function($d) use ($userDossiers) {
            $d['selected'] = in_array($d['id'], $userDossiers);
            return $d;
        }, $allDossiers);
        
        echo json_encode(['status' => 'success', 'dossiers' => $dossiers]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// Ajouter un utilisateur
if ($action === 'ajouter_utilisateur') {
    $login = trim($_POST['login'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $niveau = trim($_POST['niveau'] ?? '');
    $dossiers = $_POST['dossiers'] ?? [];
    
    try {
        // Validation
        if (empty($login) || empty($password) || empty($niveau) || empty($dossiers)) {
            throw new Exception('Tous les champs sont obligatoires');
        }

        // Vérification unicité login
        $stmt = $db->prepare("SELECT 1 FROM utilisateurs WHERE login = :login");
        $stmt->bindValue(':login', $login, SQLITE3_TEXT);
        if ($stmt->execute()->fetchArray()) {
            throw new Exception('Login déjà utilisé');
        }

        // Insertion utilisateur
        $stmt = $db->prepare("INSERT INTO utilisateurs (login, mot_de_passe, niveau) VALUES (:login, :password, :niveau)");
        $stmt->bindValue(':login', $login, SQLITE3_TEXT);
        $stmt->bindValue(':password', password_hash($password, PASSWORD_DEFAULT), SQLITE3_TEXT);
        $stmt->bindValue(':niveau', $niveau, SQLITE3_TEXT);
        $stmt->execute();
        $userId = $db->lastInsertRowID();

        // Insertion associations avec les dossiers
        foreach ($dossiers as $dossierId) {
            $stmt = $db->prepare("INSERT INTO utilisateurs_dossiers (id_utilisateur, id_dossier) VALUES (:user_id, :dossier_id)");
            $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
            $stmt->bindValue(':dossier_id', $dossierId, SQLITE3_INTEGER);
            $stmt->execute();
        }

        echo json_encode(['status' => 'success', 'message' => 'Utilisateur créé']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// Modifier un utilisateur
if ($action === 'modifier_utilisateur') {
    $id = intval($_POST['id'] ?? 0);
    $login = trim($_POST['login'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $niveau = trim($_POST['niveau'] ?? '');
    $dossiers = $_POST['dossiers'] ?? [];
    
    try {
        if ($id <= 0 || empty($login) || empty($niveau) || empty($dossiers)) {
            throw new Exception('Champs obligatoires manquants');
        }

        // Vérification unicité login
        $stmt = $db->prepare("SELECT 1 FROM utilisateurs WHERE login = :login AND id != :id");
        $stmt->bindValue(':login', $login, SQLITE3_TEXT);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        if ($stmt->execute()->fetchArray()) {
            throw new Exception('Login déjà utilisé');
        }

        // Mise à jour utilisateur
        if (!empty($password)) {
            $stmt = $db->prepare("UPDATE utilisateurs SET login = :login, mot_de_passe = :password, niveau = :niveau WHERE id = :id");
            $stmt->bindValue(':password', password_hash($password, PASSWORD_DEFAULT), SQLITE3_TEXT);
        } else {
            $stmt = $db->prepare("UPDATE utilisateurs SET login = :login, niveau = :niveau WHERE id = :id");
        }
        
        $stmt->bindValue(':login', $login, SQLITE3_TEXT);
        $stmt->bindValue(':niveau', $niveau, SQLITE3_TEXT);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->execute();

        // Mise à jour des associations
        // 1. Supprimer les anciennes associations
        $stmt = $db->prepare("DELETE FROM utilisateurs_dossiers WHERE id_utilisateur = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->execute();

        // 2. Ajouter les nouvelles associations
        foreach ($dossiers as $dossierId) {
            $stmt = $db->prepare("INSERT INTO utilisateurs_dossiers (id_utilisateur, id_dossier) VALUES (:user_id, :dossier_id)");
            $stmt->bindValue(':user_id', $id, SQLITE3_INTEGER);
            $stmt->bindValue(':dossier_id', $dossierId, SQLITE3_INTEGER);
            $stmt->execute();
        }
        
        echo json_encode(['status' => 'success', 'message' => 'Utilisateur mis à jour']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// Supprimer un utilisateur
if ($action === 'supprimer_utilisateur') {
    $id = intval($_POST['id'] ?? 0);
    
    try {
        if ($id <= 0) {
            throw new Exception('ID invalide');
        }

        // Empêcher auto-suppression
        if (isset($_SESSION['user_id']) && $id == $_SESSION['user_id']) {
            throw new Exception('Vous ne pouvez pas supprimer votre compte');
        }

        // Suppression en cascade
        // 1. D'abord les associations avec les dossiers
        $stmt = $db->prepare("DELETE FROM utilisateurs_dossiers WHERE id_utilisateur = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->execute();

        // 2. Puis l'utilisateur
        $stmt = $db->prepare("DELETE FROM utilisateurs WHERE id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        
        if ($stmt->execute()) {
            $changes = $db->changes();
            if ($changes > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Utilisateur supprimé']);
            } else {
                throw new Exception('Aucun utilisateur supprimé (ID inexistant?)');
            }
        } else {
            throw new Exception('Échec de la suppression');
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Action non reconnue']);
?>