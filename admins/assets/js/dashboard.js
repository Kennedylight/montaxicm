async function deleteGrille(id) {
  if (
    !confirm(
      "⚠️ Êtes-vous sûr de vouloir supprimer cette tranche tarifaire ?\n\nCette action est irréversible.",
    )
  ) {
    return;
  }

  try {
    const response = await fetch("auth/grille_delete.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        id
      }),
    });

    const result = await response.json();

    if (result.success) {
      alert("✅ Tranche supprimée avec succès");
      location.href = "index.php?page=tarification";
    } else {
      alert("❌ " + result.message);
    }
  } catch (error) {
    console.error("Error:", error);
    alert("❌ Une erreur est survenue");
  }
}
// ===================================
// TAB SWITCHING
// ===================================
function switchTab(tabName) {
  // Hide all tabs
  const tabs = document.querySelectorAll(".tab-content");
  tabs.forEach((tab) => tab.classList.remove("active"));

  // Remove active from all buttons
  const buttons = document.querySelectorAll(".tab-btn");
  buttons.forEach((btn) => btn.classList.remove("active"));

  // Show selected tab
  document.getElementById(`tab-${tabName}`).classList.add("active");

  // Mark button as active
  event.target.classList.add("active");
}

function openGrilleModal(grille = null) {
  const modal = document.getElementById("grilleModal");
  const title = document.getElementById("grilleModalTitle");
  const form = document.getElementById("grilleForm");

  if (grille) {
    title.textContent = "Modifier la tranche tarifaire";
    document.getElementById("grille-id").value = grille.id;
    document.getElementById("grille-dist-min").value = grille.dist_min;
    document.getElementById("grille-dist-max").value = grille.dist_max;
    document.getElementById("grille-prix").value = grille.prix_base;
    document.getElementById("grille-actif").checked = grille.actif == 1;
  } else {
    title.textContent = "Ajouter une tranche tarifaire";
    form.reset();
    document.getElementById("grille-id").value = "";
    document.getElementById("grille-actif").checked = true;
  }

  modal.style.display = "flex";
}
// ===================================
// GRILLE MODAL FUNCTIONS
// ===================================
function openGrilleModal(grille = null) {
  const modal = document.getElementById("grilleModal");
  const title = document.getElementById("grilleModalTitle");
  const form = document.getElementById("grilleForm");

  if (grille) {
    title.textContent = "Modifier la tranche tarifaire";
    document.getElementById("grille-id").value = grille.id;
    document.getElementById("grille-dist-min").value = grille.dist_min;
    document.getElementById("grille-dist-max").value = grille.dist_max;
    document.getElementById("grille-prix").value = grille.prix_base;
    document.getElementById("grille-actif").checked = grille.actif == 1;
  } else {
    title.textContent = "Ajouter une tranche tarifaire";
    form.reset();
    document.getElementById("grille-id").value = "";
    document.getElementById("grille-actif").checked = true;
  }

  modal.style.display = "flex";
}

function closeGrilleModal() {
  document.getElementById("grilleModal").style.display = "none";
  document.getElementById("grilleForm").reset();
}

function editGrille(grille) {
  openGrilleModal(grille);
}

async function saveGrille(event) {
  event.preventDefault();

  const formData = new FormData(event.target);
  const data = {
    id: formData.get("id") || null,
    dist_min: parseInt(formData.get("dist_min")),
    dist_max: parseInt(formData.get("dist_max")),
    prix_base: parseInt(formData.get("prix_base")),
    actif: formData.get("actif") ? 1 : 0,
  };

  // Validation
  if (data.dist_min >= data.dist_max) {
    alert(
      "❌ La distance minimale doit être inférieure à la distance maximale",
    );
    return;
  }

  if (data.prix_base <= 0) {
    alert("❌ Le prix de base doit être supérieur à 0");
    return;
  }

  try {
    const response = await fetch("auth/grille_save.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(data),
    });

    const result = await response.json();

    if (result.success) {
      alert("✅ " + result.message);
      closeGrilleModal();
      location.href = "index.php?page=tarification";
    } else {
      alert("❌ " + result.message);
    }
  } catch (error) {
    console.error("Error:", error);
    alert("❌ Une erreur est survenue lors de l'enregistrement");
  }
}

