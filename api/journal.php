<?php
// api/journal.php
header('Content-Type: application/json');
require_once __DIR__ . '/../db_init.php';
session_start();

$action = $_GET['action'] ?? $_POST['action'] ?? null;

// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) { // Utilisez user_id pour plus de robustesse
    echo json_encode(['success' => false, 'message' => 'Non authentifié.']);
    exit;
}

$user_level = $_SESSION['niveau'] ?? 'basique';
$id_dossier_actif = $_SESSION['id_dossier'] ?? null;

// Vérifier si un dossier est sélectionné pour les opérations nécessitant un contexte dossier
if (!$id_dossier_actif && $action !== 'get' && $action !== 'delete') { // 'get' et 'delete' peuvent avoir un ID direct, mais pour 'save' il faut un dossier actif
    echo json_encode(['success' => false, 'message' => 'Aucun dossier sélectionné. Veuillez choisir un dossier.']);
    exit;
}


switch ($action) {
    case 'save':
        if ($user_level !== 'avance') {
            echo json_encode(['success' => false, 'message' => 'Vous n\'avez pas les droits pour modifier/ajouter des journaux.']);
            exit;
        }

        $id = $_POST['id'] ?? null;
        $code = trim($_POST['code'] ?? '');
        $libelle = trim($_POST['libelle'] ?? '');

        if (!$code || !$libelle) {
            echo json_encode(['success' => false, 'message' => 'Code et libellé requis.']);
            exit;
        }

        try {
            if ($id) {
                // Modification: Assurez-vous que le journal appartient bien au dossier actif
                $stmt = $pdo->prepare("UPDATE journaux_comptables SET code = ?, libelle = ? WHERE id = ? AND id_dossier = ?");
                $stmt->execute([$code, $libelle, $id, $id_dossier_actif]);
            } else {
                // Ajout
                $stmt = $pdo->prepare("INSERT INTO journaux_comptables (code, libelle, id_dossier) VALUES (?, ?, ?)");
                $stmt->execute([$code, $libelle, $id_dossier_actif]);
            }
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') { // Code d'erreur pour les violations d'unicité (SQLITE_CONSTRAINT)
                echo json_encode(['success' => false, 'message' => 'Erreur : Le code du journal existe déjà pour ce dossier.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement : ' . $e->getMessage()]);
            }
        }
        break;

    case 'get':
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID manquant.']);
            exit;
        }

        // Récupérer le journal en s'assurant qu'il appartient au dossier actif
        $stmt = $pdo->prepare("SELECT id, code, libelle FROM journaux_comptables WHERE id = ? AND id_dossier = ?");
        $stmt->execute([$id, $id_dossier_actif]);
        $journal = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($journal) {
            echo json_encode($journal);
        } else {
            echo json_encode(['success' => false, 'message' => 'Journal non trouvé ou non accessible dans ce dossier.']);
        }
        break;

    case 'delete':
        if ($user_level !== 'avance') {
            echo json_encode(['success' => false, 'message' => 'Vous n\'avez pas les droits pour supprimer des journaux.']);
            exit;
        }

        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID manquant.']);
            exit;
        }

        try {
            // Supprimer le journal en s'assurant qu'il appartient au dossier actif
            $stmt = $pdo->prepare("DELETE FROM journaux_comptables WHERE id = ? AND id_dossier = ?");
            $stmt->execute([$id, $id_dossier_actif]);
            if ($stmt->rowCount() > 0) {
                 echo json_encode(['success' => true]);
            } else {
                 echo json_encode(['success' => false, 'message' => 'Journal non trouvé ou non accessible pour la suppression.']);
            }

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression : ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Action invalide.']);
        break;
}