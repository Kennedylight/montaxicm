<?php
session_start();
// Vérifier que l'utilisateur est admin
if (!isset($_SESSION['id'])) {
    header('Location: ./login');
    exit;
}

require_once '../inc/main.php';


// Récupérer le filtre de statut

$statut_filtre = isset($_GET['statut']) ? $_GET['statut'] : 'tous';

// Construire la requête SQL
$sql = "SELECT 
            k.*,
            c.nom,
            c.prenom,
            c.telephone,
            c.email,
            CONCAT(c.nom, ' ', c.prenom) as nom_complet
        FROM kyc k
        INNER JOIN chauffeurs c ON k.chauffeur_id = c.id";

if ($statut_filtre !== 'tous') {
    $sql .= " WHERE k.statut = :statut";
}

$sql .= " ORDER BY 
            CASE k.statut
                WHEN 'soumis' THEN 1
                WHEN 'en_cours' THEN 2
                WHEN 'approuve' THEN 3
                WHEN 'rejete' THEN 4
            END,
            k.soumis_le DESC";

$stmt = $bdd->prepare($sql);

if ($statut_filtre !== 'tous') {
    $stmt->bindParam(':statut', $statut_filtre);
}

$stmt->execute();
$kycs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Compter les KYC par statut
$count_sql = "SELECT statut, COUNT(*) as total FROM kyc GROUP BY statut";
$count_stmt = $bdd->query($count_sql);
$counts = [];
while ($row = $count_stmt->fetch(PDO::FETCH_ASSOC)) {
    $counts[$row['statut']] = $row['total'];
}

$total_kyc = array_sum($counts);
?>
<style>
    /* ===================================
   VARIABLES ET RESET
   =================================== */
:root {
    --primary-color: #2563eb;
    --primary-dark: #1e40af;
    --success-color: #16a34a;
    --warning-color: #ea580c;
    --danger-color: #dc2626;
    --info-color: #0891b2;
    --gray-50: #f9fafb;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-300: #d1d5db;
    --gray-400: #9ca3af;
    --gray-500: #6b7280;
    --gray-600: #4b5563;
    --gray-700: #374151;
    --gray-800: #1f2937;
    --gray-900: #111827;
    --white: #ffffff;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    --radius-sm: 0.375rem;
    --radius: 0.5rem;
    --radius-lg: 0.75rem;
    --radius-xl: 1rem;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
   font-family: 'Po02', sans-serif;
    font-size: 14px;
    line-height: 1.5;
    color:black;
    background-color: var(--gray-50);
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

/* ===================================
   LAYOUT PRINCIPAL
   =================================== */
.dashboard-container {
    display: flex;
    width: 100% ;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
}
.main{
    width: 100% ;
}



.logo {
    padding: 0 1.5rem 2rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.logo h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--white);
}








.subtitle {
    color: var(--gray-600);
    font-size: 0.875rem;
}

.back-link {
    display: inline-flex;
    align-items: center;
    color: var(--primary-color);
    text-decoration: none;
    font-size: 0.875rem;
    margin-bottom: 1rem;
    font-weight: 500;
    transition: color 0.2s;
}

.back-link:hover {
    color: var(--primary-dark);
}

.admin-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: 0.875rem;
    color: var(--gray-600);
}

.btn-logout {
    padding: 0.5rem 1rem;
    background: var(--gray-100);
    border: 1px solid var(--gray-300);
    border-radius: var(--radius);
    text-decoration: none;
    color: var(--gray-700);
    font-size: 0.875rem;
    transition: all 0.2s;
}

.btn-logout:hover {
    background: var(--gray-200);
}

/* ===================================
   STATISTIQUES
   =================================== */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
    margin-top: 22px;
}

