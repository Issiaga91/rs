<?php
session_start();

require_once __DIR__ . '/db_init.php'; // Appelle l'init base

// Connexion à la base
try {
    $dbPath = __DIR__ . '/db/database.sqlite';
    if (!file_exists($dbPath)) {
        require_once __DIR__ . '/db_init.php';
    }

    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérification de la table utilisateurs
    $tableExists = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='utilisateurs'")->fetchColumn();
    if (!$tableExists) {
        require_once __DIR__ . '/db_init.php';
    }

} catch (PDOException $e) {
    die("Erreur de base de données: " . $e->getMessage());
}

// Traitement du formulaire
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE login = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['mot_de_passe'])) {
        $_SESSION['user'] = $user['login'];
        $_SESSION['niveau'] = $user['niveau'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Identifiant ou mot de passe incorrect";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded shadow-md w-full max-w-sm">
        <h1 class="text-xl font-semibold mb-4 text-center">Connexion</h1>

        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 p-2 rounded mb-4 text-sm">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-4">
                <label class="block mb-1 text-sm">Identifiant</label>
                <input type="text" name="username" required 
                       class="w-full p-2 border rounded">
            </div>
            <div class="mb-6">
                <label class="block mb-1 text-sm">Mot de passe</label>
                <input type="password" name="password" required 
                       class="w-full p-2 border rounded">
            </div>
            <button type="submit" 
                    class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                Se connecter
            </button>
        </form>
    </div>
</body>
</html>
