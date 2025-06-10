<?php
header('Content-Type: application/json');

try {
    include_once __DIR__ . '/../../db_init.php';

    $id = isset($_POST['id']) ? trim($_POST['id']) : '';
    $raison = isset($_POST['raison_sociale']) ? trim($_POST['raison_sociale']) : '';
    $siren = isset($_POST['siren']) ? trim($_POST['siren']) : '';

    // Validation côté serveur
    if ($raison === '') {
        echo json_encode(['success' => false, 'message' => 'La raison sociale est obligatoire.']);
        exit;
    }

    if (!preg_match('/^[0-9]{9}$/', $siren)) {
        echo json_encode(['success' => false, 'message' => 'Le SIREN doit contenir exactement 9 chiffres.']);
        exit;
    }

    // Vérification doublon siren
    $query = "SELECT COUNT(*) FROM dossiers WHERE (raison_sociale = :raison OR siren = :siren)";
    $params = [':raison' => $raison, ':siren' => $siren];
    if ($id !== '') {
        $query .= " AND id != :id";
        $params[':id'] = $id;
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'Cette raison sociale ou ce SIREN existe déjà.']);
        exit;
    }

    // Insertion ou mise à jour
    if ($id === '') {
        $stmt = $pdo->prepare("INSERT INTO dossiers (raison_sociale, siren) VALUES (?, ?)");
        $stmt->execute([$raison, $siren]);
    } else {
        $stmt = $pdo->prepare("UPDATE dossiers SET raison_sociale = ?, siren = ? WHERE id = ?");
        $stmt->execute([$raison, $siren, $id]);
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur : ' . $e->getMessage()]);
}