async function toggleGrilleStatus(id, newStatus) {
  const action = newStatus == 1 ? "activer" : "désactiver";

  if (!confirm(`Voulez-vous vraiment ${action} cette tranche ?`)) {
    return;
  }

  try {
    const response = await fetch("auth/grille_toggle.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        id,
        actif: newStatus
      }),
    });

    const result = await response.json();

    if (result.success) {
      location.href = "index.php?page=tarification";
    } else {
      alert("❌ " + result.message);
    }
  } catch (error) {
    console.error("Error:", error);
    alert("❌ Une erreur est survenue");
  }
}

// ===================================
// PLAN MODAL FUNCTIONS
// ===================================
function openPlanModal(plan = null) {
  const modal = document.getElementById("planModal");
  const title = document.getElementById("planModalTitle");
  const form = document.getElementById("planForm");

  if (plan) {
    title.textContent = "Modifier le plan tarifaire";
    document.getElementById("plan-id").value = plan.id;
    document.getElementById("plan-nom").value = plan.nom_plan;
    document.getElementById("plan-slug").value = plan.slug;
    document.getElementById("plan-facteur").value = plan.facteur;
    document.getElementById("plan-position").value = plan.position;
    document.getElementById("plan-actif").checked = plan.actif == 1;
  } else {
    title.textContent = "Ajouter un plan tarifaire";
    form.reset();
    document.getElementById("plan-id").value = "";
    document.getElementById("plan-actif").checked = true;
    document.getElementById("plan-facteur").value = "1.00";
    document.getElementById("plan-position").value = "1";
  }

  modal.style.display = "flex";
}

function closePlanModal() {
  document.getElementById("planModal").style.display = "none";
  document.getElementById("planForm").reset();
}

function editPlan(plan) {
  openPlanModal(plan);
}

async function savePlan(event) {
  event.preventDefault();

  const formData = new FormData(event.target);
  const data = {
    id: formData.get("id") || null,
    nom_plan: formData.get("nom_plan"),
    slug: formData.get("slug"),
    facteur: parseFloat(formData.get("facteur")),
    position: parseInt(formData.get("position")),
    actif: formData.get("actif") ? 1 : 0,
  };

  // Validation
  if (data.facteur <= 0) {
    alert("❌ Le facteur doit être supérieur à 0");
    return;
  }

  if (!/^[a-z0-9_-]+$/.test(data.slug)) {
    alert(
      "❌ Le slug ne doit contenir que des lettres minuscules, chiffres, tirets et underscores",
    );
    return;
  }

  try {
    const response = await fetch("auth/plan_save.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(data),
    });

    const result = await response.json();

    if (result.success) {
      alert("✅ " + result.message);
      closePlanModal();
      location.href = "index.php?page=tarification";
    } else {
      alert("❌ " + result.message);
    }
  } catch (error) {
    console.error("Error:", error);
    alert("❌ Une erreur est survenue lors de l'enregistrement");
  }
}

async function togglePlanStatus(id, newStatus) {
  const action = newStatus == 1 ? "activer" : "désactiver";

  if (!confirm(`Voulez-vous vraiment ${action} ce plan ?`)) {
    return;
  }

  try {
    const response = await fetch("auth/plan_toggle.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        id,
        actif: newStatus
      }),
    });

    const result = await response.json();

    if (result.success) {
      location.href = "index.php?page=tarification";
    } else {
      alert("❌ " + result.message);
    }
  } catch (error) {
    console.error("Error:", error);
    alert("❌ Une erreur est survenue");
  }
}

async function deletePlan(id) {
  if (
    !confirm(
      "⚠️ Êtes-vous sûr de vouloir supprimer ce plan ?\n\nCette action est irréversible.",
    )
  ) {
    return;
  }

  try {
    const response = await fetch("auth/plan_delete.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        id
      }),
    });

    const result = await response.json();

    if (result.success) {
      alert("✅ Plan supprimé avec succès");
      location.href = "index.php?page=tarification";
    } else {
      alert("❌ " + result.message);
    }
  } catch (error) {
    console.error("Error:", error);
    alert("❌ Une erreur est survenue");
  }
}

