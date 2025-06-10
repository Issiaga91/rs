function ouvrirModalAjoutUtilisateur() {
  document.getElementById('modal-utilisateur-titre').innerText = "Ajouter un utilisateur";
  document.getElementById('utilisateur-id').value = "";
  document.getElementById('login').value = "";
  document.getElementById('email').value = "";
  document.getElementById('niveau').value = "basique"; // Défaut à 'basique' pour les nouveaux utilisateurs
  const select = document.getElementById('dossiers');
  [...select.options].forEach(o => o.selected = false);
  document.getElementById('modal-utilisateur').classList.remove('hidden');
}

function modifierUtilisateur(id) {
  fetch('api/utilisateur.php?action=get&id=' + id)
    .then(res => res.json())
    .then(data => {
      if (!data.success && data.message) { // Gestion des erreurs si l'API renvoie un échec
        showToast(data.message, "bg-red-500");
        return;
      }
      document.getElementById('modal-utilisateur-titre').innerText = "Modifier l'utilisateur";
      document.getElementById('utilisateur-id').value = data.id;
      document.getElementById('login').value = data.login;
      document.getElementById('email').value = data.email;
      document.getElementById('niveau').value = data.niveau; // Définir le niveau de l'utilisateur à modifier

      const select = document.getElementById('dossiers');
      [...select.options].forEach(o => {
        o.selected = data.dossiers.includes(parseInt(o.value));
      });

      document.getElementById('modal-utilisateur').classList.remove('hidden');
    });
}

function fermerModalUtilisateur() {
  document.getElementById('modal-utilisateur').classList.add('hidden');
}

function supprimerUtilisateur(id) {
  if (!confirm("Confirmer la suppression de cet utilisateur ?")) return;

  fetch('api/utilisateur.php?action=delete&id=' + id)
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        showToast("Utilisateur supprimé ✅");
        chargerUtilisateurs();
      } else {
        showToast(data.message || "Erreur ❌", "bg-red-500");
      }
    })
    .catch(() => showToast("Erreur réseau ❌", "bg-red-500"));
}

function initialiserFormulaireUtilisateur() {
  const form = document.getElementById('form-utilisateur');
  // Vérifie si le formulaire est déjà initialisé pour éviter de multiples écouteurs
  if (!form || form.dataset.initialised === "true") return;
  form.dataset.initialised = "true"; // Marquer comme initialisé

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(form);
    fetch('api/utilisateur.php?action=save', {
      method: 'POST',
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          fermerModalUtilisateur();
          showToast("Utilisateur enregistré ✅");
          chargerUtilisateurs();
        } else {
          showToast(data.message || "Erreur ❌", "bg-red-500");
        }
      })
      .catch(() => showToast("Erreur réseau ❌", "bg-red-500"));
  });
}

function reinitialiserEvenementsUtilisateurs() {
  const btnAjout = document.getElementById('btn-ajouter-utilisateur');
  if (btnAjout) {
    // Cloner et remplacer le bouton pour supprimer les anciens écouteurs d'événements
    const newBtn = btnAjout.cloneNode(true);
    btnAjout.parentNode.replaceChild(newBtn, btnAjout);
    // Attacher le nouvel écouteur
    newBtn.addEventListener('click', ouvrirModalAjoutUtilisateur);
  }
  // Réinitialiser les événements des boutons modifier/supprimer après le rechargement du contenu
  // (Cela est implicitement géré car chargerUtilisateurs() recharge tout le tableau)
}


// Cette fonction est appelée depuis parametres.php après le chargement AJAX du tableau des utilisateurs
// Elle assure que le formulaire et les boutons ont leurs écouteurs d'événements attachés
function chargerUtilisateurs() {
  // Optionnel: Vous pouvez charger le tableau et les modales dans des appels fetch séparés
  // ou continuer à les charger comme un seul bloc si ça fonctionne bien.
  // Pour la robustesse, je vais les séparer un peu.
  
  // Charger le tableau des utilisateurs
  fetch('modules/utilisateurs/tableau.php')
    .then(r => r.text())
    .then(html => {
      const container = document.getElementById('contenu-parametres');
      container.innerHTML = html;

      // Charger les modales des utilisateurs après le tableau
      fetch('modules/utilisateurs/modals.php')
        .then(m => m.text())
        .then(modalHtml => {
          // Si le modal est déjà dans le DOM (par exemple, si vous ne rechargez que le tableau),
          // assurez-vous de ne pas le dupliquer ou de le remplacer correctement.
          // Pour l'instant, on assume que le container.innerHTML remplace tout le contenu.
          // Il pourrait être plus propre de placer les modales en dehors du 'contenu-parametres' si elles sont globales.
          // Mais pour l'architecture actuelle, on les ajoute.
          container.innerHTML += modalHtml; // Ajoute les modales au contenu actuel

          // Important: les initialisations doivent se faire APRÈS que tout le DOM est chargé
          setTimeout(() => {
            initialiserFormulaireUtilisateur(); //
            reinitialiserEvenementsUtilisateurs(); //
          }, 10); // Petit délai pour s'assurer que le DOM est à jour
        });
    })
    .catch(err => showToast("Erreur lors du chargement des utilisateurs ❌", "bg-red-500"));
}