<?php
// Fichier : api/dossier.php
// Point d'entrée unique pour toutes les opérations sur les dossiers.

header('Content-Type: application/json');

// Assurez-vous que ces fichiers existent et que les chemins sont corrects.
// Le fichier db.php ne doit faire que la connexion PDO.
include_once __DIR__ . '/../includes/db.php';
include_once __DIR__ . '/../includes/auth.php';

// On récupère la méthode de la requête (GET, POST, DELETE, etc.)
$methode = $_SERVER['REQUEST_METHOD'];

// On vérifie les droits d'accès. La session doit être démarrée dans auth.php.
require_access_level(['avance', 'intermediaire']);

// On traite la requête en fonction de la méthode HTTP
switch ($methode) {
    case 'GET':
        // Si un 'id' est présent dans l'URL (ex: api/dossier.php?id=123), on récupère un seul dossier.
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM dossiers WHERE id = ?");
            $stmt->execute([ (int)$_GET['id'] ]);
            $dossier = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($dossier) {
                echo json_encode($dossier); // On renvoie juste l'objet dossier
            } else {
                http_response_code(404); // Not Found
                echo json_encode(['success' => false, 'message' => 'Dossier introuvable.']);
            }
        } else {
            // Sinon, on récupère la liste de tous les dossiers, avec une recherche possible.
            $recherche = isset($_GET['recherche']) ? trim($_GET['recherche']) : '';
            $sql = "SELECT id, raison_sociale, siren FROM dossiers";
            $params = [];
            if (!empty($recherche)) {
                $sql .= " WHERE raison_sociale LIKE :recherche OR siren LIKE :recherche";
                $params[':recherche'] = "%$recherche%";
            }
            $sql .= " ORDER BY raison_sociale ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $dossiers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'dossiers' => $dossiers]);
        }
        break;

    case 'POST':
        // Gère la création (pas d'ID) et la mise à jour (avec ID).
        $data = $_POST; // On utilise $_POST car le JS enverra du FormData

        $id = $data['id'] ?? null;
        $raison_sociale = trim($data['raison_sociale'] ?? '');
        $siren = trim($data['siren'] ?? '');

        if (empty($raison_sociale)) {
            http_response_code(400); // Bad Request
            echo json_encode(['success' => false, 'message' => 'La raison sociale est obligatoire.']);
            exit;
        }

        try {
            if ($id) {
                // Mise à jour d'un dossier existant
                $stmt = $pdo->prepare("UPDATE dossiers SET raison_sociale = ?, siren = ? WHERE id = ?");
                $stmt->execute([$raison_sociale, $siren, $id]);
                echo json_encode(['success' => true, 'message' => 'Dossier mis à jour.']);
            } else {
                // Création d'un nouveau dossier
                $stmt = $pdo->prepare("INSERT INTO dossiers (raison_sociale, siren) VALUES (?, ?)");
                $stmt->execute([$raison_sociale, $siren]);
                http_response_code(201); // Created
                echo json_encode(['success' => true, 'message' => 'Dossier créé.']);
            }
        } catch (PDOException $e) {
            // Gère les erreurs, notamment les doublons (raison sociale ou siren unique)
            http_response_code(409); // Conflict
            echo json_encode(['success' => false, 'message' => 'Cette raison sociale ou ce SIREN existe déjà.']);
        }
        break;

    case 'DELETE':
        // Gère la suppression d'un dossier via son ID dans l'URL.
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID du dossier manquant.']);
            exit;
        }
        $id = (int)$_GET['id'];
        $stmt = $pdo->prepare("DELETE FROM dossiers WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Dossier supprimé.']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Dossier non trouvé.']);
        }
        break;

    default:
        // Si une autre méthode HTTP est utilisée (PUT, PATCH, etc.)
        http_response_code(405); // Method Not Allowed
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
        break;
}
?>