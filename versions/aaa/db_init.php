<?php
// Activation des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Création du répertoire db si inexistant
$dbDir = __DIR__ . '/db';
if (!file_exists($dbDir)) {
    mkdir($dbDir, 0755, true);
}

// Connexion à la base de données
$dbPath = $dbDir . '/database.sqlite';
$db = new PDO('sqlite:' . $dbPath);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec("PRAGMA foreign_keys = ON");

// Création des tables
$db->exec("
CREATE TABLE IF NOT EXISTS dossiers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    raison_sociale TEXT NOT NULL UNIQUE,
    siren TEXT UNIQUE
)");

$db->exec("
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    id_dossier INTEGER,
    login TEXT NOT NULL UNIQUE,
    mot_de_passe TEXT NOT NULL,
    niveau TEXT NOT NULL DEFAULT 'avance',
    FOREIGN KEY (id_dossier) REFERENCES dossiers(id) ON DELETE SET NULL
)");

// Création de l'utilisateur admin par défaut
$count = $db->query("SELECT COUNT(*) FROM utilisateurs WHERE login = 'issiaga'")->fetchColumn();
if ($count == 0) {
    $hash = password_hash("famille", PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO utilisateurs (login, mot_de_passe, niveau) VALUES (?, ?, ?)");
    $stmt->execute(['issiaga', $hash, 'avance']);
    echo "<p>Utilisateur admin 'issiaga' créé avec le mot de passe 'famille'</p>";
}

echo "";