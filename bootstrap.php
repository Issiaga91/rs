<?php
// Fichier : bootstrap.php

// 1. Démarrer la session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. Connexion à la base de données
require_once __DIR__ . '/includes/db.php';


// 3. VÉRIFICATION ET INSTALLATION AUTOMATIQUE
try {
    // On fait une requête très rapide pour voir si la table 'utilisateurs' existe.
    $pdo->query("SELECT 1 FROM utilisateurs LIMIT 1");
} catch (PDOException $e) {
    // Si la requête échoue, la table n'existe pas. La BDD n'est pas initialisée.
    
    // 1. On lance le script d'installation en silence.
    require_once __DIR__ . '/db/init.php';
    
    // 2. Maintenant que la base est créée, on redirige l'utilisateur vers la page de connexion.
    header('Location: login.php');
    exit;
}

// 4. Vérification de l'authentification (si on arrive ici, la BDD est installée)
require_once __DIR__ . '/includes/auth.php';
?>