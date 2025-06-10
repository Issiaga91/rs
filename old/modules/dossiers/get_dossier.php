<?php
if (session_status() == PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID manquant.']);
    exit;
}

include_once __DIR__ . '/../../db_init.php';

$id = (int) $_GET['id'];

$stmt = $pdo->prepare("SELECT id, raison_sociale, siren FROM dossiers WHERE id = ?");
$stmt->execute([$id]);
$dossier = $stmt->fetch(PDO::FETCH_ASSOC);

if ($dossier) {
    echo json_encode($dossier);
} else {
    echo json_encode(['success' => false, 'message' => 'Dossier introuvable.']);
}
