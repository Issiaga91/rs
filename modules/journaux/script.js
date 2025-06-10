// modules/journaux/script.js

// Fonction pour afficher des toasts (assurez-vous qu'elle est globale)
function showToast(message, bgColorClass = "bg-green-500") {
    const toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        const div = document.createElement('div');
        div.id = 'toast-container';
        div.className = 'fixed bottom-4 right-4 flex flex-col items-end space-y-2 z-50';
        document.body.appendChild(div);
    }
    const toast = document.createElement('div');
    toast.className = `px-4 py-2 rounded shadow-md text-white ${bgColorClass}`;
    toast.innerText = message;
    document.getElementById('toast-container').appendChild(toast);
    setTimeout(() => {
        toast.remove();
    }, 3000);
}


function ouvrirModalAjoutJournal() {
    document.getElementById('modal-journal-titre').innerText = "Ajouter un journal";
    document.getElementById('journal-id').value = "";
    document.getElementById('code').value = "";
    document.getElementById('libelle').value = "";
    document.getElementById('modal-journal').classList.remove('hidden');
}

function modifierJournal(id) {
    fetch('api/journal.php?action=get&id=' + id)
        .then(res => res.json())
        .then(data => {
            if (!data.success && data.message) {
                showToast(data.message, "bg-red-500");
                return;
            }
            document.getElementById('modal-journal-titre').innerText = "Modifier le journal";
            document.getElementById('journal-id').value = data.id;
            document.getElementById('code').value = data.code;
            document.getElementById('libelle').value = data.libelle;
            document.getElementById('modal-journal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Erreur:', error);
            showToast("Erreur lors du chargement du journal ❌", "bg-red-500");
        });
}

function fermerModalJournal() {
    document.getElementById('modal-journal').classList.add('hidden');
    // Réinitialiser le formulaire
    document.getElementById('form-journal').reset();
}

function supprimerJournal(id) {
    if (!confirm("Confirmer la suppression de ce journal ?")) return;

    fetch('api/journal.php?action=delete&id=' + id)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast("Journal supprimé ✅");
                chargerJournaux(); // Recharger le tableau après suppression
            } else {
                showToast(data.message || "Erreur lors de la suppression ❌", "bg-red-500");
            }
        })
        .catch(() => showToast("Erreur réseau lors de la suppression ❌", "bg-red-500"));
}

function initialiserFormulaireJournal() {
    const form = document.getElementById('form-journal');
    if (!form || form.dataset.initialised === "true") return; // Empêche l'initialisation multiple
    form.dataset.initialised = "true"; // Marque le formulaire comme initialisé

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(form);
        // Pas besoin d'ajouter id_dossier ici, il sera automatiquement pris de la session côté serveur
        fetch('api/journal.php?action=save', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    fermerModalJournal();
                    showToast("Journal enregistré ✅");
                    chargerJournaux(); // Recharger le tableau après enregistrement
                } else {
                    showToast(data.message || "Erreur lors de l'enregistrement ❌", "bg-red-500");
                }
            })
            .catch(() => showToast("Erreur réseau lors de l'enregistrement ❌", "bg-red-500"));
    });
}

// Fonction pour recharger le tableau des journaux et réinitialiser les événements des boutons
function chargerJournaux() {
    fetch('modules/journaux/tableau.php')
        .then(r => r.text())
        .then(html => {
            const container = document.getElementById('contenu-parametres');
            container.innerHTML = html; // Remplace le tableau actuel

            // Recharger le modal après le tableau pour s'assurer qu'il est dans le DOM
            fetch('modules/journaux/modals.php')
                .then(m => m.text())
                .then(modalHtml => {
                    container.innerHTML += modalHtml; // Ajoute le modal au nouveau contenu

                    // Les initialisations doivent se faire APRÈS que le DOM est mis à jour
                    setTimeout(() => {
                        initialiserFormulaireJournal();
                        reinitialiserEvenementsJournaux();
                    }, 10);
                });
        })
        .catch(err => showToast("Erreur lors du chargement des journaux ❌", "bg-red-500"));
}

function reinitialiserEvenementsJournaux() {
    const btnAjout = document.getElementById('btn-ajouter-journal');
    if (btnAjout) {
        const newBtn = btnAjout.cloneNode(true);
        btnAjout.parentNode.replaceChild(newBtn, btnAjout);
        newBtn.addEventListener('click', ouvrirModalAjoutJournal);
    }
}

// Les fonctions initialiserFormulaireJournal et reinitialiserEvenementsJournaux
// seront appelées par le code JS dans parametres.php quand l'onglet "journaux" est activé.