/**
 * Fichier : /modules/dossiers/script.js
 * Gère toute l'interactivité pour le module des dossiers.
 * Appelé par `parametres.js` qui exécute `initialiserModuleDossiers`.
 */

//---------------------------------------------------------------------
// VARIABLES GLOBALES ET CONSTANTES
//---------------------------------------------------------------------

const API_URL_DOSSIERS = 'api/dossier.php';
let idDossierASupprimer = null;


//---------------------------------------------------------------------
// FONCTIONS PRINCIPALES (Communication avec l'API)
//---------------------------------------------------------------------

/**
 * Charge la liste des dossiers depuis l'API et l'affiche dans le tableau.
 * @param {string} recherche - Terme de recherche optionnel.
 */
function chargerDossiers(recherche = '') {
    const url = recherche ? `${API_URL_DOSSIERS}?recherche=${encodeURIComponent(recherche)}` : API_URL_DOSSIERS;
    const tbody = document.getElementById('liste-dossiers');
    
    if (!tbody) return; // Sécurité si l'élément n'est pas trouvé
    tbody.innerHTML = '<tr><td colspan="3" class="p-4 text-center text-gray-500">Chargement...</td></tr>';

    fetch(url)
        .then(response => response.json())
        .then(data => {
            tbody.innerHTML = ''; // Vider le tableau
            if (data.success && data.dossiers.length > 0) {
                data.dossiers.forEach(dossier => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="px-4 py-2">${escapeHtml(dossier.raison_sociale)}</td>
                        <td class="px-4 py-2">${escapeHtml(dossier.siren)}</td>
                        <td class="px-4 py-2 text-center">
                            <button class="edit-dossier-btn text-orange-500 hover:text-orange-600 mr-2" data-id="${dossier.id}" title="Modifier">✏️</button>
                            <button class="delete-dossier-btn text-red-500 hover:text-red-600" data-id="${dossier.id}" title="Supprimer">🗑</button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="3" class="px-4 py-2 text-center text-gray-500">Aucun dossier trouvé.</td></tr>';
            }
        })
        .catch(error => {
            tbody.innerHTML = '<tr><td colspan="3" class="px-4 py-2 text-center text-red-500">Erreur de chargement des données.</td></tr>';
            showToast('Erreur critique lors du chargement des dossiers.', false);
        });
}

/**
 * Gère la soumission du formulaire pour créer ou modifier un dossier.
 * @param {Event} event - L'événement de soumission du formulaire.
 */
function handleDossierFormSubmit(event) {
    event.preventDefault();
    const formData = new FormData(event.target);

    fetch(API_URL_DOSSIERS, { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message || 'Opération réussie !');
                fermerModalDossier();
                chargerDossiers();
            } else {
                showToast(data.message || 'Une erreur est survenue.', false);
            }
        })
        .catch(error => showToast('Erreur réseau lors de la sauvegarde.', false));
}

/**
 * Ouvre la modale pour modifier un dossier en allant chercher ses données via l'API.
 * @param {number|string} id - L'ID du dossier à modifier.
 */
function openModalModificationDossier(id) {
    fetch(`${API_URL_DOSSIERS}?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data && data.id) {
                document.getElementById('modal-titre').innerText = "Modifier le dossier";
                document.getElementById('dossier-id').value = data.id;
                document.getElementById('raison_sociale').value = data.raison_sociale;
                document.getElementById('siren').value = data.siren;
                document.getElementById('modal-dossier').classList.remove('hidden');
            } else {
                showToast(data.message || 'Erreur de récupération du dossier.', false);
            }
        })
        .catch(error => showToast('Erreur réseau.', false));
}

/**
 * Appelle l'API pour supprimer le dossier sélectionné.
 */
function confirmerSuppressionDossier() {
    if (!idDossierASupprimer) return;

    fetch(`${API_URL_DOSSIERS}?id=${idDossierASupprimer}`, { method: 'DELETE' })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message || 'Dossier supprimé.');
                chargerDossiers();
            } else {
                showToast(data.message || 'Erreur de suppression.', false);
            }
        })
        .catch(error => showToast('Erreur réseau.', false))
        .finally(() => {
            fermerModalSuppression();
        });
}

//---------------------------------------------------------------------
// FONCTIONS D'AIDE (Gestion des Modales et Utilitaires)
//---------------------------------------------------------------------

function ouvrirModalAjoutDossier() {
    document.getElementById('form-dossier').reset();
    document.getElementById('modal-titre').innerText = "Ajouter un dossier";
    document.getElementById('dossier-id').value = '';
    document.getElementById('modal-dossier').classList.remove('hidden');
}

function fermerModalDossier() {
    document.getElementById('modal-dossier').classList.add('hidden');
}

function afficherModalSuppression(id) {
    idDossierASupprimer = id;
    document.getElementById('modal-suppression').classList.remove('hidden');
}

function fermerModalSuppression() {
    idDossierASupprimer = null;
    document.getElementById('modal-suppression').classList.add('hidden');
}

function escapeHtml(unsafe) {
    return unsafe ? unsafe.toString().replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;") : '';
}

//---------------------------------------------------------------------
// INITIALISATION DU MODULE
//---------------------------------------------------------------------

/**
 * Fonction principale d'initialisation du module.
 * Attache tous les écouteurs d'événements nécessaires.
 */
function initialiserModuleDossiers() {
    const contentArea = document.getElementById('contenu-parametres');
    if (!contentArea) return;

    // Attacher les écouteurs aux éléments statiques
    const btnAjout = document.getElementById('btn-ajouter-dossier');
    const inputRecherche = document.getElementById('rechercheDossier');

    if (btnAjout) btnAjout.addEventListener('click', ouvrirModalAjoutDossier);
    if (inputRecherche) inputRecherche.addEventListener('input', (e) => chargerDossiers(e.target.value));

    // Utiliser la délégation d'événements pour tous les autres boutons
    contentArea.addEventListener('click', function(event) {
        const button = event.target.closest('button');
        if (!button) return;

        // Boutons dans le tableau
        if (button.classList.contains('edit-dossier-btn')) openModalModificationDossier(button.dataset.id);
        if (button.classList.contains('delete-dossier-btn')) afficherModalSuppression(button.dataset.id);

        // Boutons dans la modale d'ajout/modification
        if (button.type === 'submit' && button.closest('form')?.id === 'form-dossier') {
            handleDossierFormSubmit(new SubmitEvent('submit', { submitter: button }));
        }
        if (button.getAttribute('onclick') === 'fermerModalDossier()') fermerModalDossier();
        
        // Boutons dans la modale de suppression
        if (button.getAttribute('onclick') === 'fermerModalSuppression()') fermerModalSuppression();
        if (button.getAttribute('onclick') === 'confirmerSuppressionDossier()') confirmerSuppressionDossier();
    });

    // Écouteur sur le formulaire lui-même
    const form = document.getElementById('form-dossier');
    if(form) form.addEventListener('submit', handleDossierFormSubmit);
    
    // Charger les données initiales du tableau
    chargerDossiers();
}