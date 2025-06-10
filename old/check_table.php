<?php
$db = new SQLite3('db/database.sqlite');

// Afficher les colonnes de la table utilisateurs
$result = $db->query("PRAGMA table_info(utilisateurs)");

echo "<h2>Structure de la table utilisateurs :</h2><ul>";
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    echo "<li>{$row['name']} ({$row['type']}) - NOT NULL: {$row['notnull']}, DEFAULT: {$row['dflt_value']}</li>";
}
echo "</ul>";

// Vérifier les enregistrements
$res = $db->query("SELECT id, login, email, mot_de_passe FROM utilisateurs");
echo "<h2>Données actuelles :</h2><ul>";
while ($r = $res->fetchArray(SQLITE3_ASSOC)) {
    echo "<li>{$r['id']} | {$r['login']} | {$r['email']} | ".substr($r['mot_de_passe'], 0, 20)."...</li>";
}
echo "</ul>";
