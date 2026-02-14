<?php
?>


<div class="accueil-container">
  <h1 class="page-title">Tableau de bord</h1>
  <p class="welcome">Bienvenue sur l’espace admin de Mon taxi</p>

  <!-- Cartes statistiques -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="icon-bg" style="background: rgba(245, 203, 23, 0.15);">
        <i class="fas fa-users" style="color: var(--primary);"></i>
      </div>
      <div class="stat-content">
        <h3>Utilisateurs inscrits</h3>
        <p class="big-number">2 458</p>
        <span class="trend up"><i class="fas fa-arrow-up"></i> +14% ce mois</span>
      </div>
    </div>

    <div class="stat-card">
      <div class="icon-bg" style="background: rgba(4, 116, 84, 0.15);">
        <i class="fas fa-motorcycle" style="color: var(--secondary);"></i>
      </div>
      <div class="stat-content">
        <h3>Conducteurs actifs</h3>
        <p class="big-number">1 192</p>
        <span class="trend up"><i class="fas fa-arrow-up"></i> +9% ce mois</span>
      </div>
    </div>

    <div class="stat-card">
      <div class="icon-bg" style="background: rgba(245, 203, 23, 0.15);">
        <i class="fas fa-wallet" style="color: var(--primary);"></i>
      </div>
      <div class="stat-content">
        <h3>Revenus du mois</h3>
        <p class="big-number">148 500 XAF</p>
        <span class="trend up"><i class="fas fa-arrow-up"></i> +22%</span>
      </div>
    </div>

    <div class="stat-card alert">
      <div class="icon-bg" style="background: rgba(239, 68, 68, 0.15);">
        <i class="fas fa-bell" style="color: var(--danger);"></i>
      </div>
      <div class="stat-content">
        <h3>Alertes en attente</h3>
        <p class="big-number">19</p>
        <span class="trend">À traiter rapidement</span>
      </div>
    </div>
  </div>

  <!-- Graphique simple (courses par jour) -->
  <div class="chart-card">
    <h2>Évolution des courses (7 derniers jours)</h2>
    <!-- <canvas id="coursesChart" height="120"></canvas> -->
  </div>

  <!-- Dernières transactions -->
  <div class="table-card">
    <h2>Dernières transactions</h2>
    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Client</th>
            <th>Conducteur</th>
            <th>Montant</th>
            <th>Date</th>
            <th>Statut</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>#TX-98765</td>
            <td>Marie K.</td>
            <td>Paul Moto</td>
            <td>3 500 XOF</td>
            <td>13 Fév 2026 11:45</td>
            <td class="status success">Validée</td>
          </tr>
          <tr>
            <td>#TX-98764</td>
            <td>Jean D.</td>
            <td>Ali Driver</td>
            <td>4 200 XOF</td>
            <td>13 Fév 2026 10:30</td>
            <td class="status pending">En attente</td>
          </tr>
          <!-- Ajoute 4-5 lignes comme ça pour remplir -->
        </tbody>
      </table>
    </div>
  </div>
</div>


<!-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> -->
<!-- 
<script>
  // Graphique simple (courses par jour)
  const ctx = document.getElementById('coursesChart').getContext('2d');
  const coursesChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: ['6 Fév', '7 Fév', '8 Fév', '9 Fév', '10 Fév', '11 Fév', '12 Fév'],
      datasets: [{
        label: 'Courses effectuées',
        data: [45, 52, 38, 67, 89, 74, 92],
        borderColor: 'var(--primary)',
        backgroundColor: 'rgba(245, 203, 23, 0.2)',
        tension: 0.4,
        fill: true,
        pointBackgroundColor: 'var(--primary)',
        pointBorderColor: 'white',
        pointBorderWidth: 2,
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: { color: '#e2e8f0' }
        },
        x: {
          grid: { display: false }
        }
      }
    }
  });
</script> -->