.stat-card {
    background: var(--white);
    padding: 1.5rem;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.stat-icon {
    font-size: 2.5rem;
    line-height: 1;
}
tr:nth-child(even) {
  background: green;
  display: inline-block;
  color:black;
}

.stat-info h3 {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 0.25rem;
}

.stat-info p {
    font-size: 0.875rem;
    color: var(--gray-600);
}

.stat-total { border-left: 4px solid var(--primary-color); }
.stat-pending { border-left: 4px solid var(--warning-color); }
.stat-progress { border-left: 4px solid var(--info-color); }
.stat-approved { border-left: 4px solid var(--success-color); }
.stat-rejected { border-left: 4px solid var(--danger-color); }

/* ===================================
   FILTRES
   =================================== */
.filters-bar {
    background: var(--white);
    padding: 1.5rem;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
    margin-bottom: 2rem;
}

.filter-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.filter-btn {
    padding: 0.625rem 1.25rem;
    background: var(--primary);
    border: 1px solid var(--gray-300);
    border-radius: var(--radius);
    text-decoration: none;
    color: var(--gray-700);
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s;
    cursor: pointer;
}

.filter-btn:hover {
    background: var(--gray-200);
}

.filter-btn.active {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: var(--white);
}

/* ===================================
   LISTE KYC
   =================================== */
.kyc-list {
    background: var(--white);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.kyc-table {
    width: 100%;
    border-collapse: collapse;
}

.kyc-table thead {
    background: var(--gray-50);
    border-bottom: 2px solid var(--gray-200);
}

.kyc-table th {
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    font-size: 0.875rem;
    color: var(--gray-700);
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.kyc-table td {
    padding: 1rem;
    border-bottom: 1px solid var(--gray-200);
    font-size: 0.875rem;
}

.kyc-row:hover {
    background: var(--gray-50);
}

.driver-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.driver-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--gray-200);
}

.driver-avatar-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--primary-color);
    color: var(--white);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1rem;
}

.contact-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.contact-info .email {
    font-size: 0.75rem;
    color:black;
}

/* ===================================
   STATUTS
   =================================== */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    border-radius: var(--radius);
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.status-progress {
    background: #cffafe;
    color: #164e63;
}

.status-approved {
    background: #d1fae5;
    color: #065f46;
}

.status-rejected {
    background: #fee2e2;
    color: #991b1b;
}

/* ===================================
   BOUTONS D'ACTION
   =================================== */
.btn-action {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.5rem 1rem;
    border-radius: var(--radius);
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s;
    cursor: pointer;
}

.btn-view {
    background: var(--primary-color);
    color: var(--white);
}

.btn-view:hover {
    background: var(--primary-dark);
}

/* ===================================
   EMPTY STATE
   =================================== */
.empty-state {
    padding: 4rem 2rem;
    text-align: center;
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.empty-state h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--gray-900);
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: var(--gray-600);
}

/* ===================================
   ALERTS
   =================================== */
.alert {
    padding: 1rem 1.5rem;
    border-radius: var(--radius-lg);
    margin-bottom: 2rem;
    font-weight: 500;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #6ee7b7;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

/* ===================================
   DÉTAILS KYC
   =================================== */
.detail-container {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 2rem;
}

.detail-main {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.status-badge-large {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    border-radius: var(--radius-lg);
    font-size: 1.125rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
}

.status-icon {
    font-size: 1.5rem;
}

.status-meta {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    color: var(--gray-600);
    font-size: 0.875rem;
}

.status-meta strong {
    color: var(--gray-900);
}

/* ===================================
   SECTIONS D'INFORMATION
   =================================== */
.info-section {
    background: var(--white);
    padding: 2rem;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
}

.info-section h2 {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--gray-900);
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid var(--gray-100);
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
}

.info-item.full-width {
    grid-column: 1 / -1;
}

.info-label {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color:black;
}

.info-value {
    font-size: 0.9375rem;
    color: var(--gray-900);
    font-weight: 500;
}

.btn-map {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    margin-left: 1rem;
    padding: 0.375rem 0.75rem;
    background: var(--primary-color);
    color: var(--white);
    text-decoration: none;
    border-radius: var(--radius);
    font-size: 0.75rem;
    transition: background 0.2s;
}

.btn-map:hover {
    background: var(--primary-dark);
}

/* ===================================
   DOCUMENTS
   =================================== */
.documents-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.document-card {
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-lg);
    overflow: hidden;
}

.document-card h3 {
    padding: 1rem;
    background: var(--gray-50);
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--gray-900);
    border-bottom: 1px solid var(--gray-200);
}

