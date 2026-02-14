<?php
session_start();

// Vérifier que l'utilisateur est admin
if (!isset($_SESSION['id'])) {
    header('Location: ./login');
    exit;
}

require_once '../inc/main.php';

// Récupérer les grilles tarifaires
$grilles_sql = "SELECT * FROM grille_tarifs ORDER BY dist_min ASC";
$grilles = $bdd->query($grilles_sql)->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les plans
$plans_sql = "SELECT * FROM plans_facteurs ORDER BY position ASC";
$plans = $bdd->query($plans_sql)->fetchAll(PDO::FETCH_ASSOC);

// Calculer les statistiques
$stats_grilles = [
    'total' => count($grilles),
    'actives' => count(array_filter($grilles, fn($g) => $g['actif'] == 1)),
    'inactives' => count(array_filter($grilles, fn($g) => $g['actif'] == 0))
];

$stats_plans = [
    'total' => count($plans),
    'actifs' => count(array_filter($plans, fn($p) => $p['actif'] == 1)),
    'inactifs' => count(array_filter($plans, fn($p) => $p['actif'] == 0))
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de la Tarification - TransportCM</title>
    <link rel="stylesheet" href="<?= $css ?>polices.css">
</head>
<style>
    /* ===================================
   IMPORT VARIABLES FROM KYC STYLES
   =================================== */
:root {
    --primary-color: #0fdb97;
    --primary-dark: #033f0b;
    --primary-light: #046812;
    --success-color: #10b981;
    --warning-color: #f5cb17;
    --danger-color: #ef4444;
    --info-color: #06b6d4;
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
    
    /* Pricing specific colors */
    --pricing-accent: #8b5cf6;
    --pricing-gradient-start: #6366f1;
    --pricing-gradient-end: #8b5cf6;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-size: 14px;
    font-family: 'Po02', sans-serif;
    line-height: 1.5;
    color: var(--gray-800);
    background-color: var(--gray-50);
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

/* ===================================
   LAYOUT (Reuse from KYC styles)
   =================================== */
.dashboard-container {
   
    width: 100%;
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
    background: rgba(99, 102, 241, 0.15);
    color: var(--white);
    border-left-color: var(--primary-color);
}

.main-content {
    flex: 1;
    margin-left: 260px;
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
   STATISTICS
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
    cursor: pointer;
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

.stat-grilles { border-left: 1px solid var(--primary-color); }
.stat-plans { border-left: 1px solid var(--pricing-accent); }
.stat-calculator { border-left: 1px solid var(--info-color); }

/* ===================================
   TABS
   =================================== */
.tabs-container {
    margin-bottom: 2rem;
}

.tabs {
    display: flex;
    gap: 0.5rem;
    background: var(--white);
    padding: 0.5rem;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
}

.tab-btn {
    flex: 1;
    padding: 0.875rem 1.5rem;
    background: transparent;
    border: none;
    border-radius: var(--radius);
    font-size: 0.9375rem;
    font-weight: 600;
    color: var(--gray-600);
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.tab-btn:hover {
    background: var(--gray-100);
    color: var(--gray-900);
}

.tab-btn.active {
    background: var(--warning-color);
    color: var(--white);
    box-shadow: var(--shadow-md);
}

.tab-content {
    display: none;
    animation: fadeIn 0.3s ease-in;
}

.tab-content.active {
    display: block;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ===================================
   SECTION HEADER
   =================================== */
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.section-header h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--gray-900);
}

/* ===================================
   TABLE CONTAINER
   =================================== */
.table-container {
    background: var(--white);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.pricing-table {
    width: 100%;
    border-collapse: collapse;
}

.pricing-table thead {
    background: var(--warning-color);
    color: var(--white);
}

.pricing-table th {
    padding: 1.25rem 1rem;
    text-align: left;
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.pricing-table td {
    padding: 1.25rem 1rem;
    font-size: 0.875rem;
}

.pricing-table tbody tr {
    transition: background-color 0.2s;
}

.pricing-table tbody tr:hover {
    background-color: var(--gray-50);
}

.pricing-table tbody tr:last-child td {
    border-bottom: none;
}

.inactive-row {
    opacity: 0.6;
}

/* ===================================
   TABLE BADGES & ELEMENTS
   =================================== */
.id-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.625rem;
    background: var(--gray-100);
    border-radius: var(--radius);
    font-weight: 600;
    color: var(--gray-700);
    font-size: 0.8125rem;
}

.distance-range {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.range-label {
    font-weight: 600;
    color: var(--gray-900);
    font-family: 'Courier New', monospace;
}

.distance-info {
    color: var(--gray-600);
    font-size: 0.8125rem;
}

.price-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #10b981, #059669);
    color: var(--white);
    border-radius: var(--radius-lg);
    font-weight: 700;
    font-size: 1rem;
    box-shadow: var(--shadow-sm);
}

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

.status-active {
    background: #d1fae5;
    color: #065f46;
}

.status-inactive {
    background: #fee2e2;
    color: #991b1b;
}

/* ===================================
   ACTION BUTTONS
   =================================== */
.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn-icon {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: none;
    border-radius: var(--radius);
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.2s;
    background: var(--gray-100);
}

.btn-icon:hover {
    transform: scale(1.1);
    box-shadow: var(--shadow-md);
}

.btn-edit:hover {
    background: #dbeafe;
}

.btn-toggle:hover {
    background: #fef3c7;
}

.btn-delete:hover {
    background: #fee2e2;
}

/* ===================================
   BUTTONS
   =================================== */
.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: var(--radius);
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    text-decoration: none;
}

.btn-primary {
    background: var(--warning-color);
    color: var(--white);
    box-shadow: var(--shadow-sm);
}

.btn-primary:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-1px);
}

.btn-secondary {
    background: var(--gray-200);
    color: var(--gray-800);
}

.btn-secondary:hover {
    background: var(--gray-300);
}

.btn-outline {
    background: transparent;
    border: 2px solid var(--gray-300);
    color: var(--gray-700);
}

.btn-outline:hover {
    border-color: var(--primary-color);
    color: var(--primary-color);
}

.btn-danger {
    background: var(--danger-color);
    color: var(--white);
}

.btn-danger:hover {
    background: #dc2626;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.8125rem;
}

.btn-large {
    padding: 1rem 2rem;
    font-size: 1rem;
}

/* ===================================
   PLANS GRID
   =================================== */
.plans-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
}

