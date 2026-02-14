<?php
include('../inc/main.php');
?>

<style>
  body {
    font-family: 'Po02', sans-serif;
  }
  :root {
    --secondary: #047454;
    --danger: #ef4444;
    --dark: #111;
    --gray: #666;
  }
  /* Cards utilisateurs */
  .users-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
  }

  .user-card {
    background: white;
    border-radius: 14px;
    border: 1px solid #0001;
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.09);
    padding: 20px;
    transition: transform 0.2s;
  }

  .user-card:hover {
    transform: translateY(-5px);
  }

  .user-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 16px;
    position: relative;
  }

  .user-image {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--primary);
  }

  .user-basic h3 {
    font-size: 1.2rem;
    color: var(--dark);
    margin-bottom: 4px;
  }

  .user-status {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    display: inline-block;
  }

  .user-status.actif {
    background: rgba(4, 116, 84, 0.15);
    color: var(--secondary);
  }

  .user-status.bloqué {
    background: rgba(239, 68, 68, 0.15);
    color: var(--danger);
  }

  .dots-menu {
    position: absolute;
    right: 0;
    cursor: pointer;
    color: var(--gray);
    font-size: 1.2rem;
  }

  .user-info p {
    font-size: 0.95rem;
    margin-bottom: 8px;
    color: var(--gray);
  }

  .user-info p i {
    margin-right: 10px;
    color: var(--primary);
    width: 16px;
  }

  /* Modal */
  .modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2000;
    padding: 20px;
  }

  .modal-content {
    background: white;
    border-radius: 16px;
    max-width: 480px;
    width: 100%;
    padding: 24px;
    position: relative;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.25);
  }

  .modal-close {
    position: absolute;
    top: 16px;
    right: 16px;
    cursor: pointer;
    font-weight: bold;
    color: var(--gray);
    font-size: 22px;
    transition: color 0.2s;
    
  }

  .modal-close:hover {
    color: var(--danger);
  }

  .modal-content h2 {
    margin-bottom: 20px;
    color: var(--dark);
  }

  .modal-body p {
    margin-bottom: 12px;
    font-size: 0.95rem;
    color: black;
  }

  .modal-body p strong {
    color: var(--primary);
    margin-right: 8px;
  }

  .modal-actions {
    display: flex;
    gap: 16px;
    margin-top: 24px;
    justify-content: flex-end;
  }

  .btn-block,
  .btn-delete {
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.2s;
  }

  input {
    width: 50%;
    padding: 10px;
    border-radius: 100px;
    border: solid 1px #0001;
    margin-bottom: 25px;
    margin-top: 10px;
  }

  .btn-block {
    background: var(--primary);
    color: white;
  }

  .btn-block:hover {
    background: #e0b814;
  }

  .btn-delete {
    background: var(--danger);
    color: white;
    text-decoration: none;
  }

  .btn-delete:hover {
    background: #dc2626;
  }
</style>


  <!-- users/index.php – Gestion des utilisateurs (simulation front avec cards et modal) -->

  <div class="users-page">
    <h1>Gestion des Utilisateurs</h1>
    <div class="actions-bar">
      <div class="search-wrapper">
        <input type="text" id="search-users" placeholder="Rechercher par nom, email, téléphone...">

      </div>


    </div>
    <div class="users-grid">
      
    </div>
  
  </div>



