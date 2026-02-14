<?php
session_start();

// Vérifier que l'utilisateur est admin
if (!isset($_SESSION['id'])) {
    header('Location:./login');
    exit;
}

require_once '../inc/main.php';

$kyc_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($kyc_id <= 0) {
    header('Location: /?page=verificationkyc');
    exit;
}

// Récupérer les détails du KYC
$sql = "SELECT 
            k.*,
            c.nom,
            c.prenom,
            c.telephone,
            c.email,
            CONCAT(c.nom, ' ', c.prenom) as nom_complet,
            admin.nom as admin_nom,
            admin.prenom as admin_prenom
        FROM kyc k
        INNER JOIN chauffeurs c ON k.chauffeur_id = c.id
        LEFT JOIN administrateurs admin ON k.verifie_par = admin.id
        WHERE k.id = :kyc_id";

$stmt = $bdd->prepare($sql);
$stmt->bindParam(':kyc_id', $kyc_id, PDO::PARAM_INT);
$stmt->execute();
$kyc = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$kyc) {
    header('Location: /?page=verificationkyc');
    exit;
}

// Traitement du formulaire de validation/rejet
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $commentaire = trim($_POST['commentaire'] ?? '');
    
    if (in_array($action, ['approuver', 'rejeter', 'en_cours'])) {
        $nouveau_statut = '';
        
        switch ($action) {
            case 'approuver':
                $nouveau_statut = 'approuve';
                break;
            case 'rejeter':
                $nouveau_statut = 'rejete';
                if (empty($commentaire)) {
                    $error = "Veuillez indiquer le motif du rejet.";
                }
                break;
            case 'en_cours':
                $nouveau_statut = 'en_cours';
                break;
        }
        
        if (!isset($error)) {
            $update_sql = "UPDATE kyc 
                          SET statut = :statut,
                              commentaire_admin = :commentaire,
                              verifie_le = NOW(),
                              verifie_par = :admin_id
                          WHERE id = :kyc_id";
            
            $update_stmt = $bdd->prepare($update_sql);
            $update_stmt->bindParam(':statut', $nouveau_statut);
            $update_stmt->bindParam(':commentaire', $commentaire);
            $update_stmt->bindParam(':admin_id', $_SESSION['admin_id'], PDO::PARAM_INT);
            $update_stmt->bindParam(':kyc_id', $kyc_id, PDO::PARAM_INT);
            
            if ($update_stmt->execute()) {
                $success = "Le KYC a été " . ($nouveau_statut === 'approuve' ? 'approuvé' : ($nouveau_statut === 'rejete' ? 'rejeté' : 'mis en cours')) . " avec succès.";
                
                // Recharger les données
                $stmt->execute();
                $kyc = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error = "Une erreur s'est produite lors de la mise à jour.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails KYC #<?php echo $kyc_id; ?> - Dashboard</title>
    <link rel="stylesheet" href="<?= $css ?>polices.css">
</head>
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
    color: var(--gray-800);
    background-color: var(--gray-50);
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

/* ===================================
   LAYOUT PRINCIPAL
   =================================== */
.dashboard-container {
    display: flex;
    width: 100%;
    display: flex;
    justify-content: center;
    
    min-height: 100vh;
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

.nav-menu {
    padding: 1.5rem 0;
}

.nav-menu a {
    display: block;
    padding: 0.75rem 1.5rem;
    color: var(--gray-300);
    text-decoration: none;
    transition: all 0.2s;
    border-left: 3px solid transparent;
}

.nav-menu a:hover {
    background: rgba(255, 255, 255, 0.05);
    color: var(--white);
}

.nav-menu a.active {
    background: rgba(37, 99, 235, 0.1);
    color: var(--white);
    border-left-color: var(--primary-color);
}

/* ===================================
   MAIN CONTENT
   =================================== */
.main-content {
    flex: 1;
    width: 90%;
    padding: 2rem;
    max-width: calc(100vw - 260px);
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--gray-200);
}

.page-header h1 {
    font-size: 1.875rem;
    font-weight: 700;
    color: var(--gray-900);
    margin-bottom: 0.5rem;
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
    background: var(--gray-100);
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
    color: var(--gray-500);
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

.status-header {
    background: var(--white);
    padding: 2rem;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
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
    color: var(--gray-500);
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

/* ===================================
   PANEL D'ACTIONS
   =================================== */


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
  
    
    .main-content {
        margin-left: 0;
        max-width: 100%;
        padding: 1rem;
    }
    
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
    
    .page-header {
        flex-direction: column;
        gap: 1rem;
    }
    
    .documents-grid {
        grid-template-columns: 1fr;
    }
}
</style>
<body>
    <div class="dashboard-container">
        

        <!-- Main Content -->
        <main class="main-content">
            <header class="page-header">
                <div>
                    <a href="/?page=verificationkyc" class="back-link">← Retour à la liste</a>
                    <h1>Détails du KYC #<?php echo $kyc_id; ?></h1>
                    <p class="subtitle">Chauffeur: <?php echo htmlspecialchars($kyc['nom_complet']); ?></p>
                </div>
                <div class="admin-info">
                    <span>Admin: <?php echo htmlspecialchars($_SESSION['name'] ?? 'Admin'); ?></span>
                </div>
            </header>

            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle" style="color: green;"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                     <i class="fas fa-times-circle" style="color: red;"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="detail-container">
                <!-- Colonne gauche: Informations -->
                <div class="detail-main">
                    <!-- Statut actuel -->
                    <div class="status-header">
                        <?php
                        $status_class = '';
                        $status_text = '';
                        $status_icon = '';
                        switch($kyc['statut']) {
                            case 'soumis':
                                $status_class = 'status-pending';
                                $status_text = 'En attente de vérification';
                                $status_icon = '⏳';
                                break;
                            case 'en_cours':
                                $status_class = 'status-progress';
                                $status_text = 'Vérification en cours';
                                $status_icon = ' <i class="fas fa-spinner"></i>';
                                break;
                            case 'approuve':
                                $status_class = 'status-approved';
                                $status_text = 'KYC Approuvé';
                                $status_icon = '<i class="fas fa-check-circle" style="color: green;"></i>';
                                break;
                            case 'rejete':
                                $status_class = 'status-rejected';
                                $status_text = 'KYC Rejeté';
                                $status_icon = ' <i class="fas fa-times-circle" style="color: red;"></i>';
                                break;
                        }
                        ?>
                        <div class="status-badge-large <?php echo $status_class; ?>">
                            <span class="status-icon"><?php echo $status_icon; ?></span>
                            <span class="status-text"><?php echo $status_text; ?></span>
                        </div>
                        <div class="status-meta">
                            <p><strong>Soumis le:</strong> <?php echo date('d/m/Y à H:i', strtotime($kyc['soumis_le'])); ?></p>
                            <?php if ($kyc['verifie_le']): ?>
                                <p><strong>Vérifié le:</strong> <?php echo date('d/m/Y à H:i', strtotime($kyc['verifie_le'])); ?></p>
                                <?php if ($kyc['admin_nom']): ?>
                                    <p><strong>Vérifié par:</strong> <?php echo htmlspecialchars($kyc['admin_nom'] . ' ' . $kyc['admin_prenom']); ?></p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Informations personnelles -->
                    <div class="info-section">
                        <h2>👤 Informations personnelles</h2>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Nom complet:</span>
                                <span class="info-value"><?php echo htmlspecialchars($kyc['nom_complet']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Téléphone:</span>
                                <span class="info-value">📞 <?php echo htmlspecialchars($kyc['telephone']); ?></span>
                            </div>
                            <?php if ($kyc['email']): ?>
                            <div class="info-item">
                                <span class="info-label">Email:</span>
                                <span class="info-value">✉️ <?php echo htmlspecialchars($kyc['email']); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if ($kyc['date_naissance']): ?>
                            <div class="info-item">
                                <span class="info-label">Date de naissance:</span>
                                <span class="info-value"><?php echo date('d/m/Y', strtotime($kyc['date_naissance'])); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if ($kyc['genre']): ?>
                            <div class="info-item">
                                <span class="info-label">Genre:</span>
                                <span class="info-value"><?php echo htmlspecialchars($kyc['genre']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Photo du chauffeur -->
                    <?php if ($kyc['photo_chauffeur']): ?>
                    <div class="info-section">
                        <h2>📸 Photo du chauffeur</h2>
                        <div class="document-viewer">
                            <img src="<?php echo htmlspecialchars($kyc['photo_chauffeur']); ?>" 
                                 alt="Photo du chauffeur" 
                                 class="document-image"
                                 onclick="openImageModal(this.src)">
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- CNI -->
                    <div class="info-section">
                        <h2><i class="fas fa-id-card" style="color: blue;"></i> Carte Nationale d'Identité</h2>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Numéro CNI:</span>
                                <span class="info-value"><?php echo htmlspecialchars($kyc['cni_numero'] ?? 'Non renseigné'); ?></span>
                            </div>
                            <?php if ($kyc['cni_date_expiry']): ?>
                            <div class="info-item">
                                <span class="info-label">Date d'expiration:</span>
                                <span class="info-value"><?php echo date('d/m/Y', strtotime($kyc['cni_date_expiry'])); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="documents-grid">
                            <?php if ($kyc['cni_recto']): ?>
                            <div class="document-card">
                                <h3>Recto CNI</h3>
                                <div class="document-viewer">
                                    <img src="<?php echo htmlspecialchars($kyc['cni_recto']); ?>" 
                                         alt="CNI Recto" 
                                         class="document-image"
                                         onclick="openImageModal(this.src)">
                                </div>
                                <a href="<?php echo htmlspecialchars($kyc['cni_recto']); ?>" 
                                   target="_blank" 
                                   class="btn-download">
                                    📥 Télécharger
                                </a>
                            </div>
                            <?php endif; ?>

                            <?php if ($kyc['cni_verso']): ?>
                            <div class="document-card">
                                <h3>Verso CNI</h3>
                                <div class="document-viewer">
                                    <img src="<?php echo htmlspecialchars($kyc['cni_verso']); ?>" 
                                         alt="CNI Verso" 
                                         class="document-image"
                                         onclick="openImageModal(this.src)">
                                </div>
                                <a href="<?php echo htmlspecialchars($kyc['cni_verso']); ?>" 
                                   target="_blank" 
                                   class="btn-download">
                                    📥 Télécharger
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Adresse -->
                    <div class="info-section">
                        <h2>🏠 Adresse / Domicile</h2>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Pays:</span>
                                <span class="info-value"><?php echo htmlspecialchars($kyc['pays']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Ville:</span>
                                <span class="info-value"><?php echo htmlspecialchars($kyc['ville'] ?? 'Non renseigné'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Quartier:</span>
                                <span class="info-value"><?php echo htmlspecialchars($kyc['quartier'] ?? 'Non renseigné'); ?></span>
                            </div>
                        </div>
                        
                        <?php if ($kyc['adresse_complete']): ?>
                        <div class="info-item full-width">
                            <span class="info-label">Adresse complète:</span>
                            <span class="info-value"><?php echo nl2br(htmlspecialchars($kyc['adresse_complete'])); ?></span>
                        </div>
                        <?php endif; ?>

                        <?php if ($kyc['domicile_latitude'] && $kyc['domicile_longitude']): ?>
                        <div class="info-item full-width">
                            <span class="info-label">Coordonnées GPS:</span>
                            <span class="info-value">
                                📍 <?php echo $kyc['domicile_latitude']; ?>, <?php echo $kyc['domicile_longitude']; ?>
                                <a href="https://www.google.com/maps?q=<?php echo $kyc['domicile_latitude']; ?>,<?php echo $kyc['domicile_longitude']; ?>" 
                                   target="_blank" 
                                   class="btn-map">
                                    🗺️ Voir sur Google Maps
                                </a>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Commentaire admin existant -->
                    <?php if ($kyc['commentaire_admin']): ?>
                    <div class="info-section">
                        <h2>💬 Commentaire administrateur</h2>
                        <div class="comment-box">
                            <?php echo nl2br(htmlspecialchars($kyc['commentaire_admin'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Colonne droite: Actions -->
                <div class="detail-sidebar">
                    <div class="action-panel">
                        <h3>Actions</h3>
                        
                        <?php if ($kyc['statut'] !== 'approuve'): ?>
                        <!-- Formulaire Approuver -->
                        <form method="POST" class="action-form" onsubmit="return confirm('Êtes-vous sûr de vouloir approuver ce KYC ?');">
                            <input type="hidden" name="action" value="approuver">
                            <div class="form-group">
                                <label for="commentaire_approve">Commentaire (optionnel):</label>
                                <textarea name="commentaire" 
                                          id="commentaire_approve" 
                                          rows="3" 
                                          placeholder="Ajoutez un commentaire si nécessaire..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-approve">
                                <i class="fas fa-check-circle" style="color: green;"></i> Approuver le KYC
                            </button>
                        </form>
                        <?php endif; ?>

                        <?php if ($kyc['statut'] === 'soumis'): ?>
                        <!-- Formulaire Mettre en cours -->
                        <form method="POST" class="action-form">
                            <input type="hidden" name="action" value="en_cours">
                            <div class="form-group">
                                <label for="commentaire_progress">Commentaire (optionnel):</label>
                                <textarea name="commentaire" 
                                          id="commentaire_progress" 
                                          rows="3" 
                                          placeholder="Notez vos observations..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-progress">
                                 <i class="fas fa-spinner"></i> Mettre en cours de vérification
                            </button>
                        </form>
                        <?php endif; ?>

                        <?php if ($kyc['statut'] !== 'rejete'): ?>
                        <!-- Formulaire Rejeter -->
                        <form method="POST" class="action-form" onsubmit="return validateReject();">
                            <input type="hidden" name="action" value="rejeter">
                            <div class="form-group">
                                <label for="commentaire_reject">Motif du rejet <span class="required">*</span>:</label>
                                <textarea name="commentaire" 
                                          id="commentaire_reject" 
                                          rows="4" 
                                          required
                                          placeholder="Indiquez clairement le motif du rejet (document illisible, informations incohérentes, etc.)"></textarea>
                            </div>
                            <button type="submit" class="btn btn-reject">
                                 <i class="fas fa-times-circle" style="color: red;"></i> Rejeter le KYC
                            </button>
                        </form>
                        <?php endif; ?>

                        <!-- Informations utiles -->
                        <div class="info-box">
                            <h4>💡 Points de vérification</h4>
                            <ul>
                                <li>Photo du chauffeur claire et récente</li>
                                <li>CNI lisible et valide</li>
                                <li>Numéro CNI correspond au document</li>
                                <li>Date d'expiration non dépassée</li>
                                <li>Adresse cohérente avec la ville</li>
                                <li>Coordonnées GPS plausibles</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal pour agrandir les images -->
    <div id="imageModal" class="modal" onclick="closeImageModal()">
        <span class="modal-close">&times;</span>
        <img class="modal-content" id="modalImage">
    </div>

    <script>
        function openImageModal(src) {
            document.getElementById('imageModal').style.display = 'flex';
            document.getElementById('modalImage').src = src;
        }

        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
        }

        function validateReject() {
            const commentaire = document.getElementById('commentaire_reject').value.trim();
            if (commentaire.length < 10) {
                alert('Le motif du rejet doit contenir au moins 10 caractères.');
                return false;
            }
            return confirm('Êtes-vous sûr de vouloir rejeter ce KYC ?\n\nMotif: ' + commentaire);
        }

        // Fermer le modal avec Échap
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageModal();
            }
        });
    </script>
</body>
</html>