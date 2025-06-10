<?php
// api/set_dossier_session.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié.']);
    exit;
}

$id_dossier = $_POST['id_dossier'] ?? null;

if (!$id_dossier) {
    echo json_encode(['success' => false, 'message' => 'ID dossier manquant.']);
    exit;
}

// Optionnel: Vérifier que l'utilisateur a bien accès à ce dossier
// Vous pouvez ajouter une vérification ici en interrogeant la table dossier_utilisateur
// pour vous assurer que l'id_dossier fourni appartient bien à l'utilisateur_id en session.
// Pour l'instant, nous faisons confiance au sélecteur de la sidebar.

$_SESSION['id_dossier'] = (int)$id_dossier;

echo json_encode(['success' => true, 'message' => 'Dossier mis à jour en session.']);
?>