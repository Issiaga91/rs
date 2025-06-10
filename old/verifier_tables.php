<?php
// verifier_tables.php - Version finale

// Configuration des erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Fonction de connexion sécurisée
function connectDB() {
    $dbPath = 'db/database.sqlite';
    if (!file_exists($dbPath)) {
        die("<p style='color:red;'>❌ Base introuvable : $dbPath</p>");
    }

    $db = new PDO("sqlite:$dbPath");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("PRAGMA foreign_keys = ON");
    
    return $db;
}

try {
    $db = connectDB();
    $foreignKeys = $db->query("PRAGMA foreign_keys")->fetchColumn();
    
    echo "<h2 style='color:" . ($foreignKeys ? 'green' : 'red') . "'>";
    echo "Clés étrangères: " . ($foreignKeys ? "✅ ACTIVÉES" : "❌ DÉSACTIVÉES");
    echo "</h2>";

    // Structure attendue simplifiée
    $tables = [
        'dossiers' => ['id', 'raison_sociale', 'siren'],
        'utilisateurs' => ['id', 'id_dossier', 'login', 'mot_de_passe', 'niveau']
    ];

    foreach ($tables as $table => $colonnes) {
        $exists = $db->query("SELECT 1 FROM sqlite_master WHERE type='table' AND name='$table'")->fetchColumn();
        echo "<h3>" . ($exists ? "✅" : "❌") . " Table $table</h3>";
        
        if ($exists) {
            $cols = $db->query("PRAGMA table_info($table)")->fetchAll(PDO::FETCH_ASSOC);
            $cols = array_column($cols, 'name');
            
            foreach ($colonnes as $col) {
                echo in_array($col, $cols) 
                    ? "<p style='color:green;'>✔ $col</p>" 
                    : "<p style='color:red;'>✖ $col (manquante)</p>";
            }
            
            // Vérification FK pour utilisateurs
            if ($table === 'utilisateurs') {
                $fk = $db->query("PRAGMA foreign_key_list($table)")->fetch();
                echo $fk && $fk['table'] === 'dossiers' && $fk['from'] === 'id_dossier'
                    ? "<p style='color:green;'>✔ Clé étrangère: id_dossier → dossiers(id)</p>"
                    : "<p style='color:red;'>✖ Clé étrangère manquante</p>";
            }
        }
    }

} catch (Exception $e) {
    echo "<p style='color:red;'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>