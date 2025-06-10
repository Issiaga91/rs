<?php
// Activation du débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclusion de la configuration de la base de données
require_once __DIR__ . '/../db/init.php';

header('Content-Type: application/json');

// Journalisation des requêtes (debug)
file_put_contents(__DIR__ . '/../../api_debug.log', date('Y-m-d H:i:s') . " - " . print_r($_REQUEST, true) . "\n", FILE_APPEND);

$action = $_GET['action'] ?? '';

if ($action === 'save') {
    $id = $_POST['id'] ?? null;
    $login = trim($_POST['login'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $motDePasse = $_POST['mot_de_passe'] ?? '';
    $dossiers = $_POST['dossiers'] ?? [];

    // Validation des données
    if (empty($login) {
        echo json_encode(['success' => false, 'message' => 'Le nom d\'utilisateur est requis.']);
        exit;
    }

    if (empty($email) {
        echo json_encode(['success' => false, 'message' => 'L\'email est requis.']);
        exit;
    }

    try {
        if (empty($id)) {
            // Mode création - vérifier si le login existe déjà
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE login = ?");
            $stmt->execute([$login]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Ce nom d\'utilisateur est déjà utilisé.']);
                exit;
            }

            // Vérifier si l'email existe déjà
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé.']);
                exit;
            }

            if (empty($motDePasse)) {
                echo json_encode(['success' => false, 'message' => 'Un mot de passe est requis pour la création.']);
                exit;
            }

            $hash = password_hash($motDePasse, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO utilisateurs (login, email, mot_de_passe) VALUES (?, ?, ?)");
            $stmt->execute([$login, $email, $hash]);
            $id = $pdo->lastInsertId();
        } else {
            // Mode modification - vérifier les conflits
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE login = ? AND id != ?");
            $stmt->execute([$login, $id]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Ce nom d\'utilisateur est déjà utilisé.']);
                exit;
            }

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE email = ? AND id != ?");
            $stmt->execute([$email, $id]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé.']);
                exit;
            }

            // Mise à jour
            if (!empty($motDePasse)) {
                $hash = password_hash($motDePasse, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE utilisateurs SET login = ?, email = ?, mot_de_passe = ? WHERE id = ?");
                $stmt->execute([$login, $email, $hash, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE utilisateurs SET login = ?, email = ? WHERE id = ?");
                $stmt->execute([$login, $email, $id]);
            }
        }

        // Gestion des dossiers associés
        if (!empty($id)) {
            // Supprimer les associations existantes
            $pdo->prepare("DELETE FROM dossier_utilisateur WHERE id_utilisateur = ?")->execute([$id]);
            
            // Ajouter les nouvelles associations
            if (!empty($dossiers)) {
                $stmt = $pdo->prepare("INSERT INTO dossier_utilisateur (id_utilisateur, id_dossier) VALUES (?, ?)");
                foreach ($dossiers as $dossierId) {
                    if (is_numeric($dossierId)) {
                        $stmt->execute([$id, $dossierId]);
                    }
                }
            }
        }

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        $errorMessage = 'Erreur lors de l\'enregistrement';
        if ($e->getCode() == 23000) {
            $errorMessage = 'Une valeur existe déjà en double (login ou email)';
        }
        echo json_encode(['success' => false, 'message' => $errorMessage]);
    }
    exit;
}

elseif ($action === 'get') {
    $id = $_GET['id'] ?? null;
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'ID utilisateur manquant']);
        exit;
    }

    try {
        // Récupération de l'utilisateur
        $stmt = $pdo->prepare("SELECT id, login, email FROM utilisateurs WHERE id = ?");
        $stmt->execute([$id]);
        $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$utilisateur) {
            echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé']);
            exit;
        }

        // Récupération des dossiers associés
        $stmt = $pdo->prepare("SELECT id_dossier FROM dossier_utilisateur WHERE id_utilisateur = ?");
        $stmt->execute([$id]);
        $dossiers = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $utilisateur['dossiers'] = $dossiers;
        echo json_encode($utilisateur);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération']);
    }
    exit;
}

elseif ($action === 'delete') {
    $id = $_GET['id'] ?? null;
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'ID utilisateur manquant']);
        exit;
    }

    try {
        $pdo->beginTransaction();
        
        // Supprimer d'abord les associations
        $pdo->prepare("DELETE FROM dossier_utilisateur WHERE id_utilisateur = ?")->execute([$id]);
        
        // Puis supprimer l'utilisateur
        $pdo->prepare("DELETE FROM utilisateurs WHERE id = ?")->execute([$id]);
        
        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Action non reconnue']);