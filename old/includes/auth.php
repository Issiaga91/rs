<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérification : l'utilisateur doit être connecté
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Auto-forçage : si user == issiaga ➔ niveau avancé
if ($_SESSION['user'] === 'issiaga') {
    $_SESSION['niveau'] = 'avance';
}
?>