// ===================================
// SIMULATEUR
// ===================================
async function calculatePrice() {
  const distance = parseInt(document.getElementById("sim-distance").value);
  const planSlug = document.getElementById("sim-plan").value;

  if (!distance || distance <= 0) {
    alert("❌ Veuillez entrer une distance valide");
    return;
  }

  if (!planSlug) {
    alert("❌ Veuillez sélectionner un plan tarifaire");
    return;
  }

  try {
    const response = await fetch(
      `auth/calculate_price.php?distance=${distance}&plan=${planSlug}`,
    );
    const result = await response.json();

    if (result.success) {
      displayResult(result.data);
    } else {
      alert("❌ " + result.message);
    }
  } catch (error) {
    console.error("Error:", error);
    alert("❌ Une erreur est survenue lors du calcul");
  }
}

function displayResult(data) {
  const resultDiv = document.getElementById("simulator-result");

  // Update values
  document.getElementById("result-tranche").textContent =
    `${formatNumber(data.tranche.dist_min)} m → ${formatNumber(data.tranche.dist_max)} m`;

  document.getElementById("result-base").textContent =
    `${formatNumber(data.tranche.prix_base)} FCFA`;

  document.getElementById("result-facteur").textContent =
    `× ${data.plan.facteur} (${data.plan.nom_plan})`;

  document.getElementById("result-prix").textContent =
    `${formatNumber(data.prix_final)} FCFA`;

  // Build formula
  const formula = `
        <div class="formula-line">
            <span class="formula-part">${formatNumber(data.tranche.prix_base)} FCFA</span>
            <span class="formula-operator">×</span>
            <span class="formula-part">${data.plan.facteur}</span>
            <span class="formula-operator">=</span>
            <span class="formula-result">${formatNumber(data.prix_final)} FCFA</span>
        </div>
    `;

  document.getElementById("result-formula").innerHTML = formula;

  // Show result with animation
  resultDiv.style.display = "block";
  resultDiv.scrollIntoView({
    behavior: "smooth",
    block: "nearest"
  });
}

function formatNumber(num) {
  return new Intl.NumberFormat("fr-FR").format(num);
}

// ===================================
// AUTO-GENERATE SLUG FROM NAME
// ===================================
document.getElementById("plan-nom") ? document.getElementById("plan-nom").addEventListener("input", function (e) {
  const slugInput = document.getElementById("plan-slug");
  if (!slugInput.value || slugInput.dataset.autoGenerated === "true") {
    const slug = e.target.value
      .toLowerCase()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .replace(/[^a-z0-9]+/g, "_")
      .replace(/^_+|_+$/g, "");

    slugInput.value = slug;
    slugInput.dataset.autoGenerated = "true";
  }
}) : ''

document.getElementById("plan-slug") ? document.getElementById("plan-slug").addEventListener("input", function () {
  this.dataset.autoGenerated = "false";
}) : ''

// ===================================
// MODAL CLOSE ON OUTSIDE CLICK
// ===================================
window.onclick = function (event) {
  const grilleModal = document.getElementById("grilleModal");
  const planModal = document.getElementById("planModal");

  if (event.target === grilleModal) {
    closeGrilleModal();
  }

  if (event.target === planModal) {
    closePlanModal();
  }
};

// ===================================
// KEYBOARD SHORTCUTS
// ===================================
document.addEventListener("keydown", function (e) {
  // ESC to close modals
  if (e.key === "Escape") {
    closeGrilleModal();
    closePlanModal();
  }
});

// ===================================
// VALIDATION HELPERS
// ===================================
function validateGrilleOverlap(distMin, distMax, excludeId = null) {
  // This should be called before saving
  // Check if the new range overlaps with existing ranges
  // You can add this validation on the server-side as well
  return true;
}

// ===================================
// PLAN MODAL FUNCTIONS
// ===================================
function openPlanModal(plan = null) {
  const modal = document.getElementById("planModal");
  const title = document.getElementById("planModalTitle");
  const form = document.getElementById("planForm");

  if (plan) {
    title.textContent = "Modifier le plan tarifaire";
    document.getElementById("plan-id").value = plan.id;
    document.getElementById("plan-nom").value = plan.nom_plan;
    document.getElementById("plan-slug").value = plan.slug;
    document.getElementById("plan-facteur").value = plan.facteur;
    document.getElementById("plan-position").value = plan.position;
    document.getElementById("plan-actif").checked = plan.actif == 1;
  } else {
    title.textContent = "Ajouter un plan tarifaire";
    form.reset();
    document.getElementById("plan-id").value = "";
    document.getElementById("plan-actif").checked = true;
    document.getElementById("plan-facteur").value = "1.00";
    document.getElementById("plan-position").value = "1";
  }

  modal.style.display = "flex";
}

