// Pour l'envoi sans fichiers
const headers = {
	'Dplus-fetch-api': 'Request_Fetch_Dplus',
	'Content-Type': 'application/x-www-form-urlencoded',
}

// Envoi de fichiers, nécessite de créer un formData
const headers2 = {
	'Dplus-fetch-api': 'Request_Fetch_Dplus'
}

function t(fr, en)
{
	return $fr;
}


let intE = -1,
	intS = -1;


// Afficher/Masquer le mot de passe
let allForms = document.querySelectorAll('form');
if (allForms.length > 0) {
	allForms.forEach((form, idx) => {
		let eyes = form.querySelectorAll('span.mio.eyes');
		eyes.forEach(eye => {
			eye.onclick = () => {
				if (eye.textContent.toLowerCase() == 'visibility') {
					eye.innerHTML = 'visibility_off';
					eye.parentElement.querySelector('input').setAttribute('type', 'text')
					eye.setAttribute('title', 'Masquer le mot de passe')
				} else {
					eye.innerHTML = 'visibility';
					eye.parentElement.querySelector('input').setAttribute('type', 'password')
					eye.setAttribute('title', 'Afficher le mot de passe')
				}
			}
		})
	})
}

/**
 * Cette fonction permet d'ajouter une animation de chargement sur un bouton de soumission d'un formulaire.
 * Elle désactive également tous les champs du formulaire pour éviter les interactions pendant le traitement.
 * @param {HTMLElement} btn Le bouton de soumission sur lequel l'animation doit être appliquée.
 * @param {string} [text='Vérification...'] Le texte à afficher à côté de l'animation de chargement.
 * @param {HTMLElement} [form=btn.parentElement] Le formulaire contenant le bouton.
 * @returns {string} Le texte précédent du bouton avant l'ajout de l'animation.
 */
function setAnimBtnSubForm(btn, text = 'Vérification...', form = btn.parentElement) {
	form.querySelectorAll('button, input, textarea, select').forEach(b => b.disabled = true);
	let prevText = btn.innerHTML;
	btn.innerHTML = `<img src="${img}load4.gif" alt="Loading..."> ` + text;

	return prevText;
}

// Tronquer texte (réutiliser fonction existante ou créer)
function truncateText(text, max) {
	return text.length > max ? text.substring(0, max) + '...' : text;
}

/**
 * Retourne l'heure actuelle au format HH:MM
 */
function obtenirHeureFormattee() {
	const maintenant = new Date();

	// Extraction et formatage avec un zéro initial si nécessaire
	const heures = String(maintenant.getHours()).padStart(2, '0');
	const minutes = String(maintenant.getMinutes()).padStart(2, '0');

	return `${heures}:${minutes}`;
}

/**
 * Met à jour l'élément HTML et configure l'intervalle
 */
function demarrerHorloge() {
	const affichageHeure = document.querySelector('.phones .heure');

	if (affichageHeure) {
		// 1. Appel immédiat pour éviter d'attendre la première minute
		affichageHeure.innerHTML = obtenirHeureFormattee();

		// 2. Mise à jour toutes les 1000 ms (1 seconde) pour garantir que l'heure change dès que les minutes changent
		setInterval(() => {
			affichageHeure.innerHTML = obtenirHeureFormattee();
		}, 1000);
	} else {
		console.error("L'élément '.phones .heure' est introuvable dans le DOM.");
	}
}

// Lancement au chargement de la page
// demarrerHorloge();

/**
 * Cette fonction permet de retirer l'animation de chargement d'un bouton de soumission d'un formulaire.
 * Elle réactive également tous les champs du formulaire.
 * @param {HTMLElement} btn Le bouton de soumission dont l'animation doit être retirée.
 * @param {string} prevText Le texte à restaurer sur le bouton après le retrait de l'animation.
 * @param {HTMLElement} [form=btn.parentElement] Le formulaire contenant le bouton.
 */
