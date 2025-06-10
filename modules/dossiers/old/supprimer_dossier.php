<?php
header('Content-Type: application/json');

try {
    include_once __DIR__ . '/../../db_init.php';

    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        echo json_encode(['success' => false, 'message' => 'ID invalide.']);
        exit;
    }

    $id = (int) $_GET['id'];

    $stmt = $pdo->prepare("DELETE FROM dossiers WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur : ' . $e->getMessage()]);
}
