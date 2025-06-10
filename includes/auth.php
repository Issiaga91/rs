<?php
// On retire : if (session_status() == PHP_SESSION_NONE) { session_start(); }
// Car bootstrap.php s'en occupe en amont.

// Vérification : l'utilisateur doit être connecté
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Le reste du fichier (Auto-forçage pour 'issiaga' et la fonction require_access_level) est parfait et ne change pas.
if ($_SESSION['user'] === 'issiaga') {
    $_SESSION['niveau'] = 'avance';
}

function require_access_level(array $required_levels, string $redirect_page = 'index.php') {
    $user_level = $_SESSION['niveau'] ?? 'basique';
    if (!in_array($user_level, $required_levels)) {
        header('Location: ' . $redirect_page);
        exit;
    }
}
?>