function removeAnimBtnSubForm(btn, prevText, form = btn.parentElement) {

	form.querySelectorAll('button, input, textarea, select').forEach(b => b.disabled = false);
	btn.innerHTML = prevText;
}


/**
 * Affiche une notification d'erreur dans l'interface utilisateur.
 * @param {string} i Le message d'erreur à afficher.
 * @param {number} j La durée en millisecondes avant de masquer le message.
 */
function openError(i, j = 5000) {
	let errorMess = document.querySelector(".errorMess");
	if (i != '') errorMess.querySelector(".mess").innerHTML = i;
	let successMess = document.querySelector(".successMess");
	if (intE != -1) {
		clearTimeout(intE);
		intE = -1;
		if (errorMess.classList.contains("active"))
			errorMess.classList.remove("active");
	}
	if (successMess.classList.contains("active"))
		successMess.classList.remove("active");
	if (!errorMess.classList.contains("active"))
		errorMess.classList.add("active");
	intE = setTimeout(() => {
		errorMess.classList.remove("active");
		errorMess.querySelector(".mess").innerHTML = '';
	}, j);
}

/**
 * Affiche une notification de succès dans l'interface utilisateur.
 * @param {string} i Le message de succès à afficher.
 * @param {number} j La durée en millisecondes avant de masquer le message.
 */
function openSuccess(i = '', j = 5000) {
	let successMess = document.querySelector(".successMess");
	if (i != '') successMess.querySelector(".mess").innerHTML = i;
	let errorMess = document.querySelector(".errorMess");
	if (intS != -1) {
		clearTimeout(intS);
		intS = -1;
		if (successMess.classList.contains("active"))
			successMess.classList.remove("active");
	}
	if (errorMess.classList.contains("active"))
		errorMess.classList.remove("active");
	if (!successMess.classList.contains("active"))
		successMess.classList.add("active");
	intS = setTimeout(() => {
		successMess.classList.remove("active");
		successMess.querySelector(".mess").innerHTML = '';
	}, j);
}

/**
 * Affiche une boîte de confirmation avec des messages personnalisés.
 * @param {number|null} i Le type de confirmation  ou null pour messages personnalisés.
 * @param {string|null} t Le titre personnalisé (si i est null).
 * @param {string|null} m Le message personnalisé (si i est null).
 * @param {boolean|null} s Si true, masque le bouton d'annulation.
 * @returns {Promise<boolean>} Résout avec true si confirmé, false sinon.
 */
function conf(i = 0, t = null, m = null, s = null) {

	if (document.querySelector('.notifChoice:not(.v2)') != null) {
		return new Promise((resolve) => {
			let titlePop = document.querySelector('.notifChoice h2');
			let messagePop = document.querySelector('.notifChoice .messageNotif');
			document.querySelector('.notifChoice .btns span:last-child').removeAttribute('style')
			if (t != null && m != null) {
				titlePop.innerHTML = t;
				messagePop.innerHTML = m;

				if (s) document.querySelector('.notifChoice .btns span:last-child').style.display = 'none';
			} else {
				if (i == 0) {
					titlePop.innerHTML = ""; // Le titre
					messagePop.innerHTML = ``; // Le message (accepte du contenu HTML)
				}
			}

			document.querySelector('.notifChoice').classList.add('active');

			/* if (i == 18) {
				document.querySelector('.notifChoice .btns span:first-child').innerHTML = "Je suis prêt·e";
				document.querySelector('.notifChoice .btns span:last-child').setAttribute('style', 'display: none')
			} else {
				document.querySelector('.notifChoice .btns span:first-child').innerHTML = "Oui";
				document.querySelector('.notifChoice .btns span:last-child').removeAttribute('style')
			} */
			document.querySelector('.notifChoice .btns span:first-child').onclick = () => {
				document.querySelector('.notifChoice').classList.remove('active');
				resolve(true);
			};
			document.querySelector('.notifChoice .btns span:last-child').onclick = () => {
				document.querySelector('.notifChoice').classList.remove('active');
				resolve(false);
			};
		})
	}

}

