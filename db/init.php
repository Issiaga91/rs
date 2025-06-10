<?php
echo "<h1>Début du script d'installation...</h1>";

// Activation des erreurs pour être sûr de tout voir
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Le chemin du répertoire 'db' est le répertoire où se trouve ce script
$dbDir = __DIR__;
$dbPath = $dbDir . '/database.sqlite';

echo "<p>Le script va essayer de créer/ouvrir la base de données à cet emplacement :</p>";
echo "<p><strong>" . htmlspecialchars($dbPath) . "</strong></p><hr>";

try {
    // Connexion
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("PRAGMA foreign_keys = ON");

    echo "<p style='color:green;'>1. Connexion à la base de données réussie.</p>";
    echo "<p>2. Tentative de création des tables...</p>";

    // Création des tables
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS dossiers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        raison_sociale TEXT NOT NULL UNIQUE,
        siren TEXT UNIQUE
    )");
    echo " - Table 'dossiers' vérifiée/créée.<br>";

    $pdo->exec("
    CREATE TABLE IF NOT EXISTS utilisateurs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        login TEXT NOT NULL UNIQUE,
        email TEXT NOT NULL UNIQUE,
        mot_de_passe TEXT,
        niveau TEXT NOT NULL DEFAULT 'avance'
    )");
    echo " - Table 'utilisateurs' vérifiée/créée.<br>";

    $pdo->exec("
    CREATE TABLE IF NOT EXISTS dossier_utilisateur (
        id_utilisateur INTEGER NOT NULL,
        id_dossier INTEGER NOT NULL,
        PRIMARY KEY (id_utilisateur, id_dossier),
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
        FOREIGN KEY (id_dossier) REFERENCES dossiers(id) ON DELETE CASCADE
    )");
    echo " - Table 'dossier_utilisateur' vérifiée/créée.<br>";

    $pdo->exec("
    CREATE TABLE IF NOT EXISTS journaux_comptables (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        code TEXT NOT NULL UNIQUE,
        libelle TEXT NOT NULL,
        id_dossier INTEGER NOT NULL,
        FOREIGN KEY (id_dossier) REFERENCES dossiers(id) ON DELETE CASCADE
    )");
    echo " - Table 'journaux_comptables' vérifiée/créée.<br>";

    echo "<p>3. Vérification de l'utilisateur 'issiaga'...</p>";
    $count = $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE login = 'issiaga'")->fetchColumn();
    if ($count == 0) {
        $hash = password_hash("famille", PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO utilisateurs (login, email, mot_de_passe, niveau) VALUES (?, ?, ?, ?)");
        $stmt->execute(['issiaga', 'admin@example.com', $hash, 'avance']);
        echo "<p style='color:blue;'>   -> Utilisateur 'issiaga' a été créé.</p>";
    } else {
        echo "<p style='color:orange;'>   -> Utilisateur 'issiaga' existe déjà.</p>";
    }

    echo "<hr><h2>✅ Installation terminée avec succès !</h2>";

} catch (PDOException $e) {
    echo "<hr><h2 style='color:red;'>❌ Une erreur fatale est survenue lors de l'installation :</h2>";
    echo "<pre style='background-color:#f1f1f1; border:1px solid red; padding:10px;'>" . htmlspecialchars($e->getMessage()) . "</pre>";
}
?>