function closePlanModal() {
  document.getElementById("planModal").style.display = "none";
  document.getElementById("planForm").reset();
}

function editPlan(plan) {
  openPlanModal(plan);
}

async function savePlan(event) {
  event.preventDefault();

  const formData = new FormData(event.target);
  const data = {
    id: formData.get("id") || null,
    nom_plan: formData.get("nom_plan"),
    slug: formData.get("slug"),
    facteur: parseFloat(formData.get("facteur")),
    position: parseInt(formData.get("position")),
    actif: formData.get("actif") ? 1 : 0,
  };

  // Validation
  if (data.facteur <= 0) {
    alert("❌ Le facteur doit être supérieur à 0");
    return;
  }

  if (!/^[a-z0-9_-]+$/.test(data.slug)) {
    alert(
      "❌ Le slug ne doit contenir que des lettres minuscules, chiffres, tirets et underscores",
    );
    return;
  }

  try {
    const response = await fetch("auth/plan_save.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(data),
    });

    const result = await response.json();

    if (result.success) {
      alert("✅ " + result.message);
      closePlanModal();
      location.href = "index.php?page=tarification";
    } else {
      alert("❌ " + result.message);
    }
  } catch (error) {
    console.error("Error:", error);
    alert("❌ Une erreur est survenue lors de l'enregistrement");
  }
}

async function togglePlanStatus(id, newStatus) {
  const action = newStatus == 1 ? "activer" : "désactiver";

  if (!confirm(`Voulez-vous vraiment ${action} ce plan ?`)) {
    return;
  }

  try {
    const response = await fetch("auth/plan_toggle.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        id,
        actif: newStatus
      }),
    });

    const result = await response.json();

    if (result.success) {
      location.href = "index.php?page=tarification";
    } else {
      alert("❌ " + result.message);
    }
  } catch (error) {
    console.error("Error:", error);
    alert("❌ Une erreur est survenue");
  }
}

async function deletePlan(id) {
  if (
    !confirm(
      "⚠️ Êtes-vous sûr de vouloir supprimer ce plan ?\n\nCette action est irréversible.",
    )
  ) {
    return;
  }

  try {
    const response = await fetch("auth/plan_delete.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        id
      }),
    });

    const result = await response.json();

    if (result.success) {
      alert("✅ Plan supprimé avec succès");
      location.href = "index.php?page=tarification";
    } else {
      alert("❌ " + result.message);
    }
  } catch (error) {
    console.error("Error:", error);
    alert("❌ Une erreur est survenue");
  }
}



// ===================================
// MODAL CLOSE ON OUTSIDE CLICK
// ===================================
window.onclick = function (event) {
  const grilleModal = document.getElementById("grilleModal");
  const planModal = document.getElementById("planModal");

  if (event.target === grilleModal) {
    closeGrilleModal();
  }

  if (event.target === planModal) {
    closePlanModal();
  }
};

// ===================================
// KEYBOARD SHORTCUTS
// ===================================
document.addEventListener("keydown", function (e) {
  // ESC to close modals
  if (e.key === "Escape") {
    closeGrilleModal();
    closePlanModal();
  }
});

// ===================================
// VALIDATION HELPERS
// ===================================
function validateGrilleOverlap(distMin, distMax, excludeId = null) {
  // This should be called before saving
  // Check if the new range overlaps with existing ranges
  // You can add this validation on the server-side as well
  return true;
}