function slideAuth(target) {
	const slider = document.getElementById('authSlider');
	const title = document.getElementById('authTitle');

	if (target === 'signup') {
		slider.style.transform = 'translateX(-50%)';
		title.innerText = "Inscription";
	} else {
		slider.style.transform = 'translateX(0)';
		title.innerText = "Connexion";
	}
}

function toggleForgot(show) {
	const panel = document.getElementById('forgotSection');
	panel.classList.toggle('active', show);
}

// On attend que le DOM soit chargé
const formLogin = document.getElementById('formLogin');
const formSignup = document.getElementById('formSignup');

// --- LOGIQUE DE CONNEXION ---
if (formLogin) {
	formLogin.addEventListener('submit', (e) => {
		e.preventDefault();

		const email = formLogin.querySelector('input[name="email"]').value.trim();
		const pass = formLogin.querySelector('input[name="password"]').value;

		const submitBtn = formSignup.querySelector('button[type=submit]')

		// Validation simple
		if (!email || !pass) {
			openError("Veuillez remplir tous les champs de connexion.");
			return;
		}

		// Envoi des données
		submitAuth(`${auth}login.php`, {
			email: email,
			password: pass
		}, "login", submitBtn, formLogin);
	});
}

// --- LOGIQUE D'INSCRIPTION ---
if (formSignup) {
	formSignup.addEventListener('submit', (e) => {
		e.preventDefault();

		const nom = document.getElementById('names').value.trim();
		const email = formSignup.querySelector('input[name="email"]').value.trim();
		const pass = document.getElementById('password').value;

		const submitBtn = formSignup.querySelector('button[type=submit]')

		// Validations demandées
		if (nom.length < 3) {
			openError("Le nom doit contenir au moins 3 caractères.");
			return;
		}

		if (email == '') {
			openError("Veuillez entrer une adresse email valide.");
			return;
		}

		if (pass.length < 6) {
			openError("Le mot de passe doit contenir au moins 6 caractères.");
			return;
		}

		// Envoi des données
		submitAuth(`${auth}register.php`, {
			nom: nom,
			email: email,
			password: pass
		}, "register", submitBtn, formSignup);
	});
}

/**
 * Fonction générique pour l'envoi AJAX (Fetch)
 */
function submitAuth(endpoint, data, type, button, form) {
	let prevText = setAnimBtnSubForm(button, "Traitement...", form)

	fetch(endpoint, {
			method: 'POST',
			headers: headers,
			body: new URLSearchParams(data)
		})
		.then(r => r.json())
		.then(res => {
			removeAnimBtnSubForm(button, prevText, form);

			if (res.code == 0) {
				openSuccess(res.message || "Opération réussie !", 3000);

				// Redirection ou action après succès
				setTimeout(() => {
					if (type === "login") location.href = ``;
					else location.reload();
				}, 2000);

			} else {
				openError(res.message || "Une erreur est survenue.", 5000);
			}
		})
		.catch(err => {
			removeAnimBtnSubForm(button, prevText, form);
			console.error(err);
			openError(t("Erreur réseau. Impossible de joindre le serveur.", "Network error. Unable to reach server."));
		});
}

/**
 * Helper: Validation Email
 */

/**
 * Logique Mot de passe oublié (Etape 1)
 */
function verifyForgot() {
	const email = document.getElementById('forgotInput').value.trim();
	const emailDisplay = document.querySelector('.emailAdress');

	if (email == '') {
		openError(t("Veuillez entrer un email valide pour la récupération.", "Please enter a valid recovery email."));
		return;
	}

	if (typeof loadding2 === "function") loadding2(0);

	// Simulation d'envoi de code
	fetch(`${auth}forgot.php`, {
			method: 'POST',
			body: new URLSearchParams({
				email: email,
				action: 'sendCode'
			})
		})
		.then(r => r.json())
		.then(res => {
			if (typeof loadding2 === "function") loadding2(1);

			if (res.code == 0) {
				emailDisplay.innerText = email;
				document.getElementById('forgotStep1').style.display = 'none';
				document.getElementById('forgotStep2').style.display = 'block';
				openSuccess(t("Code envoyé avec succès !", "Code sent successfully!"));
			} else {
				openError(res.message);
			}
		});
}

