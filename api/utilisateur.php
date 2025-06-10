<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db_init.php';
session_start();

$action = $_GET['action'] ?? $_POST['action'] ?? null;

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié.']);
    exit;
}

$user_level = $_SESSION['niveau'] ?? 'basique';

switch ($action) {
    case 'save':
        if ($user_level !== 'avance') {
            echo json_encode(['success' => false, 'message' => 'Vous n\'avez pas les droits pour modifier/ajouter des utilisateurs.']);
            exit;
        }

        $id = $_POST['id'] ?? null;
        $login = trim($_POST['login'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $niveau = $_POST['niveau'] ?? 'basique';
        $dossiers = $_POST['dossiers'] ?? [];
        $mot_de_passe = $_POST['mot_de_passe'] ?? ''; // Récupérer le mot de passe (peut être vide)

        if (!$login || !$email) {
            echo json_encode(['success' => false, 'message' => 'Login et email requis']);
            exit;
        }

        try {
            if ($id) {
                // Modification d'un utilisateur existant
                $sql = "UPDATE utilisateurs SET login = ?, email = ?, niveau = ?";
                $params = [$login, $email, $niveau];

                // Si un nouveau mot de passe est fourni, le hasher et l'ajouter à la requête
                if (!empty($mot_de_passe)) {
                    $hashed_password = password_hash($mot_de_passe, PASSWORD_DEFAULT);
                    $sql .= ", mot_de_passe = ?";
                    $params[] = $hashed_password;
                }

                $sql .= " WHERE id = ?";
                $params[] = $id;

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

                // Gérer les dossiers associés
                $pdo->prepare("DELETE FROM dossier_utilisateur WHERE id_utilisateur = ?")->execute([$id]);
                foreach ($dossiers as $d) {
                    if (is_numeric($d)) {
                        $stmt = $pdo->prepare("INSERT INTO dossier_utilisateur (id_utilisateur, id_dossier) VALUES (?, ?)");
                        $stmt->execute([$id, $d]);
                    }
                }
            } else {
                // Ajout d'un nouvel utilisateur
                // Si le mot de passe est vide, on laisse NULL dans la base.
                // Sinon, on le hashe.
                $hashed_password = !empty($mot_de_passe) ? password_hash($mot_de_passe, PASSWORD_DEFAULT) : NULL;

                $stmt = $pdo->prepare("INSERT INTO utilisateurs (login, email, mot_de_passe, niveau) VALUES (?, ?, ?, ?)");
                $stmt->execute([$login, $email, $hashed_password, $niveau]);
                $id = $pdo->lastInsertId();

                // Gérer les dossiers associés pour le nouvel utilisateur
                foreach ($dossiers as $d) {
                    if (is_numeric($d)) {
                        $stmt = $pdo->prepare("INSERT INTO dossier_utilisateur (id_utilisateur, id_dossier) VALUES (?, ?)");
                        $stmt->execute([$id, $d]);
                    }
                }
            }
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
        }
        break;

    case 'get':
        // ... (pas de changement ici, le mot de passe ne doit pas être récupéré pour des raisons de sécurité)
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID manquant']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT id, login, email, niveau FROM utilisateurs WHERE id = ?"); // Ne pas sélectionner le mot de passe
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT id_dossier FROM dossier_utilisateur WHERE id_utilisateur = ?");
        $stmt->execute([$id]);
        $user['dossiers'] = $stmt->fetchAll(PDO::FETCH_COLUMN);

        echo json_encode($user);
        break;

    case 'delete':
        // ... (pas de changement ici)
        if ($user_level !== 'avance') {
            echo json_encode(['success' => false, 'message' => 'Vous n\'avez pas les droits pour supprimer des utilisateurs.']);
            exit;
        }

        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID manquant']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT login FROM utilisateurs WHERE id = ?");
        $stmt->execute([$id]);
        $user_to_delete = $stmt->fetchColumn();

        if ($user_to_delete === 'issiaga') {
            echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas supprimer l\'utilisateur "issiaga".']);
            exit;
        }
        if ($id == ($_SESSION['user_id'] ?? null)) {
            echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas supprimer votre propre compte.']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM dossier_utilisateur WHERE id_utilisateur = ?");
        $stmt->execute([$id]); // Supprimer les liens dossier_utilisateur d'abord

        $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Action invalide']);
        break;
}