.plan-card {
    background: var(--prim);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow);
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
    border: 2px solid transparent;
}

.plan-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
    border-color: var(--primary-light);
}

.plan-inactive {
    opacity: 0.6;
}

.plan-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    background: var(--warning-color);
    color: var(--white);
}

.plan-position {
    font-size: 0.875rem;
    font-weight: 700;
    background: rgba(255, 255, 255, 0.2);
    padding: 0.25rem 0.625rem;
    border-radius: var(--radius);
}

.plan-body {
    padding: 1.5rem;
}

.plan-name {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--gray-900);
    margin-bottom: 0.5rem;
}

.plan-slug {
    font-size: 0.8125rem;
    color: var(--gray-500);
    font-family: 'Courier New', monospace;
    margin-bottom: 1.5rem;
}

.plan-factor {
    background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
    padding: 1.25rem;
    border-radius: var(--radius-lg);
    margin-bottom: 1rem;
    border: 2px solid #bae6fd;
}

.factor-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--gray-600);
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.factor-value {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--primary-color);
    font-family: 'Courier New', monospace;
}

.plan-example {
    padding: 1rem;
    background: var(--gray-50);
    border-radius: var(--radius);
    border-left: 4px solid var(--success-color);
}

.example-label {
    font-size: 0.75rem;
    color: var(--gray-600);
    margin-bottom: 0.75rem;
    font-weight: 600;
}

.example-calc {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    font-size: 0.875rem;
}

.base-price {
    font-weight: 600;
    color: var(--gray-700);
}

.multiply, .equals {
    color: var(--gray-500);
    font-weight: 700;
}

.factor {
    font-weight: 700;
    color: var(--primary-color);
}

.final-price {
    font-weight: 700;
    color: var(--success-color);
    font-size: 1rem;
}

.plan-actions {
    padding: 1rem 1.5rem;
    background: var(--gray-50);
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

/* ===================================
   SIMULATOR
   =================================== */
.simulator-container {
    background: var(--white);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-lg);
    overflow: hidden;
}

