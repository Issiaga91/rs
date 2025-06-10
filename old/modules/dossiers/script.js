let idDossierASupprimer = null;

function ouvrirModalAjoutDossier() {
  document.getElementById('modal-titre').innerText = "Ajouter un dossier";
  document.getElementById('dossier-id').value = "";
  document.getElementById('raison_sociale').value = "";
  document.getElementById('siren').value = "";
  document.getElementById('modal-dossier').classList.remove('hidden');
}

function openModalModification(id) {
  fetch('modules/dossiers/get_dossier.php?id=' + id)
    .then(res => res.json())
    .then(data => {
      document.getElementById('modal-titre').innerText = "Modifier le dossier";
      document.getElementById('dossier-id').value = data.id;
      document.getElementById('raison_sociale').value = data.raison_sociale;
      document.getElementById('siren').value = data.siren;
      document.getElementById('modal-dossier').classList.remove('hidden');
    });
}

function fermerModalDossier() {
  document.getElementById('modal-dossier').classList.add('hidden');
}

function supprimerDossier(id) {
  idDossierASupprimer = id;
  document.getElementById('modal-suppression').classList.remove('hidden');
}

function fermerModalSuppression() {
  document.getElementById('modal-suppression').classList.add('hidden');
  idDossierASupprimer = null;
}

function initialiserModalSuppression() {
  const boutonConfirmer = document.getElementById('btn-confirmer-suppression');
  if (boutonConfirmer) {
    // Clone et remplace le bouton pour éviter les doublons d'écouteurs
    const newBtn = boutonConfirmer.cloneNode(true);
    boutonConfirmer.parentNode.replaceChild(newBtn, boutonConfirmer);
    
    newBtn.addEventListener('click', () => {
      if (!idDossierASupprimer) return;

      fetch('modules/dossiers/supprimer_dossier.php?id=' + idDossierASupprimer)
        .then(response => response.json())
        .then(data => {
          fermerModalSuppression();
          if (data.success) {
            showToast("Dossier supprimé ✅");
            chargerDossiers();
          } else {
            showToast(data.message || "Erreur lors de la suppression", "bg-red-500");
          }
        })
        .catch(() => {
          fermerModalSuppression();
          showToast("Erreur réseau lors de la suppression", "bg-red-500");
        });
    });
  }
}

function initialiserFormulaireDossier() {
  const form = document.getElementById('form-dossier');
  if (form) {
    if (form.dataset.initialised === "true") return;
    form.dataset.initialised = "true";

    form.addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      fetch('modules/dossiers/sauvegarder_dossier.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          fermerModalDossier();
          showToast("Dossier enregistré avec succès ✅");
          chargerDossiers();
        } else {
          showToast(data.message || "Erreur inconnue ❌", "bg-red-500");
        }
      })
      .catch(() => showToast("Erreur réseau ❌", "bg-red-500"));
    });
  }
}

function chargerDossiers() {
  fetch('modules/dossiers/tableau.php')
    .then(res => res.text())
    .then(html => {
      const container = document.getElementById('contenu-parametres');
      if (container) {
        container.innerHTML = html;
      }

      fetch('modules/dossiers/modals.php')
        .then(res => res.text())
        .then(modalHtml => {
          container.innerHTML += modalHtml;
          setTimeout(() => {
            initialiserFormulaireDossier();
            reinitialiserEvenementsBoutons();
            initialiserModalSuppression(); // Cette ligne doit être présente
          }, 10);
        });
    });
}

function reinitialiserEvenementsBoutons() {
  const boutonAjout = document.getElementById('btn-ajouter-dossier');
  if (boutonAjout) {
    const newBtn = boutonAjout.cloneNode(true);
    boutonAjout.parentNode.replaceChild(newBtn, boutonAjout);
    newBtn.addEventListener('click', ouvrirModalAjoutDossier);
  }

  const inputRecherche = document.getElementById('rechercheDossier');
  if (inputRecherche) {
    const newInput = inputRecherche.cloneNode(true);
    inputRecherche.parentNode.replaceChild(newInput, inputRecherche);
    newInput.addEventListener('input', function () {
      const recherche = this.value.trim();
      fetch(`modules/dossiers/tableau.php?recherche=${encodeURIComponent(recherche)}`)
        .then(r => r.text())
        .then(html => {
          const container = document.getElementById('contenu-parametres');
          container.innerHTML = html;
          fetch('modules/dossiers/modals.php')
            .then(res => res.text())
            .then(modalHtml => {
              container.innerHTML += modalHtml;
              setTimeout(() => {
                initialiserFormulaireDossier();
                reinitialiserEvenementsBoutons();
                initialiserModalSuppression();
              }, 10);
            });
        });
    });
  }
}