.document-viewer {
    position: relative;
    background: var(--gray-100);
    padding: 1rem;
    min-height: 250px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.document-image {
    max-width: 100%;
    height: auto;
    max-height: 400px;
    border-radius: var(--radius);
    cursor: pointer;
    transition: transform 0.2s;
}

.document-image:hover {
    transform: scale(1.02);
}

.btn-download {
    display: block;
    padding: 0.75rem;
    text-align: center;
    background: var(--gray-50);
    color: var(--primary-color);
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    transition: background 0.2s;
}

.btn-download:hover {
    background: var(--gray-100);
}

/* ===================================
   COMMENTAIRES
   =================================== */
.comment-box {
    padding: 1rem;
    background: var(--gray-50);
    border-left: 4px solid var(--primary-color);
    border-radius: var(--radius);
    color: var(--gray-700);
    line-height: 1.6;
}


.action-panel {
    background: var(--white);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.action-panel h3 {
    padding: 1.5rem;
    background: var(--gray-50);
    border-bottom: 2px solid var(--gray-200);
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--gray-900);
}

.action-form {
    padding: 1.5rem;
    border-bottom: 1px solid var(--gray-200);
}

.action-form:last-of-type {
    border-bottom: none;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--gray-700);
}

.required {
    color: var(--danger-color);
}

.form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--gray-300);
    border-radius: var(--radius);
    font-size: 0.875rem;
    font-family: inherit;
    resize: vertical;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.form-group textarea:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.btn {
    width: 100%;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: var(--radius);
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-approve {
    background: var(--success-color);
    color: var(--white);
}

.btn-approve:hover {
    background: #15803d;
}

.btn-progress {
    background: var(--info-color);
    color: var(--white);
}

.btn-progress:hover {
    background: #0e7490;
}

.btn-reject {
    background: var(--danger-color);
    color: var(--white);
}

.btn-reject:hover {
    background: #b91c1c;
}

/* ===================================
   INFO BOX
   =================================== */
.info-box {
    padding: 1.5rem;
    background: #eff6ff;
    border-left: 4px solid var(--primary-color);
}

.info-box h4 {
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--gray-900);
    margin-bottom: 1rem;
}

.info-box ul {
    list-style: none;
    padding: 0;
}

.info-box li {
    padding: 0.5rem 0;
    padding-left: 1.5rem;
    position: relative;
    font-size: 0.875rem;
    color: var(--gray-700);
}

.info-box li::before {
    content: "✓";
    position: absolute;
    left: 0;
    color: var(--success-color);
    font-weight: 700;
}

/* ===================================
   MODAL IMAGES
   =================================== */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.9);
    align-items: center;
    justify-content: center;
}

.modal-content {
    max-width: 90%;
    max-height: 90vh;
    object-fit: contain;
}

.modal-close {
    position: absolute;
    top: 20px;
    right: 40px;
    color: var(--white);
    font-size: 40px;
    font-weight: 700;
    cursor: pointer;
    transition: color 0.2s;
}

.modal-close:hover {
    color: var(--gray-400);
}

/* ===================================
   RESPONSIVE
   =================================== */
@media (max-width: 1200px) {
    .detail-container {
        grid-template-columns: 1fr;
    }
    
    
}

@media (max-width: 768px) {
  
    
   
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .filter-buttons {
        flex-direction: column;
    }
    
    .filter-btn {
        width: 100%;
    }
    
    .kyc-table {
        display: block;
        overflow-x: auto;
    }
    
    .documents-grid {
        grid-template-columns: 1fr;
    }
    .page-header {
       padding-bottom: 32px;
}
}
</style>

    <div class="dashboard-container">
         
        <main class="main">
            <header class="page-header">
                <div>
                    <h1>Gestion des KYC</h1>
                    <p class="subtitle">Gérez les vérifications KYC des utilisateurs</p>
                </div>
               
            </header>

            <!-- Statistiques -->
           <div class="stats-grid">
    <div class="stat-card stat-total">
        <div class="stat-icon">
            <i class="fas fa-id-card" style="color: blue;"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $total_kyc; ?></h3>
            <p>Total KYC</p>
        </div>
    </div>
    
    <div class="stat-card stat-pending">
        <div class="stat-icon">
            <i class="fas fa-clock" style="color: gray;"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $counts['soumis'] ?? 0; ?></h3>
            <p>En attente</p>
        </div>
    </div>
    
    <div class="stat-card stat-progress">
        <div class="stat-icon">
            <i class="fas fa-spinner"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $counts['en_cours'] ?? 0; ?></h3>
            <p>En cours</p>
        </div>
    </div>
    
    <div class="stat-card stat-approved">
        <div class="stat-icon">
            <i class="fas fa-check-circle" style="color: green;"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $counts['approuve'] ?? 0; ?></h3>
            <p>Approuvés</p>
        </div>
    </div>
    
    <div class="stat-card stat-rejected">
        <div class="stat-icon">
            <i class="fas fa-times-circle" style="color: red;"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $counts['rejete'] ?? 0; ?></h3>
            <p>Rejetés</p>
        </div>
    </div>