function verifyForgot() {
	// Simulation de vérification
	document.getElementById('forgotStep1').style.display = 'none';
	document.getElementById('forgotStep2').style.display = 'block';
}

/**
 * Affiche une boîte de confirmation de mot de passe avec des messages personnalisés.
 * @param {number} i Le type de confirmation de mot de passe.
 * @param {string|null} t Le titre personnalisé.
 * @param {string|null} m Le message personnalisé.
 * @param {string} label Le label pour le champ de mot de passe.
 * @param {string} type Le type d'entrée (par défaut 'password').
 * @returns {Promise<string|null>} Résout avec le mot de passe saisi ou null si annulé.
 */
function confPass(i = 0, t = null, m = null, label = 'Mot de passe :', type = 'password') {
	let box = document.querySelector('.notifChoice.v2');
	if (box != null) {
		return new Promise((resolve) => {
			let titlePop = document.querySelector('.notifChoice.v2 h2');
			let messagePop = document.querySelector('.notifChoice.v2 .messageNotif');
			let labelTag = document.querySelector('.notifChoice.v2 label');
			let input = document.querySelector('.notifChoice.v2 input');
			let spanMio = document.querySelector('.notifChoice.v2 span.mio');

			if (type.toLocaleLowerCase() == 'password') spanMio.removeAttribute('style');
			else spanMio.style.display = 'none';

			input.setAttribute('type', type);
			input.focus();
			labelTag.innerHTML = label;
			if (t != null && m != null) {
				titlePop.innerHTML = t.trim();
				messagePop.innerHTML = m.trim();
			} else {
				// Message récurrent pour les différentes confirmations de mot de passe, d'où l'utilisation de i pour différencier les cas et afficher le message approprié
				if (i == 0) {
					titlePop.innerHTML = ""; // Le titre
					messagePop.innerHTML = ``; // Le Message
				}
			}

			let form = box.querySelector('form')
			form.onsubmit = (s) => {
				s.preventDefault();
				box.querySelector('.btns span:first-child').click();
			}

			box.classList.add('active');
			input.click()
			input.focus()
			let validation = box.querySelector('.btns:not(.fl) span:first-child')
			let annulation = box.querySelector('.btns:not(.fl) span:last-child')
			validation.onclick = () => {
				let pass = form.querySelector('#ccpass');
				if (pass.value.trim() == '') {
					openError("Bien vouloir renseigner ce champ pour confirmer cette action !")
					pass.focus();
					return false;
				}
				let p = pass.value.trim()
				form.reset()
				box.classList.remove('active')
				resolve(p);
			};
			annulation.onclick = () => {
				form.reset()
				box.classList.remove('active');
				resolve(false);
			};
		})
	}

}

/**
 * Affiche une notification d'erreur d'autorisation dans l'interface utilisateur avec un message prédéfini.
 */
function openErrorAuth() {
	openError("Vous n'avez pas l'autorisation de faire cette action !")
}


function miniLoadder(element, insertInto = "beforeend", stop = false) {
	if (element != null) {
		if (stop) {
			let load = element.querySelectorAll('.miniLoader');
			if (load.length > 0) load.forEach(l => l.remove());
			return;
		}
		let load = `<div class="miniLoader">
					<img src="${img}load3.gif">
					<p class="info">Chargement des ressources en cours...<br>Veuillez patienter !</p>
				</div>`;

		element.insertAdjacentHTML(insertInto, load);
	}
}