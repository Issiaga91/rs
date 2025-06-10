function ouvrirModalAjoutUtilisateur() {
  document.getElementById('modal-utilisateur-titre').innerText = "Ajouter un utilisateur";
  document.getElementById('utilisateur-id').value = "";
  document.getElementById('login').value = "";
  document.getElementById('email').value = "";
  document.getElementById('mot_de_passe').value = "";
  document.getElementById('mot_de_passe').required = true;
  document.getElementById('mot_de_passe').placeholder = "";

  const select = document.getElementById('dossiers');
  [...select.options].forEach(o => o.selected = false);

  document.getElementById('modal-utilisateur').classList.remove('hidden');
}

function modifierUtilisateur(id) {
  fetch(`api/utilisateur.php?action=get&id=${id}`)
    .then(response => {
      if (!response.ok) throw new Error('Erreur réseau');
      return response.json();
    })
    .then(data => {
      if (!data.success && data.message) {
        throw new Error(data.message);
      }
      
      document.getElementById('modal-utilisateur-titre').innerText = "Modifier l'utilisateur";
      document.getElementById('utilisateur-id').value = data.id;
      document.getElementById('login').value = data.login;
      document.getElementById('email').value = data.email;
      document.getElementById('mot_de_passe').value = "";
      document.getElementById('mot_de_passe').required = false;
      document.getElementById('mot_de_passe').placeholder = "Laisser vide pour ne pas modifier";

      const select = document.getElementById('dossiers');
      [...select.options].forEach(o => {
        o.selected = data.dossiers.includes(parseInt(o.value));
      });

      document.getElementById('modal-utilisateur').classList.remove('hidden');
    })
    .catch(error => {
      console.error('Erreur:', error);
      showToast(error.message || "Erreur lors du chargement", "bg-red-500");
    });
}

function fermerModalUtilisateur() {
  document.getElementById('modal-utilisateur').classList.add('hidden');
}

function supprimerUtilisateur(id, login) {
  if (!confirm(`Confirmer la suppression de l'utilisateur "${login}" ?`)) return;

  fetch(`api/utilisateur.php?action=delete&id=${id}`)
    .then(response => {
      if (!response.ok) throw new Error('Erreur réseau');
      return response.json();
    })
    .then(data => {
      if (data.success) {
        showToast("Utilisateur supprimé avec succès ✅");
        chargerUtilisateurs();
      } else {
        throw new Error(data.message || "Erreur lors de la suppression");
      }
    })
    .catch(error => {
      console.error('Erreur:', error);
      showToast(error.message || "Erreur lors de la suppression", "bg-red-500");
    });
}

function initialiserFormulaireUtilisateur() {
  const form = document.getElementById('form-utilisateur');
  if (!form || form.dataset.initialised === "true") return;
  form.dataset.initialised = "true";

  form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="animate-spin">⏳</span> En cours...';

    const formData = new FormData(form);
    console.log('Données envoyées:', Object.fromEntries(formData.entries()));

    fetch('api/utilisateur.php?action=save', {
      method: 'POST',
      body: formData
    })
    .then(response => {
      if (!response.ok) throw response;
      return response.json();
    })
    .then(data => {
      if (data.success) {
        fermerModalUtilisateur();
        showToast("Utilisateur enregistré avec succès ✅");
        chargerUtilisateurs();
      } else {
        throw new Error(data.message || "Erreur lors de l'enregistrement");
      }
    })
    .catch(error => {
      error.json().then(err => {
        showToast(err.message || "Erreur lors de l'enregistrement", "bg-red-500");
      }).catch(() => {
        showToast(error.message || "Erreur lors de l'enregistrement", "bg-red-500");
      });
    })
    .finally(() => {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Enregistrer';
    });
  });
}

function reinitialiserEvenementsUtilisateurs() {
  const btnAjout = document.getElementById('btn-ajouter-utilisateur');
  if (btnAjout) {
    btnAjout.onclick = ouvrirModalAjoutUtilisateur;
  }

  // Réinitialiser les boutons de modification/suppression
  document.querySelectorAll('[onclick^="modifierUtilisateur"]').forEach(btn => {
    const match = btn.getAttribute('onclick').match(/modifierUtilisateur\((\d+)\)/);
    if (match) {
      btn.onclick = () => modifierUtilisateur(match[1]);
    }
  });

  document.querySelectorAll('[onclick^="supprimerUtilisateur"]').forEach(btn => {
    const match = btn.getAttribute('onclick').match(/supprimerUtilisateur\((\d+)\)/);
    const login = btn.closest('tr').querySelector('td:first-child').textContent;
    if (match) {
      btn.onclick = () => supprimerUtilisateur(match[1], login);
    }
  });
}

function chargerUtilisateurs() {
  const container = document.getElementById('contenu-parametres');
  container.innerHTML = '<div class="flex justify-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div></div>';

  Promise.all([
    fetch('modules/utilisateurs/tableau.php').then(r => r.text()),
    fetch('modules/utilisateurs/modals.php').then(r => r.text())
  ])
  .then(([tableHtml, modalsHtml]) => {
    container.innerHTML = tableHtml + modalsHtml;
    initialiserFormulaireUtilisateur();
    reinitialiserEvenementsUtilisateurs();
  })
  .catch(error => {
    console.error('Erreur:', error);
    container.innerHTML = '<div class="text-red-500 p-4">Erreur lors du chargement des utilisateurs</div>';
    showToast("Erreur lors du chargement", "bg-red-500");
  });
}