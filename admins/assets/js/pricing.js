// ===================================
// TAB SWITCHING
// ===================================
function switchTab(tabName) {
    // Hide all tabs
    const tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => tab.classList.remove('active'));
    
    // Remove active from all buttons
    const buttons = document.querySelectorAll('.tab-btn');
    buttons.forEach(btn => btn.classList.remove('active'));
    
    // Show selected tab
    document.getElementById(`tab-${tabName}`).classList.add('active');
    
    // Mark button as active
    event.target.classList.add('active');
}

// ===================================
// GRILLE MODAL FUNCTIONS
// ===================================
function openGrilleModal(grille = null) {
    const modal = document.getElementById('grilleModal');
    const title = document.getElementById('grilleModalTitle');
    const form = document.getElementById('grilleForm');
    
    if (grille) {
        title.textContent = 'Modifier la tranche tarifaire';
        document.getElementById('grille-id').value = grille.id;
        document.getElementById('grille-dist-min').value = grille.dist_min;
        document.getElementById('grille-dist-max').value = grille.dist_max;
        document.getElementById('grille-prix').value = grille.prix_base;
        document.getElementById('grille-actif').checked = grille.actif == 1;
    } else {
        title.textContent = 'Ajouter une tranche tarifaire';
        form.reset();
        document.getElementById('grille-id').value = '';
        document.getElementById('grille-actif').checked = true;
    }
    
    modal.style.display = 'flex';
}

function closeGrilleModal() {
    document.getElementById('grilleModal').style.display = 'none';
    document.getElementById('grilleForm').reset();
}

function editGrille(grille) {
    openGrilleModal(grille);
}

async function saveGrille(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const data = {
        id: formData.get('id') || null,
        dist_min: parseInt(formData.get('dist_min')),
        dist_max: parseInt(formData.get('dist_max')),
        prix_base: parseInt(formData.get('prix_base')),
        actif: formData.get('actif') ? 1 : 0
    };
    
    // Validation
    if (data.dist_min >= data.dist_max) {
        alert('❌ La distance minimale doit être inférieure à la distance maximale');
        return;
    }
    
    if (data.prix_base <= 0) {
        alert('❌ Le prix de base doit être supérieur à 0');
        return;
    }
    
    try {
        const response = await fetch('api/grille_save.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ ' + result.message);
            closeGrilleModal();
            location.reload();
        } else {
            alert('❌ ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Une erreur est survenue lors de l\'enregistrement');
    }
}

async function toggleGrilleStatus(id, newStatus) {
    const action = newStatus == 1 ? 'activer' : 'désactiver';
    
    if (!confirm(`Voulez-vous vraiment ${action} cette tranche ?`)) {
        return;
    }
    
    try {
        const response = await fetch('api/grille_toggle.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id, actif: newStatus })
        });
        
        const result = await response.json();
        
        if (result.success) {
            location.reload();
        } else {
            alert('❌ ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Une erreur est survenue');
    }
}

async function deleteGrille(id) {
    if (!confirm('⚠️ Êtes-vous sûr de vouloir supprimer cette tranche tarifaire ?\n\nCette action est irréversible.')) {
        return;
    }
    
    try {
        const response = await fetch('api/grille_delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ Tranche supprimée avec succès');
            location.reload();
        } else {
            alert('❌ ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Une erreur est survenue');
    }
}

// ===================================
// PLAN MODAL FUNCTIONS
// ===================================
function openPlanModal(plan = null) {
    const modal = document.getElementById('planModal');
    const title = document.getElementById('planModalTitle');
    const form = document.getElementById('planForm');
    
    if (plan) {
        title.textContent = 'Modifier le plan tarifaire';
        document.getElementById('plan-id').value = plan.id;
        document.getElementById('plan-nom').value = plan.nom_plan;
        document.getElementById('plan-slug').value = plan.slug;
        document.getElementById('plan-facteur').value = plan.facteur;
        document.getElementById('plan-position').value = plan.position;
        document.getElementById('plan-actif').checked = plan.actif == 1;
    } else {
        title.textContent = 'Ajouter un plan tarifaire';
        form.reset();
        document.getElementById('plan-id').value = '';
        document.getElementById('plan-actif').checked = true;
        document.getElementById('plan-facteur').value = '1.00';
        document.getElementById('plan-position').value = '1';
    }
    
    modal.style.display = 'flex';
}

function closePlanModal() {
    document.getElementById('planModal').style.display = 'none';
    document.getElementById('planForm').reset();
}

