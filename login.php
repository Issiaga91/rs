<?php
session_start(); // On démarre la session ici

// Si l'utilisateur est déjà connecté, on le redirige vers l'index
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

// On inclut SEULEMENT la connexion à la BDD
require_once __DIR__ . '/includes/db.php';

// Traitement du formulaire
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE login = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['mot_de_passe'])) {
            // L'utilisateur est authentifié, on peuple la session
            $_SESSION['user'] = $user['login'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['niveau'] = $user['niveau'] ?? 'basique';
            
            // On redirige vers la page d'accueil
            header("Location: index.php");
            exit;
        } else {
            $error = "Identifiant ou mot de passe incorrect.";
        }
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

        <form method="POST" action="login.php">
            <div class="mb-4">
                <label for="username" class="block mb-1 text-sm">Identifiant</label>
                <input type="text" id="username" name="username" required
                       class="w-full p-2 border rounded">
            </div>
            <div class="mb-6">
                <label for="password" class="block mb-1 text-sm">Mot de passe</label>
                <input type="password" id="password" name="password" required
                       class="w-full p-2 border rounded">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700">Se connecter</button>
        </form>
    </div>
</body>
</html>