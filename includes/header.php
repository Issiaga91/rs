<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Tableau de bord</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body class="bg-gray-50 font-sans">
    
<div id="toast-container" class="fixed bottom-4 right-4 flex flex-col items-end space-y-2 z-50"></div>
<script>
// Fonction TOAST globale, disponible pour tous les scripts
function showToast(message, isSuccess = true) {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    const bgColor = isSuccess ? 'bg-green-500' : 'bg-red-500';
    toast.className = `px-4 py-2 rounded shadow-md text-white ${bgColor} transform transition-all duration-300 ease-out`;
    toast.innerText = message;
    
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.addEventListener('transitionend', () => toast.remove());
    }, 3000);
}
</script>