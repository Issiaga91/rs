<?php
// Fichier: includes/db.php
// Ce fichier gère UNIQUEMENT la connexion à la base de données.

try {
    // Le chemin __DIR__ pointe vers le dossier où se trouve ce fichier (includes)
    // On remonte donc d'un niveau (../) pour trouver le dossier 'db'
    $dbPath = __DIR__ . '/../db/database.sqlite'; // <== LE SLASH A ÉTÉ AJOUTÉ ICI
    
    // Connexion
    $pdo = new PDO('sqlite:' . $dbPath);
    
    // Configuration des options pour la gestion des erreurs et le format des résultats
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec("PRAGMA foreign_keys = ON;"); // Important pour l'intégrité des données

} catch (PDOException $e) {
    // En cas d'échec, on arrête le script avec un message clair.
    die("ERREUR CRITIQUE: Impossible de se connecter à la base de données. Message : " . $e->getMessage());
}
?>