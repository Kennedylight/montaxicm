document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('login-form');
  const errorBox = document.getElementById('error-box');
  const errorText = document.getElementById('error-text');
  const submitBtn = document.getElementById('submit-btn');
  // Toggle visibilité mot de passe
  // document.getElementById('toggle-password').addEventListener('click', (e) => {
  //   const input = document.getElementById('password');
  //   const type = input.type === 'password' ? 'text' : 'password';
  //   input.type = type;
  //   e.target.classList.toggle('fa-eye');
  //   e.target.classList.toggle('fa-eye-slash');
  // });


  
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
  console.log("Login JS chargé avec succès !");
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();

    errorBox.style.display = 'none';
    const formData = new FormData(form);
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Connexion...';

    try {
            const response = await fetch('../auth/login_process.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // Option : petite animation de succès
                errorBox.className = 'success-message'; // tu peux créer une classe verte
                errorBox.style.display = 'flex';
                errorText.textContent = result.message || 'Connexion réussie...';

                // Redirection après 800ms pour laisser voir le message
                setTimeout(() => {
                    window.location.href = '/';
                }, 800);
            } else {
                // Erreur
                errorText.textContent = result.message || 'Erreur lors de la connexion';
                errorBox.style.display = 'flex';
            }
        } catch (err) {
            console.error(err);
            errorText.textContent = 'Erreur réseau ou serveur indisponible';
            errorBox.style.display = 'flex';
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Se connecter';
        }
  
  });
});