.simulator-header {
    background: var(--warning-color);
    color: var(--white);
    padding: 2rem;
    text-align: center;
}

.simulator-header h2 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.simulator-header p {
    font-size: 1rem;
    opacity: 0.9;
}

.simulator-body {
    padding: 2rem;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

.simulator-inputs {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.simulator-result {
    background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
    border-radius: var(--radius-lg);
    padding: 2rem;
    border: 1px solid #bae6fd;
}

.result-header h3 {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--gray-900);
    margin-bottom: 1.5rem;
}

.result-steps {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.result-step {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: var(--white);
    border-radius: var(--radius);
}

.step-label {
    font-size: 0.875rem;
    color: var(--gray-600);
    font-weight: 600;
}

.step-value {
    font-size: 0.9375rem;
    color: var(--gray-900);
    font-weight: 700;
}

.result-divider {
    height: 2px;
    background: linear-gradient(90deg, transparent, var(--primary-color), transparent);
    margin: 0.5rem 0;
}

.result-final {
    background: linear-gradient(135deg, var(--success-color), #059669);
    color: var(--white);
    padding: 1.5rem;
    border-radius: var(--radius-lg);
    text-align: center;
    box-shadow: var(--shadow-md);
}

.final-label {
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    margin-bottom: 0.5rem;
    opacity: 0.9;
}

.final-value {
    font-size: 2.5rem;
    font-weight: 700;
    font-family: 'Courier New', monospace;
}

.result-formula {
    margin-top: 1.5rem;
    padding: 1rem;
    background: var(--white);
    border-radius: var(--radius);
    border: 1px solid var(--gray-300);
}

.formula-line {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    font-size: 1.125rem;
}

.formula-part {
    font-weight: 600;
    color: var(--gray-700);
}

.formula-operator {
    color: var(--gray-500);
    font-weight: 700;
}

.formula-result {
    font-weight: 700;
    color: var(--success-color);
    font-size: 1.5rem;
}

/* ===================================
   FORMS
   =================================== */
.input-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.input-group label {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--gray-700);
}

.required {
    color: var(--danger-color);
}

.form-input,
.form-select {
    padding: 0.75rem 1rem;
    border: 1px solid var(--gray-300);
    border-radius: var(--radius);
    font-size: 0.9375rem;
    font-family: inherit;
    transition: all 0.2s;
}

.form-input:focus,
.form-select:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.input-hint {
    font-size: 0.75rem;
    color: var(--gray-500);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.checkbox-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    font-size: 0.9375rem;
    color: var(--gray-700);
}

.checkbox-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

/* ===================================
   MODAL
   =================================== */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(4px);
}

.modal-content {
    background: var(--white);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-xl);
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem 2rem;
    background: var(--warning-color);
    color: var(--white);
}

.modal-header h3 {
    font-size: 1.25rem;
    font-weight: 700;
}

.modal-close {
    background: none;
    border: none;
    color: var(--white);
    font-size: 2rem;
    cursor: pointer;
    line-height: 1;
    opacity: 0.8;
    transition: opacity 0.2s;
}

.modal-close:hover {
    opacity: 1;
}

.modal-content form {
    padding: 2rem;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.modal-actions {
    display: flex;
    gap: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--gray-200);
}

.modal-actions .btn {
    flex: 1;
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
    margin-bottom: 1.5rem;
}

/* ===================================
   RESPONSIVE
   =================================== */
@media (max-width: 1200px) {
    .simulator-body {
        grid-template-columns: 1fr;
    }
    
    .plans-grid {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    }
}