function editPlan(plan) {
    openPlanModal(plan);
}

async function savePlan(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const data = {
        id: formData.get('id') || null,
        nom_plan: formData.get('nom_plan'),
        slug: formData.get('slug'),
        facteur: parseFloat(formData.get('facteur')),
        position: parseInt(formData.get('position')),
        actif: formData.get('actif') ? 1 : 0
    };
    
    // Validation
    if (data.facteur <= 0) {
        alert('❌ Le facteur doit être supérieur à 0');
        return;
    }
    
    if (!/^[a-z0-9_-]+$/.test(data.slug)) {
        alert('❌ Le slug ne doit contenir que des lettres minuscules, chiffres, tirets et underscores');
        return;
    }
    
    try {
        const response = await fetch('api/plan_save.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ ' + result.message);
            closePlanModal();
            location.reload();
        } else {
            alert('❌ ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Une erreur est survenue lors de l\'enregistrement');
    }
}

async function togglePlanStatus(id, newStatus) {
    const action = newStatus == 1 ? 'activer' : 'désactiver';
    
    if (!confirm(`Voulez-vous vraiment ${action} ce plan ?`)) {
        return;
    }
    
    try {
        const response = await fetch('api/plan_toggle.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id, actif: newStatus })
        });
        
        const result = await response.json();
        
        if (result.success) {
            location.reload();
        } else {
            alert('❌ ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Une erreur est survenue');
    }
}

async function deletePlan(id) {
    if (!confirm('⚠️ Êtes-vous sûr de vouloir supprimer ce plan ?\n\nCette action est irréversible.')) {
        return;
    }
    
    try {
        const response = await fetch('api/plan_delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ Plan supprimé avec succès');
            location.reload();
        } else {
            alert('❌ ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Une erreur est survenue');
    }
}

// ===================================
// SIMULATEUR
// ===================================
async function calculatePrice() {
    const distance = parseInt(document.getElementById('sim-distance').value);
    const planSlug = document.getElementById('sim-plan').value;
    
    if (!distance || distance <= 0) {
        alert('❌ Veuillez entrer une distance valide');
        return;
    }
    
    if (!planSlug) {
        alert('❌ Veuillez sélectionner un plan tarifaire');
        return;
    }
    
    try {
        const response = await fetch(`api/calculate_price.php?distance=${distance}&plan=${planSlug}`);
        const result = await response.json();
        
        if (result.success) {
            displayResult(result.data);
        } else {
            alert('❌ ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Une erreur est survenue lors du calcul');
    }
}

function displayResult(data) {
    const resultDiv = document.getElementById('simulator-result');
    
    // Update values
    document.getElementById('result-tranche').textContent = 
        `${formatNumber(data.tranche.dist_min)} m → ${formatNumber(data.tranche.dist_max)} m`;
    
    document.getElementById('result-base').textContent = 
        `${formatNumber(data.tranche.prix_base)} FCFA`;
    
    document.getElementById('result-facteur').textContent = 
        `× ${data.plan.facteur} (${data.plan.nom_plan})`;
    
    document.getElementById('result-prix').textContent = 
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
    
    document.getElementById('result-formula').innerHTML = formula;
    
    // Show result with animation
    resultDiv.style.display = 'block';
    resultDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function formatNumber(num) {
    return new Intl.NumberFormat('fr-FR').format(num);
}

// ===================================
// AUTO-GENERATE SLUG FROM NAME
// ===================================
document.getElementById('plan-nom')?.addEventListener('input', function(e) {
    const slugInput = document.getElementById('plan-slug');
    if (!slugInput.value || slugInput.dataset.autoGenerated === 'true') {
        const slug = e.target.value
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '');
        
        slugInput.value = slug;
        slugInput.dataset.autoGenerated = 'true';
    }
});

document.getElementById('plan-slug')?.addEventListener('input', function() {
    this.dataset.autoGenerated = 'false';
});

// ===================================
// MODAL CLOSE ON OUTSIDE CLICK
// ===================================
window.onclick = function(event) {
    const grilleModal = document.getElementById('grilleModal');
    const planModal = document.getElementById('planModal');
    
    if (event.target === grilleModal) {
        closeGrilleModal();
    }
    
    if (event.target === planModal) {
        closePlanModal();
    }
}

// ===================================
// KEYBOARD SHORTCUTS
// ===================================
document.addEventListener('keydown', function(e) {
    // ESC to close modals
    if (e.key === 'Escape') {
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