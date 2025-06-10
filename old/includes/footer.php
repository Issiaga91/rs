
</body>
</html>
<script>
function openModal() {
  document.getElementById("modal").classList.remove("hidden");
}
function closeModal() {
  document.getElementById("modal").classList.add("hidden");
  document.getElementById("fournisseurForm").reset();
  document.getElementById("formMessage").innerHTML = "";
}

// Soumission AJAX
document.getElementById("fournisseurForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const formData = new FormData(this);

  fetch("ajouter_ajax.php", {
    method: "POST",
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    const msg = document.getElementById("formMessage");
    if (data.success) {
      msg.className = "text-green-600";
      msg.innerText = "✅ Fournisseur ajouté avec succès !";
      setTimeout(() => window.location.reload(), 1000);
    } else {
      msg.className = "text-red-600";
      msg.innerText = data.message;
    }
  })
  .catch(() => {
    document.getElementById("formMessage").innerText = "❌ Erreur lors de l'ajout.";
  });
});
</script>
<script>
function openEditModal(code, nom) {
  document.getElementById("editModal").classList.remove("hidden");
  document.getElementById("editCode").value = code;
  document.getElementById("editCodeDisplay").value = code;
  document.getElementById("editNom").value = nom;
}

function closeEditModal() {
  document.getElementById("editModal").classList.add("hidden");
  document.getElementById("editFournisseurForm").reset();
  document.getElementById("editFormMessage").innerHTML = "";
}

// Soumission AJAX du formulaire de modification
document.getElementById("editFournisseurForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const formData = new FormData(this);

  fetch("modifier_ajax.php", {
    method: "POST",
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    const msg = document.getElementById("editFormMessage");
    if (data.success) {
      msg.className = "text-green-600";
      msg.innerText = "✅ Modification enregistrée.";
      setTimeout(() => window.location.reload(), 1000);
    } else {
      msg.className = "text-red-600";
      msg.innerText = data.message;
    }
  })
  .catch(() => {
    document.getElementById("editFormMessage").innerText = "❌ Erreur lors de la modification.";
  });
});
</script>
<script>
let codeToDelete = null;

function openDeleteModal(code) {
  codeToDelete = code;
  document.getElementById("deleteModal").classList.remove("hidden");
}

function closeDeleteModal() {
  codeToDelete = null;
  document.getElementById("deleteModal").classList.add("hidden");
  document.getElementById("deleteFormMessage").innerText = "";
}

function deleteFournisseur() {
  if (!codeToDelete) return;

  const formData = new FormData();
  formData.append('code', codeToDelete);

  fetch("supprimer_ajax.php", {
    method: "POST",
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      document.getElementById("deleteFormMessage").className = "text-green-600";
      document.getElementById("deleteFormMessage").innerText = "✅ Fournisseur supprimé.";
      setTimeout(() => window.location.reload(), 1000);
    } else {
      document.getElementById("deleteFormMessage").className = "text-red-600";
      document.getElementById("deleteFormMessage").innerText = data.message;
    }
  })
  .catch(() => {
    document.getElementById("deleteFormMessage").innerText = "❌ Une erreur est survenue.";
  });
}
</script>
<script>
document.getElementById("searchInput").addEventListener("input", function () {
  const query = this.value;

  fetch("recherche_ajax.php?search=" + encodeURIComponent(query))
    .then(res => res.text())
    .then(html => {
      document.getElementById("fournisseursBody").innerHTML = html;
    })
    .catch(err => console.error("Erreur AJAX :", err));
});
</script>
<script>
function openAddFournisseurModal() {
  document.getElementById('modal-ajout-fournisseur').classList.remove('hidden');
}
function closeAddFournisseurModal() {
  document.getElementById('modal-ajout-fournisseur').classList.add('hidden');
}
</script>