@media (max-width: 768px) {
    .sidebar {
        width: 100%;
        position: static;
        height: auto;
    }
    
    .main-content {
        margin-left: 0;
        max-width: 100%;
        padding: 1rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .tabs {
        flex-direction: column;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .pricing-table {
        display: block;
        overflow-x: auto;
    }
    
    .plans-grid {
        grid-template-columns: 1fr;
    }
    
    .page-header {
        flex-direction: column;
        gap: 1rem;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    .main{
        width: 100%;
    }
    .simulator-body {
        padding: 1rem;
    }
}
</style>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
       

        <!-- Main Content -->
        <main class="main">
            <header class="page-header">
                <div>
                    <h1>Gestion de la Tarification</h1>
                    <p class="subtitle">Configurez les grilles tarifaires et les plans de prix</p>
                </div>
               
            </header>

            <!-- Statistiques -->
            <div class="stats-grid">
                <div class="stat-card stat-grilles">
                    <div class="stat-icon">📏</div>
                    <div class="stat-info">
                        <h3><?php echo $stats_grilles['actives']; ?>/<?php echo $stats_grilles['total']; ?></h3>
                        <p>Tranches actives</p>
                    </div>
                </div>
                <div class="stat-card stat-plans">
                    <div class="stat-icon">🎯</div>
                    <div class="stat-info">
                        <h3><?php echo $stats_plans['actifs']; ?>/<?php echo $stats_plans['total']; ?></h3>
                        <p>Plans actifs</p>
                    </div>
                </div>
                <div class="stat-card stat-calculator">
                    <div class="stat-icon">🧮</div>
                    <div class="stat-info">
                        <h3>Calculer</h3>
                        <p>Simulateur de tarif</p>
                    </div>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <div class="tabs-container">
                <div class="tabs">
                    <button class="tab-btn active" onclick="switchTab('grilles')">
                        📏 Grilles Tarifaires
                    </button>
                    <button class="tab-btn" onclick="switchTab('plans')">
                        🎯 Plans & Facteurs
                    </button>
                    <button class="tab-btn" onclick="switchTab('simulator')">
                        🧮 Simulateur
                    </button>
                </div>
            </div>

            <!-- TAB 1: GRILLES TARIFAIRES -->
            <div id="tab-grilles" class="tab-content active">
                <div class="section-header">
                    <h2>📏 Grille Tarifaire Principale</h2>
                    <button class="btn btn-primary" onclick="openGrilleModal()">
                        ➕ Ajouter une tranche
                    </button>
                </div>

                <div class="table-container">
                    <?php if (empty($grilles)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">📭</div>
                            <h3>Aucune tranche tarifaire</h3>
                            <p>Commencez par ajouter votre première tranche de tarification.</p>
                            <button class="btn btn-primary" onclick="openGrilleModal()">
                                ➕ Ajouter une tranche
                            </button>
                        </div>
                    <?php else: ?>
                        <table class="pricing-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tranche de distance</th>
                                    <th>Distance (m)</th>
                                    <th>Prix de base (FCFA)</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($grilles as $grille): ?>
                                    <tr class="table-row <?php echo $grille['actif'] ? '' : 'inactive-row'; ?>">
                                        <td><span class="id-badge">#<?php echo $grille['id']; ?></span></td>
                                        <td>
                                            <div class="distance-range">
                                                <span class="range-label">
                                                    <?php echo number_format($grille['dist_min']); ?> m 
                                                    → 
                                                    <?php echo number_format($grille['dist_max']); ?> m
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="distance-info">
                                                <?php 
                                                $diff = $grille['dist_max'] - $grille['dist_min'] + 1;
                                                echo number_format($diff) . ' m';
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="price-badge">
                                                <?php echo number_format($grille['prix_base'] ); ?> FCFA
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($grille['actif']): ?>
                                                <span class="status-badge status-active">✅ Actif</span>
                                            <?php else: ?>
                                                <span class="status-badge status-inactive">⭕ Inactif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-icon btn-edit" 
                                                        onclick="editGrille(<?php echo htmlspecialchars(json_encode($grille)); ?>)"
                                                        title="Modifier">
                                                    ✏️
                                                </button>
                                                <button class="btn-icon btn-toggle" 
                                                        onclick="toggleGrilleStatus(<?php echo $grille['id']; ?>, <?php echo $grille['actif'] ? 0 : 1; ?>)"
                                                        title="<?php echo $grille['actif'] ? 'Désactiver' : 'Activer'; ?>">
                                                    <?php echo $grille['actif'] ? '🔴' : '🟢'; ?>
                                                </button>
                                                <button class="btn-icon " 
                                                        onclick="deleteGrille(<?php echo $grille['id']; ?>)"
                                                        title="Supprimer">
                                                    🗑️
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TAB 2: PLANS & FACTEURS -->
            <div id="tab-plans" class="tab-content">
                <div class="section-header">
                    <h2>🎯 Plans Tarifaires</h2>
                    <button class="btn btn-primary" onclick="openPlanModal()">
                        ➕ Ajouter un plan
                    </button>
                </div>

                <div class="plans-grid">
                    <?php if (empty($plans)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">📭</div>
                            <h3>Aucun plan tarifaire</h3>
                            <p>Créez votre premier plan de tarification.</p>
                            <button class="btn btn-primary" onclick="openPlanModal()">
                                ➕ Ajouter un plan
                            </button>
                        </div>
                    <?php else: ?>
                        <?php foreach ($plans as $plan): ?>
                            <div class="plan-card <?php echo $plan['actif'] ? '' : 'plan-inactive'; ?>">
                                <div class="plan-header">
                                    <div class="plan-position">#{<?php echo $plan['position']; ?>}</div>
                                    <div class="plan-status">
                                        <?php if ($plan['actif']): ?>
                                            <span class="status-badge status-active">✅ Actif</span>
                                        <?php else: ?>
                                            <span class="status-badge status-inactive">⭕ Inactif</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="plan-body">
                                    <h3 class="plan-name"><?php echo htmlspecialchars($plan['nom_plan']); ?></h3>
                                    <div class="plan-slug"><?php echo htmlspecialchars($plan['slug']); ?></div>
                                    
                                    <div class="plan-factor">
                                        <div class="factor-label">Facteur multiplicateur</div>
                                        <div class="factor-value">× <?php echo $plan['facteur']; ?></div>
                                    </div>

                                    <div class="plan-example">
                                        <div class="example-label">Exemple: Tranche 200-1200m</div>
                                        <div class="example-calc">
                                            <span class="base-price">200 FCFA</span>
                                            <span class="multiply">×</span>
                                            <span class="factor"><?php echo $plan['facteur']; ?></span>
                                            <span class="equals">=</span>
                                            <span class="final-price"><?php echo number_format(200 * $plan['facteur']); ?> FCFA</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="plan-actions">
                                    <button class="btn btn-secondary btn-sm" 
                                            onclick="editPlan(<?php echo htmlspecialchars(json_encode($plan)); ?>)">
                                        ✏️ Modifier
                                    </button>
                                    <button class="btn btn-outline btn-sm" 
                                            onclick="togglePlanStatus(<?php echo $plan['id']; ?>, <?php echo $plan['actif'] ? 0 : 1; ?>)">
                                        <?php echo $plan['actif'] ? '🔴 Désactiver' : '🟢 Activer'; ?>
                                    </button>
                                    <button class="btn btn-danger btn-sm" 
                                            onclick="deletePlan(<?php echo $plan['id']; ?>)">
                                        🗑️ Supprimer
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TAB 3: SIMULATEUR -->
            <div id="tab-simulator" class="tab-content">
                <div class="simulator-container">
                    <div class="">
                        <h2>🧮 Simulateur de Tarif</h2>
                        <p>Testez vos tarifs en temps réel</p>
                    </div>

                    <div class="simulator-body">
                        <div class="simulator-inputs">
                            <div class="input-group">
                                <label for="sim-distance">Distance du trajet (en mètres)</label>
                                <input type="number" 
                                       id="sim-distance" 
                                       class="form-input" 
                                       placeholder="Ex: 3500"
                                       min="0"
                                       step="1">
                                <div class="input-hint">Entrez la distance en mètres</div>
                            </div>

                            <div class="input-group">
                                <label for="sim-plan">Plan tarifaire</label>
                                <select id="sim-plan" class="form-select">
                                    <option value="">-- Sélectionnez un plan --</option>
                                    <?php foreach ($plans as $plan): ?>
                                        <?php if ($plan['actif']): ?>
                                            <option value="<?php echo $plan['slug']; ?>" data-facteur="<?php echo $plan['facteur']; ?>">
                                                <?php echo htmlspecialchars($plan['nom_plan']); ?> (× <?php echo $plan['facteur']; ?>)
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <button class="btn btn-primary btn-large" onclick="calculatePrice()">
                                🧮 Calculer le tarif
                            </button>
                        </div>

                        <div class="simulator-result" id="simulator-result" style="display: none;">
                            <div class="result-header">
                                <h3>💰 Résultat du calcul</h3>
                            </div>
                            
                            <div class="result-steps">
                                <div class="result-step">
                                    <div class="step-label">📏 Tranche trouvée</div>
                                    <div class="step-value" id="result-tranche">-</div>
                                </div>
                                
                                <div class="result-step">
                                    <div class="step-label">💵 Prix de base</div>
                                    <div class="step-value" id="result-base">-</div>
                                </div>
                                
                                <div class="result-step">
                                    <div class="step-label">🎯 Facteur du plan</div>
                                    <div class="step-value" id="result-facteur">-</div>
                                </div>
                                
                                <div class="result-divider"></div>
                                
                                <div class="result-final">
                                    <div class="final-label">Prix final</div>
                                    <div class="final-value" id="result-prix">0 FCFA</div>
                                </div>
                            </div>

                            <div class="result-formula" id="result-formula">
                                <!-- Formula will be inserted here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Grille -->
    <div id="grilleModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="grilleModalTitle">Ajouter une tranche tarifaire</h3>
                <button class="modal-close" onclick="closeGrilleModal()">&times;</button>
            </div>
            <form id="grilleForm" onsubmit="saveGrille(event)">
                <input type="hidden" id="grille-id" name="id">
                
                <div class="form-row">
                    <div class="input-group">
                        <label for="grille-dist-min">Distance minimale (m) <span class="required">*</span></label>
                        <input type="number" 
                               id="grille-dist-min" 
                               name="dist_min" 
                               class="form-input" 
                               required
                               min="0"
                               step="1">
                    </div>

                    <div class="input-group">
                        <label for="grille-dist-max">Distance maximale (m) <span class="required">*</span></label>
                        <input type="number" 
                               id="grille-dist-max" 
                               name="dist_max" 
                               class="form-input" 
                               required
                               min="1"
                               step="1">
                    </div>
                </div>

                <div class="input-group">
                    <label for="grille-prix">Prix de base (FCFA) <span class="required">*</span></label>
                    <input type="number" 
                           id="grille-prix" 
                           name="prix_base" 
                           class="form-input" 
                           required
                           min="1"
                           step="1">
                    <div class="input-hint">Prix en Francs CFA</div>
                </div>

                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" 
                               id="grille-actif" 
                               name="actif" 
                               checked>
                        <span>Tranche active</span>
                    </label>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeGrilleModal()">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        💾 Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Plan -->
    <div id="planModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="planModalTitle">Ajouter un plan tarifaire</h3>
                <button class="modal-close" onclick="closePlanModal()">&times;</button>
            </div>
            <form id="planForm" onsubmit="savePlan(event)">
                <input type="hidden" id="plan-id" name="id">
                
                <div class="input-group">
                    <label for="plan-nom">Nom du plan <span class="required">*</span></label>
                    <input type="text" 
                           id="plan-nom" 
                           name="nom_plan" 
                           class="form-input" 
                           required
                           placeholder="Ex: Classique, Prestige, VIP...">
                </div>

                <div class="input-group">
                    <label for="plan-slug">Identifiant (slug) <span class="required">*</span></label>
                    <input type="text" 
                           id="plan-slug" 
                           name="slug" 
                           class="form-input" 
                           required
                           pattern="[a-z0-9_-]+"
                           placeholder="Ex: classique, prestige_plus...">
                    <div class="input-hint">Lettres minuscules, chiffres, tirets et underscores uniquement</div>
                </div>

                <div class="form-row">
                    <div class="input-group">
                        <label for="plan-facteur">Facteur multiplicateur <span class="required">*</span></label>
                        <input type="number" 
                               id="plan-facteur" 
                               name="facteur" 
                               class="form-input" 
                               required
                               min="0.01"
                               step="0.01"
                               value="1.00">
                        <div class="input-hint">Ex: 1.00 = prix normal, 1.50 = +50%</div>
                    </div>

                    <div class="input-group">
                        <label for="plan-position">Position d'affichage <span class="required">*</span></label>
                        <input type="number" 
                               id="plan-position" 
                               name="position" 
                               class="form-input" 
                               required
                               min="1"
                               step="1"
                               value="1">
                    </div>
                </div>

                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" 
                               id="plan-actif" 
                               name="actif" 
                               checked>
                        <span>Plan actif</span>
                    </label>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closePlanModal()">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        💾 Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
    
</body>
</html>