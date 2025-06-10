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
$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec("PRAGMA foreign_keys = ON");

// Création des tables
$pdo->exec("
CREATE TABLE IF NOT EXISTS dossiers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    raison_sociale TEXT NOT NULL UNIQUE,
    siren TEXT UNIQUE
)");

$pdo->exec("
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    login TEXT NOT NULL UNIQUE,
    email TEXT NOT NULL UNIQUE,
    mot_de_passe TEXT,
    niveau TEXT NOT NULL DEFAULT 'avance'
)");

$pdo->exec("
CREATE TABLE IF NOT EXISTS dossier_utilisateur (
    id_utilisateur INTEGER NOT NULL,
    id_dossier INTEGER NOT NULL,
    PRIMARY KEY (id_utilisateur, id_dossier),
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (id_dossier) REFERENCES dossiers(id) ON DELETE CASCADE
)");

// Création de l'utilisateur admin par défaut
$count = $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE login = 'issiaga'")->fetchColumn();
if ($count == 0) {
    $hash = password_hash("famille", PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO utilisateurs (login, email, mot_de_passe, niveau) VALUES (?, ?, ?, ?)");
    $stmt->execute(['issiaga', 'admin@example.com', $hash, 'avance']);
    echo "<p>Utilisateur admin 'issiaga' créé avec le mot de passe 'famille'</p>";
}
echo "";
