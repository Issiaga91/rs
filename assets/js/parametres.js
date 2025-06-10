document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.tab-button');
    const contentContainer = document.getElementById('contenu-parametres');

    function switchTab(tabButton) {
        tabs.forEach(btn => btn.classList.remove('border-blue-500', 'text-blue-600', 'font-semibold'));
        tabButton.classList.add('border-blue-500', 'text-blue-600', 'font-semibold');
        const tabName = tabButton.dataset.tab;
        contentContainer.innerHTML = `<p class="text-center text-gray-500 p-8">Chargement...</p>`;
        
        fetch(`modules/${tabName}/tableau.php`)
            .then(response => response.ok ? response.text() : Promise.reject('Fichier introuvable'))
            .then(html => {
                contentContainer.innerHTML = html;
                loadModuleScript(`modules/${tabName}/script.js`, tabName);
            })
            .catch(error => {
                contentContainer.innerHTML = `<p class="text-center text-red-500 p-8">${error}</p>`;
            });
    }

    function loadModuleScript(src, tabName) {
        const oldScript = document.getElementById('module-script');
        if (oldScript) oldScript.remove();
      
        const script = document.createElement('script');
        script.id = 'module-script';
        script.src = src;
        
        script.onload = () => {
            // -- ESPIONS --
            console.log(`[parametres.js] Le script ${src} a été chargé avec succès.`);
            if (tabName === 'dossiers' && typeof initialiserModuleDossiers === 'function') {
                console.log("[parametres.js] La fonction 'initialiserModuleDossiers' existe. Tentative d'appel...");
                initialiserModuleDossiers();
                console.log("[parametres.js] Appel à 'initialiserModuleDossiers' terminé.");
            } else {
                 console.log("[parametres.js] La fonction 'initialiserModuleDossiers' n'a pas été trouvée ou l'onglet n'est pas 'dossiers'.");
            }
        };
        document.body.appendChild(script);
    }

    tabs.forEach(button => button.addEventListener('click', () => switchTab(button)));
    if (tabs.length > 0) switchTab(tabs[0]);
});