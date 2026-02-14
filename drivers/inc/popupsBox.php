<!-- Box pour confirmer une action par un OUI ou NON -->
<div class="popups notifChoice">
	<div class="popup">
		<h2>Lorem ipsum dolor sit amet consectetur adipisicing elit.</h2>
		<p class="messageNotif">
			Lorem ipsum, dolor sit amet consectetur adipisicing elit. Officiis expedita ad eum hic dolorem illum alias inventore voluptatum labore quaerat magnam odit, eaque nisi provident.
		</p>
		<div class="btns">
			<span class="next">Oui</span>
			<span class="prev">Non</span>
		</div>
	</div>
</div>

<!-- Confirmer une action avec une donnée -->
<div class="popups notifChoice v2">
	<div class="popup">
		<h2>Lorem ipsum dolor sit, amet consectetur adipisicing elit.</h2>
		<p class="messageNotif">
			Lorem ipsum, dolor sit amet consectetur adipisicing elit. Laboriosam atque, quibusdam totam deleniti ad sequi ab excepturi. Commodi aliquam minus mollitia perferendis dignissimos laborum omnis, tempore placeat eaque, fugiat nam.
		</p>
		<form>
			<label for="passConfirm">Mot de passe :</label>
			<div class="input-group">
				<input type="password" id="ccpass" autocomplete="off">
				<span class="mio eyes icons" title="Afficher le mot de passe">visibility</span>
			</div>
			<div class="btns">
				<span class="next">Confirmer</span>
				<span class="prev">Annuler</span>
			</div>
		</form>
	</div>
</div>

<div class="boxCookie" style="display: none" data-utils="<?= $version_app ?>"></div>

<div class="loads loaddingProcess">
	<div>
		<img src="<?= $img ?>load2.gif">
		<div>
			<span>Chargement en cours<br><span class="secondary">Veuillez patienter s'il vous plaît...</span></span>
		</div>
	</div>
</div>

<div class="loads loaddingProcess2">
	<div>
		<img src="<?= $img ?>load4.gif">
		<div>
			<span>L'opération est en cours de configuration...<br>Veuillez patienter s'il vous plaît...</span>
		</div>
	</div>
</div>

<div class="errorMess msg">
	<span class="mio">cancel</span>
	<span class="mess">
		<?php if (isset($_SESSION['error'])) {
			echo $_SESSION['error'];
		} ?>
	</span>
</div>
<div class="successMess msg">
	<span class="mio">check_circle</span>
	<span class="mess">
		<?php if (isset($_SESSION['success'])) {
			echo $_SESSION['success'];
		} ?>
	</span>
</div>