</div>

            <!-- Filtres -->
            <div class="filters-bar">
                <div class="filter-buttons">
                    <a href="?page=verificationkyc&statut=tous" class="filter-btn <?php echo $statut_filtre === 'tous' ? 'active' : ''; ?>">
                        Tous
                    </a>
                    <a href="?page=verificationkyc&statut=soumis" class="filter-btn <?php echo $statut_filtre === 'soumis' ? 'active' : ''; ?>">
                        En attente (<?php echo $counts['soumis'] ?? 0; ?>)
                    </a>
                    <a href="?page=verificationkyc&statut=en_cours" class="filter-btn <?php echo $statut_filtre === 'en_cours' ? 'active' : ''; ?>">
                        En cours (<?php echo $counts['en_cours'] ?? 0; ?>)
                    </a>
                    <a href="?page=verificationkyc&statut=approuve" class="filter-btn <?php echo $statut_filtre === 'approuve' ? 'active' : ''; ?>">
                        Approuvés (<?php echo $counts['approuve'] ?? 0; ?>)
                    </a>
                    <a href="?page=verificationkyc&statut=rejete" class="filter-btn <?php echo $statut_filtre === 'rejete' ? 'active' : ''; ?>">
                        Rejetés (<?php echo $counts['rejete'] ?? 0; ?>)
                    </a>
                </div>
            </div>

            <!-- Liste des KYC -->
            <div class="kyc-list">
                <?php if (empty($kycs)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📭</div>
                        <h3>Aucun KYC trouvé</h3>
                        <p>Il n'y a pas de demandes KYC avec ce statut pour le moment.</p>
                    </div>
                <?php else: ?>
                    <table class="kyc-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Chauffeur</th>
                                <th>Contact</th>
                                <th>Ville</th>
                                <th>CNI N°</th>
                                <th>Soumis le</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($kycs as $kyc): ?>
                                <tr class="kyc-row">
                                    <td>#<?php echo $kyc['id']; ?></td>
                                    <td>
                                        <div class="driver-info">
                                            <img src="../assets/images/profil.jpg" class="driver-avatar" alt="Profile" class="user-image">
                                            
                                            <span><?php echo htmlspecialchars($kyc['nom_complet']); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="contact-info">
                                            <div>📞 <?php echo htmlspecialchars($kyc['telephone']); ?></div>
                                            <?php if ($kyc['email']): ?>
                                                <div class="email">✉️ <?php echo htmlspecialchars($kyc['email']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($kyc['ville'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($kyc['cni_numero'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($kyc['soumis_le'])); ?></td>
                                    <td>
                                        <?php
                                        $status_class = '';
                                        $status_text = '';
                                        switch($kyc['statut']) {
                                            case 'soumis':
                                                $status_class = 'status-pending';
                                                $status_text = '⏳ En attente';
                                                break;
                                            case 'en_cours':
                                                $status_class = 'status-progress';
                                                $status_text = '🔄 En cours';
                                                break;
                                            case 'approuve':
                                                $status_class = 'status-approved';
                                                $status_text = '✅ Approuvé';
                                                break;
                                            case 'rejete':
                                                $status_class = 'status-rejected';
                                                $status_text = '❌ Rejeté';
                                                break;
                                        }
                                        ?>
                                        <span class="status-badge <?php echo $status_class; ?>">
                                            <?php echo $status_text; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/verificationkyc/detail.php?id=<?php echo $kyc['id']; ?>" 
                                           class="btn-action btn-view">
                                            👁️ Voir détails
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>