document.addEventListener("DOMContentLoaded", () => {
  console.log("Dashboard JS chargé avec succès !");

  const sidebar = document.querySelector(".sidebar");
  const menuToggle = document.querySelector(".menu-toggle");
  const content = document.getElementById("content");
  const loader = document.querySelector(".loader");
  const deconnexionLink = document.getElementById("deconnexion");
  const urlParams = new URLSearchParams(window.location.search);
  const page = urlParams.get("page");

  if (page) {
    loadPage(page);
  }
  deconnexionLink.addEventListener("click", (e) => {
    if (!confirm("Êtes-vous sûr de vouloir vous déconnecter ?")) {
      e.preventDefault();
    } else {
      submitBtn.innerHTML =
        '<i class="fas fa-spinner fa-spin"></i> Déconnexion...';

      fetch("../logout.php")
        .then(() => {
          window.location.href = "../";
        })
        .catch(() => {
          alert("Erreur lors de la déconnexion. Veuillez réessayer.");
        });
    }
  });

  // Toggle sidebar mobile
  if (menuToggle) {
    menuToggle.addEventListener("click", () => {
      if (sidebar) sidebar.classList.toggle("open");
    });
  }

  // Routage des liens sidebar
  if (sidebar) {
    sidebar.querySelectorAll("li").forEach((li) => {
      li.addEventListener("click", (e) => {
        const page = e.target.dataset.page;
        if (page) {
          loadPage(page);
          sidebar
            .querySelectorAll("li")
            .forEach((item) => item.classList.remove("active"));
          li.classList.add("active");
          sidebar.classList.remove("open");
        }
      });
    });
  }

  async function loadPage(page) {
    if (!content) return;
    loader.style.display = "block";
    content.innerHTML = "";

    await fetch(`${page}/index.php`)
      .then((response) => {
        if (!response.ok) throw new Error("Erreur " + response.status);
        console.log(`Page ${page} chargée avec succès !`);
        if (page === "utilisateurs") {
          allusers();
        }
        if (page === "conducteurs") {
          allconducteurs();
        }
        if (page === "tarification") {
          alltarification();
        }
        return response.text();
      })
      .then((html) => {
        content.innerHTML = html;
        loader.style.display = "none";

        // Ré-attache les événements après chargement dynamique
        attachDynamicEvents();
      })
      .catch((error) => {
        content.innerHTML =
          '<p style="color:red;padding:20px;">Erreur : ' +
          error.message +
          "</p>";
        loader.style.display = "none";
      });
  }

  function allconducteurs() {
    fetch("../auth/alldrivers.php")
      .then((res) => {
        if (!res.ok) throw new Error("Erreur " + res.status);
        return res.json();
      })
      .then((users) => {
        console.log("Données utilisateurs reçues :", users);
        let html = "";
        if (users.data.length === 0) {
          html =
            '<p style="text-align:center;padding:20px;"><i class="fas fa-users"></i>  Aucun conducteur trouvé.</p>';
        }
        users.data.forEach((user) => {
          html += `
                     <div class="user-card" data-user-id="${user.id}">
          <div class="user-header">
           <img src="../assets/images/profil.jpg" alt="Profile" class="user-image">
            <div class="user-basic">
              <h3>${user.nom} ${user.prenom}</h3>              
              <span class="user-status ${user.statut}">${user.statut}</span>
            </div>
          </div>
          <div class="user-info">
            <p><i class="fas fa-envelope"></i> ${user.email}</p>
            <p><i class="fas fa-phone"></i> ${user.telephone || "—"}</p>
            <p><i class="fas fa-calendar"></i> Inscrit ${user.cree_le}</p>
          </div>
           <div class="modal-actions">
           ${ user.statut == 0 ? `
          <a href="#" class="btn-block" data-userId="${user.id}"><i class="fas fa-ban"></i> Bloquer</a>
          ` : `
          <a href="#" class="btn-block" data-userId="${user.id}"><i class="fas fa-unlock"></i> Débloquer</a>
          `
           }
          <a href="#" class="btn-delete" data-userId="${user.id}"><i class="fas fa-trash"></i> Supprimer</a>
        </div>
        </div>
                  `;
        });
        const usersGrid = document.querySelector(".users-grid");
        if (usersGrid) usersGrid.innerHTML = html;

        attachDynamicEvents();
      });
  }




  async function saveGrille(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const data = {
      id: formData.get("id") || null,
      dist_min: parseInt(formData.get("dist_min")),
      dist_max: parseInt(formData.get("dist_max")),
      prix_base: parseInt(formData.get("prix_base")),
      actif: formData.get("actif") ? 1 : 0,
    };

    // Validation
    if (data.dist_min >= data.dist_max) {
      alert(
        "❌ La distance minimale doit être inférieure à la distance maximale",
      );
      return;
    }

    if (data.prix_base <= 0) {
      alert("❌ Le prix de base doit être supérieur à 0");
      return;
    }

    try {
      const response = await fetch("auth/grille_save.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(data),
      });

      const result = await response.json();

      if (result.success) {
        alert("✅ " + result.message);
        closeGrilleModal();
        location.href = "index.php?page=tarification";
      } else {
        alert("❌ " + result.message);
      }
    } catch (error) {
      console.error("Error:", error);
      alert("❌ Une erreur est survenue lors de l'enregistrement");
    }
  }

  async function toggleGrilleStatus(id, newStatus) {
    const action = newStatus == 1 ? "activer" : "désactiver";

    if (!confirm(`Voulez-vous vraiment ${action} cette tranche ?`)) {
      return;
    }

    try {
      const response = await fetch("auth/grille_toggle.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          id,
          actif: newStatus
        }),
      });

      const result = await response.json();

      if (result.success) {
        location.href = "index.php?page=tarification";
      } else {
        alert("❌ " + result.message);
      }
    } catch (error) {
      console.error("Error:", error);
      alert("❌ Une erreur est survenue");
    }
  }





  function allusers() {
    fetch("../auth/allusers.php")
      .then((res) => {
        if (!res.ok) throw new Error("Erreur " + res.status);
        return res.json();
      })
      .then((users) => {
        console.log("Données utilisateurs reçues :", users);
        let html = "";
        if (users.data.length === 0) {
          html =
            '<p style="text-align:center;padding:20px;"><i class="fas fa-users"></i>  Aucun utilisateur trouvé.</p>';
        }
        users.data.forEach((user) => {
          html += `
                     <div class="user-card" data-user-id="${user.id}">
          <div class="user-header">
           <img src="../assets/images/profil.jpg" alt="Profile" class="user-image">
            <div class="user-basic">
              <h3>${user.noms}</h3>
              <span class="user-status ${user.statut == 0 ? "actif" : "bloque"}">${user.statut == 0 ? 'actif' : 'bloqué'}</span>
            </div>
          </div>
          <div class="user-info">
            <p><i class="fas fa-envelope"></i> ${user.email}</p>
            <p><i class="fas fa-phone"></i> ${user.telephone || "—"}</p>
            <p><i class="fas fa-calendar"></i> Inscrit ${user.created_at}</p>
          </div>
           <div class="modal-actions">
           ${
             user.statut == 0
               ? `
          <a href="#" class="btn-block" data-userId="${user.id}"><i class="fas fa-ban"></i> Bloquer</a>
          `
               : `
          <a href="#" class="btn-block" data-userId="${user.id}"><i class="fas fa-unlock"></i> Débloquer</a>
          `
           }
          <a href="#" class="btn-delete" data-userId="${user.id}"><i class="fas fa-trash"></i> Supprimer</a>
        </div>
        </div>
                  `;
        });
        const usersGrid = document.querySelector(".users-grid");
        if (usersGrid) usersGrid.innerHTML = html;

        attachDynamicEvents();
      });
  }

  function alltarification() {
    console.log("Chargement des tarifications...");
    const btn_delete = document.querySelectorAll(".btn-delete");
    console.log("Boutons supprimer trouvés :", btn_delete.length);



    btn_delete.forEach((btn) => {
      btn.addEventListener("click", (e) => {
        e.preventDefault();
        const userId = btn.dataset.userid;
        if (confirm("Êtes-vous sûr de vouloir supprimer ce plan ?")) {
          fetch(`../auth/plandelete.php`, {
              method: "POST",
              body: new URLSearchParams({
                id: userId
              }),
              headers: {
                "Content-Type": "application/x-www-form-urlencoded",
              },
            })
            .then((res) => {
              if (!res.ok) throw new Error("Erreur " + res.status);
              return res.json();
            })
            .then((data) => {
              alert(data.message);
              loadPage("tarifications"); // Recharger la liste après action
            })
            .catch((err) => {
              alert("Erreur lors de la suppression : " + err.message);
            });
        }
      });
    });
  }

  function gestionButtom() {
    const btn_block = document.querySelectorAll(".btn-block");
    const btn_delete = document.querySelectorAll(".btn-delete");

    btn_block.forEach((btn) => {
      console.log("Bouton bloquer trouvé pour user ID:", btn.dataset.userid);
      btn.addEventListener("click", (e) => {
        e.preventDefault();
        const userId = btn.dataset.userid;
        if (confirm("Êtes-vous sûr de vouloir bloquer ce conducteur ?")) {
          // Appel AJAX pour bloquer l'utilisateur
          fetch(`../auth/bloque_driver.php`, {
              method: "POST",
              body: new URLSearchParams({
                id: userId
              }),
              headers: {
                "Content-Type": "application/x-www-form-urlencoded",
              },
            })
            .then((res) => {
              if (!res.ok) throw new Error("Erreur " + res.status);
              return res.json();
            })
            .then((data) => {
              alert(data.message);
              loadPage("conducteurs"); // Recharger la liste après action
            })
            .catch((err) => {
              alert("Erreur lors du blocage : " + err.message);
            });
        }
      });
    });

    btn_delete.forEach((btn) => {
      btn.addEventListener("click", (e) => {
        e.preventDefault();
        const userId = btn.dataset.userid;
        if (confirm("Êtes-vous sûr de vouloir supprimer ce conducteur ?")) {
          fetch(`../auth/delete_driver.php`, {
              method: "POST",
              body: new URLSearchParams({
                id: userId
              }),
              headers: {
                "Content-Type": "application/x-www-form-urlencoded",
              },
            })
            .then((res) => {
              if (!res.ok) throw new Error("Erreur " + res.status);
              return res.json();
            })
            .then((data) => {
              alert(data.message);
              loadPage("conducteurs"); // Recharger la liste après action
            })
            .catch((err) => {
              alert("Erreur lors de la suppression : " + err.message);
            });
        }
      });
    });
  }
  // Fonction pour attacher les événements sur les éléments chargés dynamiquement
  function attachDynamicEvents() {
    gestionButtom();
    // 3 points pour ouvrir modal
    document.querySelectorAll(".dots-menu").forEach((menu) => {
      menu.addEventListener("click", (e) => {
        e.stopPropagation();

        const card = e.target.closest(".user-card");
        if (!card) return;

        const userId = card.dataset.userId;
        const modal = document.getElementById("user-modal");
        const modalBody = document.getElementById("modal-body");

        modalBody.innerHTML =
          fakeDetails[userId] || "<p>Détails non disponibles.</p>";
        modal.style.display = "flex";
      });
    });
    const modalClose = document.getElementById("modal-close");
    if (modalClose) {
      modalClose.addEventListener("click", () => {
        document.getElementById("user-modal").style.display = "none";
      });
    }

    // Recherche live sur les cards
    const searchInput = document.getElementById("search-users");
    if (searchInput) {
      searchInput.addEventListener("input", () => {
        const query = searchInput.value.toLowerCase().trim();
        const cards = document.querySelectorAll(".user-card");
        let visibleCount = 0;
        cards.forEach((card) => {
          const text = card.textContent.toLowerCase();
          card.style.display = text.includes(query) ? "block" : "none";
          const matches = text.includes(query);
          if (matches) visibleCount++;
        });
        const noResultMessage = document.getElementById("no-result-message");

        if (visibleCount === 0 && query !== "") {
          if (!noResultMessage) {
            const message = document.createElement("p");
            message.id = "no-result-message";
            message.style.cssText =
              "text-align: center; color: #ef4444; font-size: 1.1rem; margin: 40px 0;";
            message.textContent = `Aucun utilisateur trouvé pour « ${query} »`;
            document.querySelector(".users-grid") ? document.querySelector(".users-grid").after(message) : '';
          }
        } else {
          if (noResultMessage) noResultMessage.remove();
        }

        console.log(`Résultat : ${visibleCount} cartes visibles`);
        console.log(`Recherche : "${query}", ${cards.length} cards filtrées`);

        if (query === "") {
          cards.forEach((card) => (card.style.display = "block"));
        }
      });
    }
  }

  loadPage("accueil");

  // Ré-attache les événements après chaque chargement dynamique
  attachDynamicEvents();
});